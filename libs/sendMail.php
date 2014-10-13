<?php
class SendMail{
	public function send($to, $fromName, $fromMail, $subject, $content){
		$boundary = md5(uniqid(microtime(), TRUE));
		 
		$headers = 'From: '.$fromName.' <'.$fromMail.'>'."\r\n";
		$headers .= 'Mime-Version: 1.0'."\r\n";
		$headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
		$headers .= "\r\n";
		 	 
		$message .= '--'.$boundary."\r\n";
		$message .= 'Content-type: text/html; charset=utf-8'."\r\n\r\n";
		$message .= $content."\r\n";
 
		$message .= '--'.$boundary."\r\n";
		 
		mail($to, $subject, $message, $headers);
	}
}