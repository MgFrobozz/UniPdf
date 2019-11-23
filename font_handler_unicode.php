<?php
namespace tFPDF;

// See https://www.unicode.org/versions/Unicode12.0.0/ch03.pdf
// The Unicode® Standard
// Version 12.0 – Core Specification

require_once __DIR__ . "/ttfonts.php";

class FontHandlerUnicode
{
    const DefaultMissingWidth = 500;
    const DefaultFontSize = 10;
    const Debug = false;

    // Font description:
    private $m_font;
    // Font size:
    private $m_font_size;
    // Widths of glyphs, not scaled by current font size:
    private $m_glyph_widths;
    // Used for missing codepoints;
    private $m_missing_width;

    function __construct($font, $font_size = self::DefaultFontSize)
    {
        $this->m_font = $font;
        $this->m_glyph_widths = $font["cw"];
        $this->m_font_size = $font_size;

        $this->m_missing_width = null;
        if (isset($font["desc"]["MissingWidth"]))
        {
            $this->m_missing_width = $font["desc"]["MissingWidth"];
        }
        else if (isset($font["MissingWidth"])) 
        { 
            $this->m_missing_width = $font["MissingWidth"];
        }
        else
        {
            $this->m_missing_width = self::DefaultMissingWidth;
        }
    }

    // Converts UTF-8 strings to codepoints array
    // ("Table 3-7. Well-Formed UTF-8 Byte Sequences")
    function Utf8ToCodepoints($string) 
    {
        # debug...
        # print(__FUNCTION__ . "($string)\n");
        # ...debug

        $codes = array();
        $str_length = strlen($string);
        $i = 0;
        while ($i < $str_length) 
        {
            $code = -1;
            $h = ord($string[$i++]);
            if ($h <= 0x7F)
            {
                $code = $h;
            }
            elseif ($h < 0xC2) 
            {
                // Undefined in table 3-7, drop it ...
            }
            else if ($h <= 0xDF && $i < $str_length)
            {
                $code = ($h & 0x1F) << 6 | 
                    (ord($string[$i++]) & 0x3F);
            }
            elseif ($h <= 0xEF && $i < ($str_length - 1))
            {
                $code = ($h & 0x0F) << 12 | 
                    (ord($string[$i++]) & 0x3F) << 6 |
                    (ord($string[$i++]) & 0x3F);
            }
            elseif ($h <= 0xF4 && $i < ($str_length - 2))
            {
                $code = ($h & 0x0F) << 18 | 
                    (ord($string[$i++]) & 0x3F) << 12 |
                    (ord($string[$i++]) & 0x3F) << 6 |
                    (ord($string[$i++]) & 0x3F);
            }

            if ($code >= 0) 
            {
                $codes[] = $code;
            }
        }
        return $codes;
    }

    // Get the glyph width of a string
    function GetStringWidth($string)
    {
        $string = (string)$string;
        $str_width = 0;
        $codes = self::Utf8ToCodepoints($string);

        foreach ($codes as $code) 
        {
            if (isset($this->m_glyph_widths[$code])) 
            { 
                // Big-endian uint_16:
                $str_width += (ord($this->m_glyph_widths[2 * $code]) << 8) + 
                    ord($this->m_glyph_widths[2 * $code + 1]); 
            }
            else if ($code > 0 && $code < 128 && 
                isset($this->m_glyph_widths[chr($code)])) 
            { 
                $str_width += $this->m_glyph_widths[chr($code)]; 
            }
            else
            {
                $str_width += $this->m_missing_width;
            }
        }

        # debug...
        # $fs = $this->m_font_size;
        # $w = $str_width * $this->m_font_size / 1000;
        # print(__FUNCTION__ . "($string, $fs) -> $w\n");
        # ...debug
        return $str_width * $this->m_font_size / 1000;
    }

    // Return string length, not including final newlines
    function GetStringLength($string)
    {
        $str_len = mb_strlen($string, "utf-8");
        while ($str_len > 0 && 
            mb_substr($string, $num_bytes - 1, 1, "utf-8") == "\n")
        {
            --$str_len;
        }
        # debug...
        # print(__FUNCTION__ . "($string)\n");
        # ...debug
        return($str_len);
    }

    function GetSubstring($string , $start, $length = null)
    {
        return mb_substr($string, $start, $length, "utf-8");
    }

    function EscapeString($string)
    {
        return mb_convert_encoding($string, "UTF-16BE", "UTF-8");
    }

    function SaveCodeSubset($string)
    {
        foreach ($this->Utf8ToCodepoints($string) as $code)
        {
            $this->m_font["subset"][$code] = $code;
        }
    }

    static function CreateFontDescription($family, $style, $font_file_path)
    {
        if (!is_file($font_file_path))
        {
            throw new \Exception(__FUNCTION__ . ": no file '$font_file_path'");
        }

        $ttf = new TTFontFile();
        $ttf->getMetrics($font_file_path);

        $desc= 
        [
            "Ascent" => round($ttf->ascent),
            "Descent" => round($ttf->descent),
            "CapHeight" => round($ttf->capHeight),
            "Flags" => $ttf->flags,
            "FontBBox" => "[".
                round($ttf->bbox[0]) . " " .
                round($ttf->bbox[1]) . " " . 
                round($ttf->bbox[2]) . " " .
                round($ttf->bbox[3]) . "]",
            "ItalicAngle" => $ttf->italicAngle,
            "StemV" => round($ttf->stemV),
            "MissingWidth" => round($ttf->defaultWidth),
        ];

        $font = 
        [
            "type" => "TTF", 
            "name" => preg_replace("/[ ()]/", "", $ttf->fullName),
            "desc" => $desc, 
            "up" => round($ttf->underlinePosition), 
            "ut" => round($ttf->underlineThickness), 
            "cw" => $ttf->charWidths, 
            "ttffile" => $font_file_path, 
            "unifilename" => $font_file_path,
            "omit_cw127" => true,
        ];

        if (self::Debug)
        {
            # Dump except for binary "cw" array:
            $f_copy = $this->fonts[$fontkey];
            $f_copy["cw"] = null;
            $f = print_r($f_copy, true);
            $ff = print_r($this->FontFiles[$font_file_name], true);
            print(__FUNCTION__ . 
                "($family, $style, $font_file_path)\n" .
                "fonts: $f\n" .
                "FontFiles: $ff\n");
        }

        return $font;
    }

}



