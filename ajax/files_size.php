<?php
/*  function readForLines($csvFile){
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

      $csvFile = $file_name;
      $max = readForLines($csvFile) ;
      echo $max;
*////////////////////////////////
      $fileNametxt = $_POST['DisplayedRecords'];
    //  $fileNametxt = substr($fileName, 0, -4);
  //  $fileNametxt = '../data/SaveData/'.$fileNametxt.'.txt';
    //  $fileNametxt = '../data/SaveData/'.$fileNametxt.".txt";
      $fileID = fopen($fileNametxt, "r") or die("Unable to open file!");
      $homeid = fgets($fileID);
     $amount_lines = fgets($fileID);
      fclose($fileID);

        echo json_encode(array($homeid, $amount_lines));
      //  echo $homeid;
?>
