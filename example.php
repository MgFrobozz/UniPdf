<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");

require('tfpdf.php');

// Please update these if you change the content of HelloWorld.txt
$src_file_name = 'HelloWorld.txt';
$pdf_file_name = 'example.pdf';
$pdf_file_size = '22.1KB';

$pdf = new tFPDF();
$pdf->AddPage();

// Add a Unicode font (uses UTF-8)
$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
$pdf->SetFont('DejaVu','',14);

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