<?php
$seat = $_POST['seat'];
if(!isset($_POST['email'])||!isset($_POST['transaction'])||empty($seat)) {
	die('Form not submitted');
}
$connection = mysqli_connect("","","","");

$id = mysqli_real_escape_string($connection,$_POST['transaction']);
$email = mysqli_real_escape_string($connection,$_POST['email']);

$test = mysqli_query($connection, "SELECT t1,t2 FROM customers WHERE `key`='$id' AND email='$email'");
if(!$test) {
	die("Connection failed!");
}

if(mysqli_num_rows($test)==0) {
	die("Could not find your order");
}
if(mysqli_num_rows($test)>1) {
	die("could not locate your order");
}

$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
$prem=$row["t1"]+$row["t2"];
$meets = $row["t1"];

$test = mysqli_query($connection, "SELECT ticket FROM tickets WHERE transaction='$id' AND email='$email' AND NOT section='General Admission'");
if(!$test) {
	die("Connection failed!");
}
$rownum = mysqli_num_rows($test);

$reserved = count($seat);
if($reserved!==($prem-$rownum)) {
	die('You must reserve every premium seat you purchased.<br><br><a href="javascript:history.back()">Go back</a>');
}

foreach ($seat as $indiv) {
	$indiv = mysqli_real_escape_string($connection,$indiv);
	$row = $indiv{0};
	$col = substr($indiv, 1); 
	$test = mysqli_query($connection, "SELECT ticketid FROM premium WHERE `row`='$row' AND `column`='$col'");
	if(!$test) {
		die("connection failed");
	} 
	$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
	if(strlen($row['ticketid'])!=0) {
		die('Unfortunately those seats are no longer available. Please make a new selection. <br><br><a href="http://thejustinveatchfund.org/tickets/print.php?id='.$id.'&email='.rawurlencode($email).'">Try again</a>');
	}
}


foreach ($seat as $indiv) {
	$indiv = mysqli_real_escape_string($connection,$indiv);
	$row = $indiv{0};
	$col = substr($indiv, 1); 
	$unique = md5(microtime().rand());
	$test = mysqli_query($connection, "UPDATE premium SET `ticketid` = '$unique' WHERE `row`='$row' AND `column`='$col' AND `ticketid`=''");
	if(!$test) {
		die('Connection error!');
	}
	if(mysqli_affected_rows($connection)==0) {
		die('Unfortunately those seats are no longer available <br><br><a href="http://thejustinveatchfund.org/tickets/print.php?id='.$id.'&email='.rawurlencode($email).'">Try again</a>');
	}
	$meetg = 0;
	if($meets>0) {
		$meetg=1;
		$meets--;
	}
	$section = $row.' '.$col;
	$test = mysqli_query($connection, "INSERT INTO tickets (transaction,email,ticket,section,meet,student) VALUES ('{$id}', 
		'{$email}', '{$unique}', '{$section}', '{$meetg}', '0')");
	if(!$test) {
		die("Could not generate ticket");
	}	
}
header( 'Location: http://thejustinveatchfund.org/tickets/print.php?id='.$id.'&email='.rawurlencode($email) ) ;
?>