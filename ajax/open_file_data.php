<?php

$fsize = (int) $_POST['fsize'];
//$times = $_POST['tim'];
//$times = 10;
function readCSV($csvFile)
{
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 512);
    }
    fclose($file_handle);

    return $line_of_text;
}

function readSomeLines($csvFile)
{
    $start = $_POST['gridLen'];
    $amountLines = (int) $_POST['tim'];
    $file = new SplFileObject($csvFile);
    $file->seek($start);

    while ($file->key() != $amountLines) {
        $line_of_text[] = $file->fgetcsv();
    }

    /* $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $data = fgetcsv($file_handle, 1024);
          if($x >= $start && $x < $times){
    	  	  $line_of_text[] = $data;
          }
            $x+=1;
    	 }*/
  //	 fclose($file_handle);
     return $line_of_text;
}

$file_name = $_POST['data'];
//$file_name = '../data/Saves/'.$file_name.'.csv';
$csvFile = $file_name;
if ($fsize > 2000) {
    $AnalyzerData = readSomeLines($csvFile);
} else {
    $AnalyzerData = readCSV($csvFile);
}
header('Content-Type: application/json');

echo json_encode($AnalyzerData);
