
<?php

include ('/www/ajax/zniffer_controller.php');

if ($process -> status()){
        echo "The process is currently running";
    }else{
        echo "The process is not running.";
    }
?>
