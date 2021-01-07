<?php
// required headers
/*
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
*/

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($email,$subject,$body){

$mail = new PHPMailer(); // create a new object
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
$mail->IsSMTP(); // enable SMTP
$mail->SMTPDebug = 0; // debugging: 0 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = 'smtp.mailtrap.io';
$mail->Port = 465; // or 587
$mail->IsHTML(true);
$mail->Username = "f4f19c96dae186";
$mail->Password = "8db6c737d58038";
$mail->SetFrom("IoThouse@gmail.com");
$mail->Subject = $subject;
$mail->Body = $body;
$mail->AddAddress($email);

 if(!$mail->Send()) {
    return false;
 } else {
    return true;
 }
}

?>
