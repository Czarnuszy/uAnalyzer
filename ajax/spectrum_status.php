<?php

include ('/www/ajax/spectrum_controller.php');
$y = 1;
$n = 0;
if ($processSpectrum -> status()){
        echo $y;
    }else{
        echo $n;
    }

//   $d = $processSpectrum -> showpid();
  // echo $d[2];

?>
