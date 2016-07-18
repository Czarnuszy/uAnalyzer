<?php

$csvFile = '../zniffer/data/zniffer.csv';
$zlfFile = '../zniffer/data/zniffer.csv';
$SaveFile = '../data/Saves/'.$_GET['filename'].".csv";
$SaveFilezlf = '../data/Saves/'.$_GET['filename'].".zlf";

if (!copy($csvFile, $SaveFile)) {
    echo "failed to copy $file...\n";
}

if (!copy($zlfFile, $SaveFilezlf)) {
    echo "failed to copy $file...\n";
 }


?>
<script>
  w2ui.grid.clear();
$( "#body-w" ).load( "ajax/jsGrid.php" );
</script>
