<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");

require('uni_pdf.php');
use tFPDF\UniPdf;
use tFPDF\tFPDF;

// Can also use installed system files directly, eg
// "/usr/share/fonts/truetype/freefont/FreeSansOblique.ttf";
$italic_font_path = __DIR__ . "/my_fonts/FreeSansOblique.ttf";

$src_file_name = "lorem_ipsum.txt";

$text = 
    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod " .
    "tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim " .
    "veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea " .
    "commodo consequat. Duis aute irure dolor in reprehenderit in voluptate " .
    "velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint " .
    "occaecat cupidatat non proident, sunt in culpa qui officia deserunt " .
    "mollit anim id est laborum.";
$pdf_file_name = 'test_out.pdf';

$pdf = new UniPdf();
$pdf->SetTopMargin(50);
$pdf->SetLeftMargin(50);
$pdf->AddPage(tFPDF::OrientPortrait, "letter"); 

// Add a Unicode font (uses UTF-8)
$pdf->AddFontUnicode("custom", tFPDF::FontItalic, $italic_font_path);
$pdf->SetFont("custom", UniPdf::FontItalic, 12);

$width = 100;
$height = 5;
$text = file_get_contents($src_file_name);

# $pdf->Write(8,$text);

$pdf->MultiCell($width, $height, $text, tFPDF::BorderFrame, 
    tFPDF::AlignJustify, tFPDF::FillNone);

$pdf->Output($pdf_file_name, 'F');
print("Converted $src_file_name -> '$pdf_file_name'\n");

?>
