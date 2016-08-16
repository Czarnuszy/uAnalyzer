<?php
$file = fopen("../zniffer/data/id.txt", "w") or die("Unable to open file!");
$txt = $_POST['homeid'];
fwrite($file, $txt);
fclose($file);

?>
