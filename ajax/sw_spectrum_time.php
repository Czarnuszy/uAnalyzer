<?php

$todo = $_POST['sw'];

if ($todo == "save") {
  $myFile = "/www/zniffer/data/timeSpectrum";
  $fh = fopen($myFile, 'w') or die("can't open file");
  $Data = $_POST['timedata'];
  fwrite($fh, $Data);
  fclose($fh);
  echo 1;
}elseif ($todo == "read") {

  $myFile = "/www/zniffer/data/timeSpectrum";
  $fh = fopen($myFile, 'r') or die("can't open file");
  $time = fgets($fh);
  fclose($fh);
  echo $time;

}



?>
