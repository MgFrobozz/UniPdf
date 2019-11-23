<?php
namespace tFPDF;

class FontHandler
{
    const DefaultFontSize = 10;

    // Font size:
    private $m_font_size;
    // Widths of glyphs, not scaled by current font size:
    private $m_glyph_widths;

    function __construct($font, $font_size = self::DefaultFontSize)
    {
        $m_glyph_widths = $font["cw"];
        $m_font_size = $font_size;
    }

    // Get the glyph width of a string
    function GetStringWidth($string)
    {
        $string = (string)$string;
        $str_width = 0;
        $str_len = strlen($string);
        for ($i = 0; $i < $str_len; $i++)
        {
            $str_width += $m_glyph_widths[$string[$i]];
        }
        return $str_width * $m_font_size / 1000;
    }

    // Return string length, not including final newline
    // FIXME - find out whether original author intended to suppress
    // all final newlines (see FontHandlerUnicode::GetStringLength)
    function GetStringLength($string)
    {
        $string_len = strlen($string);
        return ($string_len > 0 && $string[$string_len - 1] == "\n") ?
            ($string_len - 1) : $string_len;
    }

    function GetSubstring($string , $start, $length = null)
    {
        return substr($string, $start, $length);
    }

    function SaveCodeSubset($string)
    {
        // Nothing to do ...
    }

    function EscapeString($string)
    {
        // Nothing to do ...
        return $string;
    }

}


