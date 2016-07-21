<?php
$fileName = $_POST['fileName'];
$fileID = fopen("../data/Saves/".$fileName, "r") or die("Unable to open file!");
$homeid = fgets($fileID);
fclose($fileID);

  echo $homeid;


?>
