<?php
$file = fopen("../zniffer/data/zniffer.txt", "w") or die("Unable to open file!");
$txt = $_POST['homeid'];
$len = $_POST['gridlen'];
$txt = $txt."\n";
fwrite($file, $txt);
fwrite($file, $len);
fclose($file);

?>
