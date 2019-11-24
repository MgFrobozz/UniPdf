<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");

require('tfpdf.php');
use tFPDF\tFPDF;

// Can also use installed system files directly, eg
// "/usr/share/fonts/truetype/freefont/FreeSansOblique.ttf";
$italic_font_path = __DIR__ . "/my_fonts/FreeSansOblique.ttf";

$text = 
    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod " .
    "tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim " .
    "veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea " .
    "commodo consequat. Duis aute irure dolor in reprehenderit in voluptate " .
    "velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint " .
    "occaecat cupidatat non proident, sunt in culpa qui officia deserunt " .
    "mollit anim id est laborum.";
$pdf_file_name = 'test_out.pdf';

$pdf = new tFPDF();

// Add a Unicode font (uses UTF-8)
$pdf->AddFontUnicode("custom", tFPDF::StyleItalic, $italic_font_path);
$pdf->SetFont("custom", tFPDF::StyleItalic, 12);

$text = file_get_contents("lorem_ipsum.txt");
$width = 80;
$line_height = 5;

$pdf->AddPage(tFPDF::OrientPortrait, "letter"); 

$title = "BorderFrame, AlignJustify, FillNone";
$pdf->SetXY(20, 10);
$pdf->MultiCell($width, $line_height, $title, tFPDF::BorderNone, 
    tFPDF::AlignJustify, tFPDF::FillNone);
$pdf->SetX(20);
$pdf->MultiCell($width, $line_height, $text, tFPDF::BorderFrame, 
    tFPDF::AlignJustify, tFPDF::FillNone);

$title = "BorderFrame, AlignLeft, FillNone";
$pdf->SetXY(120, 10);
$pdf->MultiCell($width, $line_height, $title, tFPDF::BorderNone, 
    tFPDF::AlignJustify, tFPDF::FillNone);
$pdf->SetX(120);
$pdf->MultiCell($width, $line_height, $text, tFPDF::BorderFrame, 
    tFPDF::AlignLeft, tFPDF::FillNone);

$title = "BorderFrame, AlignRight, FillNone";
$pdf->SetLineWidth(0.5);
$pdf->SetXY(20, 100);
$pdf->MultiCell($width, $line_height, $title, tFPDF::BorderNone, 
    tFPDF::AlignJustify, tFPDF::FillNone);
$pdf->SetX(20);
$pdf->MultiCell($width, $line_height, $text, tFPDF::BorderFrame, 
    tFPDF::AlignRight, tFPDF::FillNone);

$title = "BorderNone, AlignRight, FillSolid";
$pdf->SetFillColor(200, 200, 255);
$pdf->SetLineWidth(0.75);
$pdf->SetXY(120, 100);
$pdf->MultiCell($width, $line_height, $title, tFPDF::BorderNone, 
    tFPDF::AlignJustify, tFPDF::FillNone);
$pdf->SetX(120);
$pdf->MultiCell($width, $line_height, $text, tFPDF::BorderNone, 
    tFPDF::AlignJustify, tFPDF::FillSolid);

$pdf->AddPage(tFPDF::OrientPortrait, "letter"); 
$pdf->SetMargins(10, 10);
$pdf->SetXY(10, 10);

$line_height = 8;

$pdf->SetFont("custom", tFPDF::StyleItalic, 14);

// Load a UTF-8 string from a file and print it
$text = file_get_contents("HelloWorld.txt");
$pdf->Write($line_height, $text);

$pdf->Output($pdf_file_name, 'F');
print("Converted -> '$pdf_file_name'\n");

?>
