<?php
namespace tFPDF;

require_once __DIR__ . "/font_handler_unicode.php";
require_once __DIR__ . "/tfpdf.php";
require_once __DIR__ . "/ttfonts.php";

class UniPdf extends tFPDF
{
    // Use with $orientation:
    const OrientPortrait = "P";
    const OrientLandscape = "L";

    // Use with $units:
    const UnitsPoints = "pt";
    const UnitsMillimeters = "mm";
    const UnitsCentimeters = "cm";
    const UnitsInches = "in";

    // Use with $style:
    const FontNormal = "";
    const FontBold = "B";
    const FontItalic = "I";
    const FontBoldItalic = "BI";
    const FontUnderline = "U";

    protected $m_debug = false;

    private $m_font_handler;

    function __construct($orientation = self::OrientPortrait, 
        $units = self::UnitsMillimeters, $page_size= "A4")
    {
        parent::__construct($orientation, $units, $page_size);
    }

    // Add a true-type unicode font
    function AddFontUnicode($family, $style, $font_dir_path, $font_file_name)
    {
        $family = strtolower($family);
        $style = strtoupper($style);
        if ($style=='IB')
        {
            $style='BI';
        }

        $fontkey = $family.$style;
        if (isset($this->fonts[$fontkey]))
        {
            return;
        }

        $font_file_path = "$font_dir_path/$font_file_name";
        if (!is_file($font_file_path))
        {
            throw new Exception("No file '$font_file_path'");
            return(false);
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

        $sbarr = (!empty($this->AliasNbPages)) ? range(0,57) : range(0,32);

        $this->fonts[$fontkey] = 
        [
            "i" => count($this->fonts) + 1, 
            "type" => "TTF", 
            "name" => preg_replace("/[ ()]/", "", $ttf->fullName),
            "desc" => $desc, 
            "up" => round($ttf->underlinePosition), 
            "ut" => round($ttf->underlineThickness), 
            "cw" => $ttf->charWidths, 
            "ttffile" => $font_file_path, 
            "fontkey" => $fontkey, 
            "subset" => $sbarr, 
            "unifilename" => $font_file_path,
            "omit_cw127" => true,
        ];

        $this->FontFiles[$fontkey]=
        [
            # "length1" => $originalsize, 
            "type"=> "TTF", 
            "ttffile" => $font_file_path,
        ];
        $this->FontFiles[$font_file_name] = ["type" => "TTF"];

        if ($this->m_debug)
        {
            # Dump except for binary "cw" array:
            $f_copy = $this->fonts[$fontkey];
            $f_copy["cw"] = null;
            $f = print_r($f_copy, true);
            $ff = print_r($this->FontFiles[$font_file_name], true);
            print(__FUNCTION__ . 
                "($family, $style, $font_dir_path, $font_file_name)\n" .
                "fonts: $f\n" .
                "FontFiles: $ff\n");
        }

        $this->m_font_handler = new FontHandlerUnicode($this->fonts[$fontkey]);

        return true;
    }

    // Get width of a string in the current font
    function GetStringWidth($str)
    {
        return($this->m_font_handler->GetStringWidth($str));
        /*
        $str = (string)$str;
        $char_widths = &$this->CurrentFont["cw"];
        $width = 0;
        $unicode = $this->UTF8StringToArray($str);

        foreach ($unicode as $char) 
        {
            if (isset($char_widths[$char])) 
            { 
                // Extract big-endian uint_16:
                $width += (ord($char_widths[2 * $char]) << 8) + 
                    ord($char_widths[2 * $char + 1]); 
            }
            else if ($char > 0 && $char < 128 && 
                isset($char_widths[chr($char)])) 
            { 
                $width += $char_widths[chr($char)]; 
            }
            else if (isset($this->CurrentFont["desc"]["MissingWidth"])) 
            { 
                $width += $this->CurrentFont["desc"]["MissingWidth"]; 
            }
            else if (isset($this->CurrentFont["MissingWidth"])) 
            { 
                $width += $this->CurrentFont["MissingWidth"]; 
            }
            else 
            { 
                $width += 500; 
            }
        }

        return $width * $this->FontSize / 1000;
        */
    }

    // Converts UTF-8 strings to codepoints array
    // See https://www.unicode.org/versions/Unicode12.0.0/ch03.pdf,
    // "Table 3-7. Well-Formed UTF-8 Byte Sequences"
    function UTF8StringToArray($str) 
    {


        $out = array();
        $str_length = strlen($str);
        $i = 0;
        while ($i < $str_length) 
        {
            $code = -1;
            $h = ord($str[$i++]);
            if ($h <= 0x7F)
            {
                $code = $h;
            }
            elseif ($h < 0xC2) 
            {
                // null
            }
            else if ($h <= 0xDF && $i < $str_length)
            {
                $code = ($h & 0x1F) << 6 | 
                    (ord($str[$i++]) & 0x3F);
            }
            elseif ($h <= 0xEF && $i < ($str_length - 1))
            {
                $code = ($h & 0x0F) << 12 | 
                    (ord($str[$i++]) & 0x3F) << 6 |
                    (ord($str[$i++]) & 0x3F);
            }
            elseif ($h <= 0xF4 && $i < ($str_length - 2))
            {
                $code = ($h & 0x0F) << 18 | 
                    (ord($str[$i++]) & 0x3F) << 12 |
                    (ord($str[$i++]) & 0x3F) << 6 |
                    (ord($str[$i++]) & 0x3F);
            }

            if ($code >= 0) 
            {
                $out[] = $code;
            }
        }
        return $out;
    }

    protected function SaveUnicodeSubset($string)
    {
        foreach ($this->UTF8StringToArray($string) as $code)
        {
            $this->CurrentFont["subset"][$code] = $code;
        }
    }

    protected function EscapeUnicodeString($string)
    {
        return $this->_escape($this->UTF8ToUTF16BE($string, false));
    }

    // Get unicode string length, ignoring trailing newlines:
    protected function GetUnicodeStringLength($string)
    {
        $num_bytes = mb_strlen($string, "utf-8");
        while ($num_bytes > 0 && 
            mb_substr($string, $num_bytes - 1, 1, "utf-8") == "\n")
        {
            --$num_bytes;
        }
    }

    function GetUnicodeSubstring($string , $start, $length = null)
    {
        return mb_substr($string, $start, $length, "utf-8");
    }

}
