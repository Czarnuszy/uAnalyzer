<?php

$csvFile = '../zniffer/data/zniffer.csv';
$zlfFile = '../zniffer/data/zniffer.zlf';
$idFile = '../zniffer/data/zniffer.txt';
$SaveFile = '../data/Saves/'.$_GET['filename'].".csv";
$SaveFilezlf = '../data/Saves/'.$_GET['filename'].".zlf";
$SaveFileid = '../data/Saves/'.$_GET['filename'].".txt";

if (!copy($csvFile, $SaveFile)) {
    echo "failed to copy $file...\n";
}

if (!copy($zlfFile, $SaveFilezlf)) {
    echo "failed to copy $file...\n";
 }

 if (!copy($idFile, $SaveFileid)) {
     echo "failed to copy $file...\n";
  }

?>
<script>
  w2ui.grid.clear();
$( "#body-w" ).load( "ajax/jsGrid.php" );
</script>
