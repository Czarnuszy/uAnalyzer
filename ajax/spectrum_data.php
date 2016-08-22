
<?php
$fname = $_POST['fileName'];
$clear = $_POST['clear'];

function readCSV($csvFile)
{
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);

    return $line_of_text;
}
//open file//read data to firt grid//opem secend file//read max data to second grid//copy first file to second file
function clear_file($file, $max, $AnalyzerData)
{
    $file = fopen($file, 'w') or die('Unable to open file!');
    for ($i = 0; $i < $max; ++$i) {
        $txt = $AnalyzerData[$i][0].','.'0'."\n";
        fwrite($file, $txt);
    }
    fclose($file);
}

$csvFile = '../zniffer/data/AnalyzerData.csv';
$AnalyzerData = readCSV($csvFile);
$max = count($AnalyzerData) - 1;

$csvFileMax = '../zniffer/data/MaxAnalyzerData.csv';
$MaxAnalyzerData = readCSV($csvFileMax);
$maxM = count($MaxAnalyzerData) - 1;

if ($clear == 1) {
    clear_file($csvFile, $max, $AnalyzerData);
    clear_file($csvFileMax, $max, $AnalyzerData);
    $no_data = readCSV($csvFile);
    $no_data_Max = readCSV($csvFileMax);
    echo json_encode(array($no_data, $no_data_Max));
} else {
    for ($i = 0; $i < $max; ++$i) {
        // c$AnalyzerData.sizeofode..$AnalyzerData.sizeof.

     $AnalyzerData[$i][0] = (float) $AnalyzerData[$i][0];
        $AnalyzerData[$i][0] = (int) $AnalyzerData[$i][0] / 1000;
     /*$rssi = $AnalyzerData[$i][1];
       	$rssi = $rssi * 1.7;
       	$rssi = $rssi - 30;
       	$rssi = (int) $rssi;
       	if ($rssi> 100) $rssi = 100;
       	$AnalyzerData[$i][1] = $rssi;*/
    }

    for ($i = 0; $i < $max; ++$i) {
        $MaxAnalyzerData[$i][0] = (float) $MaxAnalyzerData[$i][0];
        $MaxAnalyzerData[$i][0] = (int) $MaxAnalyzerData[$i][0] / 1000;
    /*$mrssi = $MaxAnalyzerData[$i][1];
       $mrssi = $mrssi * 1.7;
       $mrssi = $mrssi - 30;
       $mrssi = (int) $mrssi;
       if ($mrssi> 100) $mrssi = 100;
       $MaxAnalyzerData[$i][1] = $mrssi;*/
    }

    echo json_encode(array($AnalyzerData, $MaxAnalyzerData));
}
/*
if ($fname == "data") {
  echo json_encode($AnalyzerData);
}
else if($fname == "d") {
  echo json_encode($MaxAnalyzerData);
}*/
//copy($csvFile, $csvFileMax);

?>
