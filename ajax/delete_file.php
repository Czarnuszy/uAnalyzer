<?php
$rename = $_POST['todo'];
$fname = $_POST['fileName'];
$csvname = $fname. ".csv";
$txtname = $fname. ".txt";
$zlfname = $fname. ".zlf";

if ($rename == 1){
  $newName = $_POST['newName'];
  $newCSVname = $newName. ".csv";
  $newTXTname = $newName. ".txt";
  $newZLFname = $newName. ".zlf";

  rename($csvname, $newCSVname);
  rename($txtname, $newTXTname);
  rename($zlfname, $newZLFname);
}else{
  unlink($csvname);
  unlink($txtname);
  unlink($zlfname);
}



 ?>
