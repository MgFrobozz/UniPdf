<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");

require("tfpdf.php");
use tFPDF\tfPDF;

// Update if you change the content of HelloWorld.txt:
$pdf_file_size = "22.1KB";

$pdf_file_name = "example_out.pdf";

$pdf = new tFPDF();
$pdf->AddPage();

// Add a Unicode font (uses UTF-8). Can also use installed system files 
// directly (eg /usr/share/fonts/truetype/freefont/FreeSans.ttf)
$dvs_normal_font_path = __DIR__ . "/font/unifont/DejaVuSansCondensed.ttf";

$pdf->AddFontUnicode("dvs", tFPDF::StyleNormal, $dvs_normal_font_path);
$pdf->SetFont("dvs", tFPDF::StyleNormal, 14);

// Load a UTF-8 string from a file and print it
$hello_world = file_get_contents("HelloWorld.txt");
$pdf->Write(8, $hello_world);

// Select a standard font (uses windows-1252). As a core font, this need not
// be loaded before calling SetFont
$pdf->SetFont("Arial", "", 14);
$pdf->Ln(10);
$pdf->Write(5,"This PDF is only about $pdf_file_size bytes long.");

$pdf->Output($pdf_file_name, "F");
print("Converted -> '$pdf_file_name'\n");

?>
