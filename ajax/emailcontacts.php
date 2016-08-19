<?php
//include ('www/ajax/phpmailer/PHPMailerAutoload.php');
require 'PHPMailerAutoload.php';
$task = $_POST['request'];

if($task == "send2zwave"){
	send2zwave();
}	else{
	contact();
	}






//send2zwave();
function send2zwave(){

$fname = $_POST['fileName'];
$csvFile = $fname. ".csv";
$txtFile = $fname. ".txt";
$zlfFile = $fname. ".zlf";

		$email = new PHPMailer();
		$email -> setFrom('zwaveOrSMt@gmail.com', $nm);
		$email -> addAddress('michalmagni@gmail.com', 'Me');

		$email -> addAttachment('/www/data/Saves/'.$csvFile);
		$email -> addAttachment('/www/data/Saves/'.$txtFile);
		$email -> addAttachment('/www/data/Saves/'.$zlfFile);
		$email->isHTML(true);

		$body = "Help me with my zwave!";
		$email->Subject = 'Zwave ';
  	$email->msgHTML($body);


		if(!$email->send()) {
		    echo 'Mailer Error: ' . $email->ErrorInfo;
		} else
		    echo 'Message has been sent';
}



function contact(){
if(isset($_POST['fname']) )
{

	$to = 'michalmagni@gmail.com';

	$subject = "Z-Wave Toolbox".'.'.$_POST['fname'];
	$message = $_POST['message'] . "\n\n" . 'Regards, ' . $_POST['fname'] . ' '. $_POST['lname'] . '.';
	$headers = 'From: ' . $_POST['fname'] . "\r\n" . 'Reply-To: ' . $_POST['email'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);
	echo "Done";
}else {
	echo 'error';
}

}




?>
