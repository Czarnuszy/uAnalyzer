<?php

$dev = $_POST['dev'];

exec('python /www/pyzwave/serial/ima.py -s -dev '.$dev);
echo 'done';


?>
