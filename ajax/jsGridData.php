<?php


function readCSV($csvFile){
  $start_line = (int) $_POST['startline'];
  $end = $start_line + 4;
      $x = 0;
  	 $file_handle = fopen($csvFile, 'r');

    	 	while (!feof($file_handle) ) {
            $buff = fgetcsv($file_handle, 1024);
            if($x >= $start_line && $x < $end)
              $line_of_text[] = $buff;
            $buff = "";
            $x+=1;
        //   $line_of_text[] = fgetcsv($file_handle, 1024);
    	 }
  	 fclose($file_handle);
  	 return $line_of_text;

}
$csvFile = '../zniffer/data/zniffer.csv';

$AnalyzerData = readCSV($csvFile);

echo json_encode($AnalyzerData);

?>
