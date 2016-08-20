<?php
$myFile = "/www/data/userconfig.json";
$fh = fopen($myFile, 'w') or die("can't open file");
$Data = $_POST['userData'];
fwrite($fh, $Data);
fclose($fh);
echo $Data;
?>
