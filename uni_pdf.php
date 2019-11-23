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
        # debug...
        # print(__FUNCTION__ . "()\n");
        # ...debug
        foreach ($this->UTF8StringToArray($string) as $code)
        {
            $this->CurrentFont["subset"][$code] = $code;
        }
    }

    protected function EscapeUnicodeString($string)
    {
        # debug...
        # print(__FUNCTION__ . "()\n");
        # ...debug
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
        return($num_bytes);
    }

}
