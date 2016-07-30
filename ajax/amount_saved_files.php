<?php
$directory = '../data/Saves';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
$amount_files = count($scanned_directory);
echo $amount_files;
?>
