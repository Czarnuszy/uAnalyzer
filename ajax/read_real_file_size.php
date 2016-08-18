<?php

function readForLines($csvFile){
       $line_count=0;
    	 $file_handle = fopen($csvFile, 'r');

      	 while (!feof($file_handle) ) {
      	  	$line = fgetcsv($file_handle);
            $line_count++;

      	 }

    	 fclose($file_handle);
    	 return $line_count;
    }

    $csvFile = '../zniffer/data/zniffer.csv';
    $max = readForLines($csvFile) -1;
    echo $max;

?>
