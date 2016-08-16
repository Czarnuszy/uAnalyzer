<?php
$myFile = "/www/user_password.txt";

$oldPwdFile = fopen($myFile, "r") or die("Unable to open file!");
$realOldPwd = fgets($oldPwdFile);
fclose($oldPwdFile);

$oldpwd = $_POST['usrPWD'];
$newpwd = $_POST['newPWD'];
$newpwd2 = $_POST['newPWD2'];

//if($oldpwd === $realOldPwd && $newPWD === $newPWD2){
if((!strcmp($oldpwd, $realOldPwd)) && (!strcmp($newPWD, $newPWD2))){
  $fh = fopen($myFile, 'w') or die("can't open file");
  fwrite($fh, $newpwd);
  fclose($fh);
  echo 1;
}else {
  echo 0;
}

?>
