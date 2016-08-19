<?php

include ('/www/ajax/spectrum_controller.php');

if ($processSpectrum -> status()){
        echo "The process is currently running";
    }else{
        echo "The process is not running.";
    }

//   $d = $processSpectrum -> showpid();
  // echo $d[2];

?>
