
<?php
$fname = $_POST['fileName'];

function readCSV($csvFile){
   $file_handle = fopen($csvFile, 'r');
   while (!feof($file_handle) ) {
      $line_of_text[] = fgetcsv($file_handle, 1024);
 }
 fclose($file_handle);
 return $line_of_text;
}
//open file//read data to firt grid//opem secend file//read max data to second grid//copy first file to second file



$csvFile = '../zniffer/data/AnalyzerData.csv';
$AnalyzerData = readCSV($csvFile);
$max = count($AnalyzerData)-1;

$csvFileMax = '../zniffer/data/MaxAnalyzerData.csv';
$MaxAnalyzerData = readCSV($csvFileMax);
$maxM = count($MaxAnalyzerData)-1;

for ($i=0; $i < $max; $i++) {
# c$AnalyzerData.sizeofode..$AnalyzerData.sizeof.

   $AnalyzerData[$i][0] = (float)$AnalyzerData[$i][0];
   $AnalyzerData[$i][0] = (int)$AnalyzerData[$i][0]/1000;
   $rssi = $AnalyzerData[$i][1];
     	$rssi = $rssi * 1.7;
     	$rssi = $rssi - 30;
     	$rssi = (int) $rssi;
     	if ($rssi> 100) $rssi = 100;
     	$AnalyzerData[$i][1] = $rssi;
}



for ($i=0; $i < $maxM; $i++) {
  $MaxAnalyzerData[$i][1] = (int)$MaxAnalyzerData[$i][1];
      if( $AnalyzerData[$i][1] > $MaxAnalyzerData[$i][1]){
        	$MaxAnalyzerData[$i][1] = $AnalyzerData[$i][1];
        }
}

$file = fopen($csvFileMax, "w") or die("Unable to open file!");
  for ($i=0; $i < $max; $i++) {
    $txt =  $AnalyzerData[$i][0].','. $MaxAnalyzerData[$i][1]."\n";
    fwrite($file, $txt);
  }

fclose($file);


if ($fname == "data") {
  echo json_encode($AnalyzerData);
}
else if($fname == "d") {
  echo json_encode($MaxAnalyzerData);
}
//copy($csvFile, $csvFileMax);

?>
