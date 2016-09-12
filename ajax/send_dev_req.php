<?php


$dev = $_POST['dev'];
$file = '../zniffer/data/actual_script.txt';
$actualScript = readFileData($file);

if ($actualScript == 'ima') {
  exec('python /www/pyzwave/serial/ima.py -s -dev '.$dev);
  echo 'done';
} else {
    exec('rwee -w -z -x -f /www/data/firmware/ima');
    saveFileData($file);
    exec('python /www/pyzwave/serial/ima.py -s -dev '.$dev);
    echo 'done';
}


function saveFileData($File)
{
    $file_handle = fopen($File, 'w');
    $txt = 'ima';
    fwrite($file_handle, $txt);
    fclose($file_handle);
}

function readFileData($File)
{
    $file_handle = fopen($File, 'r');
    $line_of_text = fread($file_handle, 512);

    fclose($file_handle);

    return $line_of_text;
}

?>
