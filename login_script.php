<?php

	session_start();
	
	if ((!isset($_POST['login'])) || (!isset($_POST['password'])))
	{
		header('Location: index.php');
		exit();
	}

//require_once "user_data.txt";

$file_login =fopen('user_login.txt', 'r');

$file_pass =fopen('user_password.txt', 'r');

while(!feof($file_pass) && !feof($file_login)	)
{	
	$line = fgets($file_pass);
	$data_pass = $line;	
	$line_ln = fgets($file_login);
	$data_login = $line_ln;

}
	fclose($file_pass);
	

	
	if(!file_exists('user_password.txt') || !file_exists('user_login.txt') )
	{
	echo "Error: Can not find a user_data files";
	}
	else
	{
	$login = $_POST['login'];
	$password = $_POST['password'];
	
			if((!strcmp($data_login, $login)) && (!strcmp($data_pass, $password))){
			$_SESSION['logged'] = true;
			
			unset($_SESSION['error']);

			header('Location: index.php');
				
			} else {
					
				$_SESSION['error'] = '<span style="color:red">Incorrect login or password!</span>';
					header('Location: login.php');
		

						}
	}

?>