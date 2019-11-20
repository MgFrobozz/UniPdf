<?php

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

    function __construct($orientation = self::OrientPortrait, 
        $units = self::UnitsMillimeters, $page_size= "A4")
    {
        parent::__construct($orientation, $units, $page_size);
    }

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

        return(true);
    }
}
