<?php
function readCSV($csvFile){
  	 $file_handle = fopen($csvFile, 'r');
    	 	while (!feof($file_handle) ) {
    	  	  $line_of_text[] = fgetcsv($file_handle, 1024);
    	 }
  	 fclose($file_handle);
  	 return $line_of_text;

}
$file_name = $_POST['data'];
$csvFile = '../data/Saves/'.$file_name;
$AnalyzerData = readCSV($csvFile);

echo json_encode($AnalyzerData);

?>
