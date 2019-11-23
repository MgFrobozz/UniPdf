<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");

require('uni_pdf.php');
use tFPDF\UniPdf;

// Please update these if you change the content of HelloWorld.txt:
$src_file_name = 'HelloWorld.txt';
$pdf_file_size = '22.1KB';

// Can also use installed system files directly, eg
// "/usr/share/fonts/truetype/freefont/FreeSansOblique.ttf";
$italic_font_path = __DIR__ . "/my_fonts/FreeSansOblique.ttf";

$pdf_file_name = 'example.pdf';

$pdf = new UniPdf();
$pdf->AddPage();

// Add a Unicode font (uses UTF-8)
$pdf->AddFontUnicode("custom", UniPdf::FontItalic, $italic_font_path);
$pdf->SetFont("custom", UniPdf::FontItalic, 14);

// Load a UTF-8 string from a file and print it
$txt = file_get_contents($src_file_name);
$pdf->Write(8,$txt);

// Select a standard font (uses windows-1252)
$pdf->SetFont('Arial','',14);
$pdf->Ln(10);
$pdf->Write(5,"This PDF is only about $pdf_file_size bytes long.");

$pdf->Output($pdf_file_name, 'F');
print("Converted $src_file_name -> '$pdf_file_name'\n");

?>
