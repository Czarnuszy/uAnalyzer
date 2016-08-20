<?php
$myFile = "/www/data/timeconfig.json";
$fh = fopen($myFile, 'w') or die("can't open file");
$Data = $_POST['timedata'];
fwrite($fh, $Data);
fclose($fh);
echo $Data;
?>
