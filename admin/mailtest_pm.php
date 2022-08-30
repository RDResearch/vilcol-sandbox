<?php

require 'phpmailer/PHPMailerAutoload.php';

$mail = new PHPMailer;

$mxrouting = true;

/* We are using mxrouting from 02/08/22
	Username:	outgoing@looking.co.uk
	Password:	tt#Ra^!dXz@g
	POP/IMAP Server:	pixel.mxrouting.net
	SMTP Server:	pixel.mxrouting.net
 */

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = ($mxrouting ? 'pixel.mxrouting.net' : 'rdresearch.co.uk');  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$auth_username = ($mxrouting ? 'outgoing@looking.co.uk' : 'sclbykevin@rdresearch.co.uk');
$mail->Username = $auth_username;                    // SMTP username
$mail->Password = ($mxrouting ? 'tt#Ra^!dXz@g' : 'h*23YE$y1p');                           // SMTP password
$mail->SMTPSecure = '';#'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 25;#587;                                    // TCP port to connect to

$mail->From = ($mxrouting ? 'Vilcol@looking.co.uk' : 'vilcol@rdresearch.co.uk');
$mail->FromName = 'Vilcol';

$going_to = 'kevinbeckett1@gmail.com';
$mail->addAddress($going_to, 'Kevin Beckett');     // Add a recipient
$mail->addReplyTo('kevin_beckett@hotmail.com', 'HotMail');
#$mail->addCC('cc@example.com');
#$mail->addBCC('bcc@example.com');

#$mail->addAttachment('LD/6047_fhbd/6047_Redemption_Statement_JAN2010_Neil_and_Rosalynne_Minsky_35938.pdf', '6047_Redemption_Statement_JAN2010_Neil_and_Rosalynne_Minsky_35938.pdf'); #('/var/tmp/file.tar.gz');         // Add attachments
#$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$date = date('d-m-y h:i:s');
$mail->Subject = "Test email sent at $date";
$details = "sent to $going_to via $auth_username at $date";
$mail->Body    = "This is the HTML message body <b>in bold!</b><br>$details";
$mail->AltBody = "This is the body in plain text for non-HTML mail clients ... $details";

if(!$mail->send()) {
    echo "Message could not be $details";
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo "Message has been $details";
}

?>
