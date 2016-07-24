<?php
$fileName = $_POST['fileName'];
$fileID = fopen("../data/Saves/".$fileName, "r") or die("Unable to open file!");
$homeid = fgets($fileID);
fclose($fileID);

  echo $homeid;

  $directory = '../data/Saves';
  $scanned_directory = array_diff(scandir($directory), array('..', '.'));
  $amount_files = count($scanned_directory);
?>
