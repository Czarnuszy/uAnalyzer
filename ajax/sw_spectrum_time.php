<?php
date_default_timezone_set('America/New_York');

$todo = $_POST['sw'];

if ($todo == "save") {
  $myFile = "/www/zniffer/data/timeSpectrum";

  if (file_exists($myFile)){
      $fh = fopen($myFile, 'w') or die("can't open file");
      $Data = $_POST['timedata'];
      fwrite($fh, $Data);
      fclose($fh);
      echo 1;
  }
}elseif ($todo == "read") {

  $myFile = "/www/zniffer/data/AnalyzerData.csv";
  if (file_exists($myFile)){
  //  $fh = fopen($myFile, 'r') or die("can't open file");
    $t = date("F d Y H:i:s.", filectime($myFile));
  //  $time = fgets($fh);
  //  fclose($fh);
    echo $t;
  }
}elseif ($todo == "readStartTime") {
  $myFile = "/www/zniffer/data/timeSpectrum";
  if (file_exists($myFile)){
      $fh = fopen($myFile, 'r') or die("can't open file");
      $time = fgets($fh);
      fclose($fh);
      echo $time;
  }
}



?>
