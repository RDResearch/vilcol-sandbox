<?php

# lib_pm.php : interface to phpMailer via mail_pm().
# KDB 26/04/16 (from Rialto 30/04/15).
# 
# $attachment_file and $attachment_name can be one of the following:
#	- both blank
#	- both set to a text string
#	- both set to an array of text strings, but both arrays must have the same count (from 02/08/16)

require 'phpmailer/PHPMailerAutoload.php';

function mail_pm($mailto, $subject, $message, $from_addr, $from_name, $attachment_file, $attachment_name, $bcc='', $image_paths='', $image_names='', $verbose=false)
{
	# return false for failure, true for success
	
	$abort = '';
	
	if (global_debug())
	{
		if (($mailto != 'kevin_beckett@hotmail.com') && ($mailto != 'kevinbeckett1@gmail.com'))
			$mailto = 'kevinbeckett1@gmail.com';
		$bcc = '';
	}
//	if (0 && global_debug())
//	{
//		dprint("*** MAIL DISABLED FOR KEVIN ***<br>mail_pm($mailto, $subject,<br>$message,<br>$from_addr, $from_name, $attachment_file, $attachment_name, $bcc='',
//					$image_paths, $image_names)");
//		return false;
//	}
	
	$mailto = str_replace(';', ',', str_replace(' ', '', $mailto));
	$to_list = explode(',', $mailto);
	if ($bcc)
	{
		$bcc = str_replace(';', ',', str_replace(' ', '', $bcc));
		$bcc_list = explode(',', $bcc);
	}
	else 
		$bcc_list = array();
		
	$pmail = new PHPMailer;
	
	$mxrouting = true; # We are using mxrouting from 02/08/22

	$pmail->isSMTP();
	$pmail->Host = ($mxrouting ? 'pixel.mxrouting.net' : 'rdresearch.co.uk');
	$pmail->SMTPAuth = true;
	//$pmail->Username = ($mxrouting ? 'outgoing@looking.co.uk' : 'sclbykevin@rdresearch.co.uk'); // TODO - uncomment this
	$pmail->Password = ($mxrouting ? 'tt#Ra^!dXz@g' : 'h*23YE$y1p');
	$pmail->SMTPSecure = 'ssl';
	$pmail->Port = 465;
	
	$pmail->From = $from_addr;
	$pmail->FromName = $from_name;
	
	foreach ($to_list as $to_one)
		$pmail->addAddress($to_one);
	if ($bcc_list)
	{
		foreach ($bcc_list as $bcc_one)
			$pmail->addBCC($bcc_one);
	}
	$pmail->addReplyTo($from_addr, $from_name);
	
	$message = $message . "<br><br><br>
		DISCLAIMER: The information contained in this message is confidential and is intended for the addressee only and may also be privileged or exempt from disclosure under applicable law. The unauthorised use, disclosure, copying or alteration of this message is strictly forbidden and may be illegal. If you have received this message in error please notify the originator immediately and delete it from your system and do not copy, disclose or otherwise act upon any part of this e-mail or its attachments. Village Investigations Limited will not be liable for direct, special, indirect or consequential damages arising from any use or alteration of the contents of this message by a third party or as a result of any virus being passed on.<br>
Village Investigations Ltd reserves the right to monitor and record e-mail messages sent to and from this address for the purposes of investigating or detecting any unauthorised use of its system and ensuring its compliance policies are adhered to. Any opinion or other information in this e-mail or its attachments that does not relate to the business of Village Investigations Limited is personal to the sender and is not given or endorsed by Village Investigations Limited.<br>
<br>
Village Investigations Limited is a company registered in England and Wales with company number 2267884. Registered office: 97 Ewell Road, Surbiton, Surrey KT6 6AH<br>
<br>
Village Investigations Limited is a member of the Credit Services Association and authorised and regulated by the Financial Conduct Authority and a Data Protection Registration (No. Z5416641) issued by the Information Commissioner's Office.<br>
<br>
Village Investigations Limited operates an anti-bribery policy, which complies with the Bribery Act 2010.<br>
	";
	
	if ($attachment_file)
	{
		if (is_array($attachment_file))
		{
			$attachment_count = count($attachment_file);
			if ((!is_array($attachment_name)) || (count($attachment_name) != $attachment_count))
				$abort = 'Array of attachment files doesn\'t match attachment names';
			else
			{
				for ($ii = 0; $ii < $attachment_count; $ii++)
				{
					#if ($verbose)
					#	echo "Adding attachment \"{$attachment_file[$ii]}\" (\"{$attachment_name[$ii]}\")<br>";
					$pmail->addAttachment($attachment_file[$ii], $attachment_name[$ii]);
				}
			}
		}
		else
			$pmail->addAttachment($attachment_file, $attachment_name);
	}
	
	if ($abort == '')
	{
		if ($image_paths)
		{
			for ($ii=0; $ii < count($image_paths); $ii++)
				$pmail->AddEmbeddedImage($image_paths[$ii], $image_names[$ii]);
		}

		$pmail->isHTML(true);

		$pmail->Subject = $subject;
		$pmail->Body = $message;
		$pmail->AltBody = strip_tags($message);

		#if ($verbose)
		#	echo 'About to send mail...<br>';
		if ($pmail->send())
		{
			if ($verbose)
				dprint("An email has been sent to " . implode(', ', $to_list) . " with subject line \"{$subject}\"", true);
			return true;
		}
		else 
		{
			dprint('The email could not be sent.<br>Mailer Error: ' . $pmail->ErrorInfo, true, 'red');
			return false;
		}
	}
	else
	{
		echo "Mailing aborted: $abort";
		return false;
	}
}

?>
