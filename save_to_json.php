<?php
$myFile = "config.json";
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $_POST['timdat'];
$tab = [];
for ($i=0; $i < 40000; $i++) {
  array_push($tab, $stringData);
}
fwrite($fh, json_encode($tab));
fclose($fh)
?>
