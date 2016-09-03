<?php
$rename = $_POST['todo'];
$fname = $_POST['fileName'];

$csvname = '../data/Saves/'. $fname. ".csv";
$txtname = '../data/SaveData/'. $fname. ".txt";
$zlfname = '../data/SaveData/'.$fname. ".zlf";

if ($rename == 1){
  $newName = $_POST['newName'];
  $newCSVname ='../data/Saves/'. $newName. ".csv";
  $newTXTname = '../data/SaveData/'.$newName. ".txt";
  $newZLFname =  '../data/SaveData/'. $newName. ".zlf";

  rename($csvname, $newCSVname);
  rename($txtname, $newTXTname);
  rename($zlfname, $newZLFname);
}else{
  unlink($csvname);
  unlink($txtname);
  unlink($zlfname);
}



 ?>
