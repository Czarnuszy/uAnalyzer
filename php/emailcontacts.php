<?php
if( isset($_POST['fname']) )
{
	$to = 'michalmagni@gmail.com';
	
	$subject = "Z-Wave Toolbox".'.'.$_POST['fname'];
	$message = $_POST['message'] . "\n\n" . 'Regards, ' . $_POST['fname'] . ' '. $_POST['lname'] . '.';
	$headers = 'From: ' . $_POST['fname'] . "\r\n" . 'Reply-To: ' . $_POST['email'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();
	
	mail($to, $subject, $message, $headers);
}
?>