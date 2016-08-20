<?php
//include ('www/ajax/phpmailer/PHPMailerAutoload.php');

$c = '/www/zniffer/check_internet_connection';
exec($c, $o);
$e =$o[0];
$t = substr($e, 6 , 5);

if ($t == "up") {

	require '/www/ajax/phpmailer/PHPMailerAutoload.php';
	$task = $_POST['request'];

	if($task == "send2zwave")
		send2zwave();
	else
		contact();

}else{
	echo "NOINTERNET";
}





//send2zwave();
function send2zwave(){

$fname = $_POST['fileName'];
$csvFile = $fname. ".csv";
$txtFile = $fname. ".txt";
$zlfFile = $fname. ".zlf";
$email = new PHPMailer();

$email->isSMTP();
$email->SMTPDebug = 2;
$email->Debugoutput = 'html';
//$email->Host = 'smtp.gmail.com';
$email->Host = gethostbyname('smtp.gmail.com');
$email->Port = 547;
$email->SMTPSecure = 'tls';
$email->SMTPAuth = true;
$email->Username = "zw@gmail.com";
$email->Password = "";



		$email -> setFrom('zw@gmail.com', "mee");
		$email -> addAddress('zw@gmail.com', 'Me');

		//$email -> addAttachment($csvFile);
		$email -> addAttachment($txtFile);
//		$email -> addAttachment($zlfFile);
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
