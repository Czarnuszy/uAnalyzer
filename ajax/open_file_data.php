<?php
$fsize = $_POST['fsize'];
//$times = $_POST['tim'];
//$times = 10;

function readCSV($csvFile){
  	 $file_handle = fopen($csvFile, 'r');
    	 	while (!feof($file_handle)) {
    	  	  $line_of_text[] = fgetcsv($file_handle, 1024);
    	 }
  	 fclose($file_handle);
  	 return $line_of_text;
}

function readSomeLines($csvFile){
    $start= $_POST['gridLen'];
    $times = $_POST['tim'];
    $x = 0;
    $file = new SplFileObject($csvFile);

  /*    for($i=$start; $i < $times; $i ++){
      $file->seek($i);
      $line_of_text[] = $file->current();
    }
*/


	 $file_handle = fopen($csvFile, 'r');
 //flock($file_handle, 1);
        while (!feof($file_handle)) {
            $data = fgetcsv($file_handle, 1024);
          if($x >= $start && $x < $times){ //&& $x < $times){
    	  	  $line_of_text[] = $data;
          }
            $x+=1;
    	 }
    //   flock($file_handle, 3);

  	 fclose($file_handle);
  	 return $line_of_text;
}

$file_name = $_POST['data'];
$csvFile = '../data/Saves/'.$file_name;

if($fsize > 1000){
  $AnalyzerData = readSomeLines($csvFile);
}
else {
  $AnalyzerData = readCSV($csvFile);

}

echo json_encode($AnalyzerData);

?>
