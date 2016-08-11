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
      $file_name = $_POST['DisplayedRecords'];

      $csvFile = '../data/Saves/'.$file_name;
      $max = readForLines($csvFile) ;
      echo $max;


?>
