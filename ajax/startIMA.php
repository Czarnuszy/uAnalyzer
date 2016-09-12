<?php

function actions($todo)
{
    if ($todo == 'add') {
        exec('python /www/pyzwave/serial/ima.py -a');
    } elseif ($todo == 'rm') {
        exec('python /www/pyzwave/serial/ima.py -rm');
    } elseif ($todo == 'reset') {
        exec('python /www/pyzwave/serial/ima.py -x');
    } elseif ($todo == 'nodeInf') {
        exec('python /www/pyzwave/serial/ima.py -n');
    } elseif ($todo == 'routingInf') {
        exec('python /www/pyzwave/serial/ima.py -rg');
    } elseif ($todo == 'learn') {
        exec('python /www/pyzwave/serial/ima.py -l');
    }
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

    $Todo = $_POST['req'];
    $file = '../zniffer/data/actual_script.txt';
    $actualScript = readFileData($file);
    echo $actualScript;
    echo $file;
    echo $Todo;
  //  $Todo = 'nodeInf';

    if ($actualScript == 'ima') {
        actions($Todo);
    } else {
        exec('rwee -w -z -x -f /www/data/firmware/ima');
        saveFileData($file);
        actions($Todo);
    }
