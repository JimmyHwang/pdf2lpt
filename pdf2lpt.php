<?Php
  use setasign\Fpdi\Fpdi;
  require_once('vendor/autoload.php');

  // 
  // 用日期決定印哪個版本
  //
  $day = date('d');
  $i = $day % 3;
  if ($i == 1) {
    $template_pdf = __DIR__."/templates/Template-Parrot.pdf";  
  } else if ($i == 2) {
    $template_pdf = __DIR__."/templates/Template-Umbrella.pdf";
  } else {
    $template_pdf = __DIR__."/templates/Template-Default.pdf";
  }
  //$template_pdf = "/usr/share/cups/data/default-testpage.pdf";  

  //
  // 初始變數
  //
  $target_pdf = "/tmp/pdf2lpt.pdf";
  $font_name = "Courier";
  $display_x = 40;
  $display_y = 160;
  $display_h = 5;  
  $lines = array();
  
  $date = new \DateTime('now');
  $tstr = $date->format('Y-m-d G:i');
  array_push($lines, "System Time: ".$tstr."|U");
  
  $disk_lines = GetDisksInfo();
  foreach ($disk_lines as $line) {
    array_push($lines, $line);
  }
  
  $pdf = new Fpdi();
  $pdf->AddPage();
  $pdf->setSourceFile($template_pdf);
  $tplIdx = $pdf->importPage(1);
  $pdf->useTemplate($tplIdx, 10, 10, 200);
  // $pdf->SetFont('Helvetica');
  $pdf->SetFont($font_name);
  $pdf->SetFontSize(10);
  
  $pdf->SetTextColor(0, 0, 0);
  $x = $display_x;
  $y = $display_y;
  $reset_font = false;
  foreach ($lines as $line) {
    if (strpos($line, "|") !== false) {
      $temp = explode("|", $line);
      if (count($temp) == 2) {
        $line = $temp[0];
        $pdf->SetFont($font_name, $temp[1]);
        $reset_font = true;
      }      
    }
    $pdf->SetXY($x, $y);
    $pdf->Write(0, $line);
    $y += $display_h;
    if ($reset_font) {
      $reset_font = false;
      $pdf->SetFont($font_name);
    }
  }

  // $pdf->Image('sample.png',100,0);
  $pdf->Output($target_pdf, 'F');
  PrintPDF($target_pdf);
  unlink($target_pdf);
    
function GetDisksInfo() {
  exec('df -h', $out);
  return $out;
}

function PrintPDF($pdf) {
  $cmd = sprintf("lp %s", $pdf);
  exec($cmd, $out);
  return $out;
}
?>