<html>
<head></head>
<body>
<?php
 function readCSV($csvFile){
 $file_handle = fopen($csvFile, 'r');
 while (!feof($file_handle) ) {
  $line_of_text[] = fgetcsv($file_handle, 1024);
 }
 fclose($file_handle);
 return $line_of_text;
}
$csvFile = 'AnalyzerData.txt';

$AnalyzerData = readCSV($csvFile);
$max = count($AnalyzerData);


echo '<pre>';
//print_r($AnalyzerData);
echo "Records found:"; 
echo "$max";
echo '</pre>';


for ($i=0; $i < $max; $i++) {  
# c$AnalyzerData.sizeofode..$AnalyzerData.sizeof.
 $AnalyzerData[$i][0] = (float)$AnalyzerData[$i][0];
 $AnalyzerData[$i][0] = (int)$AnalyzerData[$i][0]/1000;

}
echo $AnalyzerData[0][0];
?>

<?php
$file = 'AnalyzerData.txt';

$plik = fopen($file,'r');


$zawartosc = '';

while(!feof($plik))
{
   $linia = fgets($plik);
   $zawartosc .= $linia;
}

echo $zawartosc;

?>


</body>
</html>