<?php
require_once 'Zend/Loader/StandardAutoloader.php';
$loader = new Zend\Loader\StandardAutoloader(array('autoregister_zf' => true));
$loader->register();

$message = '<img src="http://thejustinveatchfund.org/tickets/header.png" alt=""><br><br><span style="color:#000;">Thank you for purchasing tickets to our September 20<sup>th</sup> concert! Print out your tickets ';
if($t0+$t1>0) {
	$message.='and select your seats ';
}
$message.='from <a href="http://thejustinveatchfund.org/tickets/print.php?id='.$key.'&amp;email='.rawurlencode($payer_email).'">this link</a>.</span><br><br><span style="color:#000;">If you have any questions, don\'t hesitate to contact us by phone at [redacted] or email at [redacted].';

$mail = new Zend\Mail\Message();
$mail->addFrom('', 'JVF Tickets');
$mail->addTo($payer_email,$first.' '.$last);
$mail->setSubject('The Justin Veatch Fund Concert Tickets');
$mail->addBcc('');

$html = new Zend\Mime\Part($message);
$html->type = "text/html";
$body = new Zend\Mime\Message();
$body->setParts(array($html));
$mail->setBody($body);

$transport = new Zend\Mail\Transport\Sendmail();

try {
	$transport->send($mail);
} catch (Exception $e) {
	mail('', 'JVF Tickets', $payer_email." zend failed".$e);
}
?>