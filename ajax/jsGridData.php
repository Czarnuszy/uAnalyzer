<?php
function readCSV($csvFile){
  	 $file_handle = fopen($csvFile, 'r');
    	 	while (!feof($file_handle) ) {
    	  	  $line_of_text[] = fgetcsv($file_handle, 1024);
    	 }
  	 fclose($file_handle);
  	 return $line_of_text;

}
$csvFile = '../zniffer/data/zniffer.csv';

$AnalyzerData = readCSV($csvFile);
$max = count($AnalyzerData) -1;
$start_line = $_POST['data'];


echo json_encode($AnalyzerData);

?>
