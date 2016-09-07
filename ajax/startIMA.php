<?php
    $todo = $_POST['req'];

    if ($todo == 'add') {
      exec('python /www/pyzwave/serial/ima.py -a');
    }elseif ($todo == 'rm') {
      exec('python /www/pyzwave/serial/ima.py -rm');
    }elseif ($todo == 'reset') {
      exec('python /www/pyzwave/serial/ima.py -x');
    }elseif ($todo == 'nodeInf') {
      exec('python /www/pyzwave/serial/ima.py -n');
    }elseif ($todo == 'routingInf') {
      exec('python /www/pyzwave/serial/ima.py -rg');
    }elseif ($todo == 'learn') {
      exec('python /www/pyzwave/serial/ima.py -l');
    }

 ?>
