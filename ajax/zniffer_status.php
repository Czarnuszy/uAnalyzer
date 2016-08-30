
<?php

include ('/www/ajax/zniffer_controller.php');
$y = 1;
$n = 0;
$response = [0,1];
if ($process -> status()){
        echo json_encode($response[1]);
    }else{
        echo json_encode($response[0]);
    }
  //  $process -> showpid();

//   $d = $process -> showpid();
  // echo $d[2];

?>
