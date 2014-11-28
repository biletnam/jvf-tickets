<?php

if(!isset($_GET["id"])||!isset($_GET["email"])) {
	die("Error! Invalid!");
}

$id = $_GET["id"];
$email = $_GET["email"];

$connection = mysqli_connect("","","","");

$id = mysqli_real_escape_string($connection,$id);
$email = mysqli_real_escape_string($connection,$email);

$test = mysqli_query($connection, "SELECT first,last,t1,t2,t3,t3s FROM customers WHERE `key`='$id' AND email='$email'");
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

$ganum = $row["t3"]+$row["t3s"];
$first = $row["first"];
$last = $row["last"];
if($ganum>0) {
	$test = mysqli_query($connection, "SELECT ticket FROM tickets WHERE transaction='$id' AND email='$email' AND section='General Admission'");
	if(!$test) {
		die("Connection failed!");
	}
	$rownum = mysqli_num_rows($test);
	if($ganum>$rownum) {
		$students = $row["t3s"];
		for($counter = $rownum;$counter<$ganum;$counter++) {
			$ticket = md5(microtime().rand());
			$student = 0;
			if($students>0) {
				$student=1;
				$students--;
			}
			$test = mysqli_query($connection, "INSERT INTO tickets (transaction,email,ticket,section,meet,student) VALUES ('{$id}', 
				'{$email}', '{$ticket}', 'General Admission', '0', '{$student}')");
			if(!$test) {
				die("Could not generate ticket");
			}
		}
	}
}

$prem=$row["t1"]+$row["t2"];
if($prem>0) {
	$test = mysqli_query($connection, "SELECT ticket FROM tickets WHERE transaction='$id' AND email='$email' AND NOT section='General Admission'");
	if(!$test) {
		die("Connection failed!");
	}
	$rownum = mysqli_num_rows($test);
	if($prem>$rownum) {
		include('/ticket/seats.php');
		die();
	}
}

$test = mysqli_query($connection, "SELECT ticket,section,meet,student FROM tickets WHERE transaction='$id' AND email='$email'");
if(!$test) {
	die("Connection failed!");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>September Concert Tickets</title>
		<style type="text/css" media="print">
			.tickets {
				page-break-before: always;
			}
		</style>
	</head>
	<body onload="window.print()">
<?php 
	$counter = 1;
	$tickets = mysqli_num_rows($test);
	while($row = mysqli_fetch_array($test,MYSQLI_ASSOC)) { 
?>	
		<div <?php if($counter>1) echo 'class = "tickets"'; ?>>
			<p>Ticket <?php echo $counter.' of '.$tickets; ?></p>
			<img src="http://thejustinveatchfund.org/tickets/header.png" alt="">
			<h1>The Justin Veatch Fund Presents&#8230;</h1>
			<h2>Livingston Taylor at Yorktown Stage<br>
			Saturday, September 20, 2014 7pm</h2>
			<h1><strong>Name:</strong> <?php echo htmlentities($first.' '.$last); ?><br>
			<strong>Seat:</strong> <?php echo htmlentities($row["section"]); 
			if($row["meet"]===1) {echo '<br>Meet and Greet';} else if($row["student"]===1) {echo '<br>Requires Student ID';} ?></h1>
			<img src="https://chart.googleapis.com/chart?chs=300x300&amp;cht=qr&amp;chl=<?php echo 'http%3A%2F%2Fthejustinveatchfund.org%2Fticket%2Fconcert.php%3Femail%3D'.rawurlencode(rawurlencode($email)).'%26t%3D'.htmlentities($row["ticket"]); ?>&amp;choe=UTF-8" alt="This ticket is not valid without the QR code!" >
			<p>This ticket is good for the initial entry of one person. If you would like to leave Yorktown Stage and re&#45;enter, you must make arrangements with the ushers at the door <strong>before</strong> leaving.</p>
		</div>
<?php 
		$counter++;
	}
 ?>
	</body>
</html>