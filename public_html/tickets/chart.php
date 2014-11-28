<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>Seating Chart</title>
<link rel="stylesheet" type="text/css" media="all" href="http://thejustinveatchfund.org/tickets/style.css" />
<style type="text/css">
* {
	margin:0;
	padding:0;
}
</style>
</head>
<body>
						<div id="chartContainer">
							<div id="chart">
								<?php 
									$connection = mysqli_connect("","","","");
									$test = mysqli_query($connection, "SELECT ticketid FROM `premium` ORDER BY `premium`.`index` ASC");
									$letters = array('A','B','C','D','E','F','G');
									for($counter =1;$counter<8;$counter+=2) { 
										$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
										if(!empty($row['ticketid'])) {
											echo '<img src="http://thejustinveatchfund.org/tickets/reserved.png" alt="R" style="position:absolute; top:74px; left:'.(69+$counter*9).'px;"/>';
										}
									}
									for($outer = 1;$outer<5;$outer++) {
										for($counter =1;$counter<10;$counter+=2) { 
											$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
											if(!empty($row['ticketid'])) {
												echo '<img src="http://thejustinveatchfund.org/tickets/reserved.png" alt="R" style="position:absolute; top:'.floor(74+14.5*$outer).'px; left:'.(51+$counter*9).'px;"/>';
											}
										}
									} 
									for($counter =2;$counter<9;$counter+=2) { 
										$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
										if(!empty($row['ticketid'])) {
											echo '<img src="http://thejustinveatchfund.org/tickets/reserved.png" alt="R" style="position:absolute; top:74px; left:'.(447+$counter*9).'px;"/>';
										}
									} 
									for($outer = 1;$outer<5;$outer++) {
										for($counter =2;$counter<11;$counter+=2) { 
											$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
											if(!empty($row['ticketid'])) {
												echo '<img src="http://thejustinveatchfund.org/tickets/reserved.png" alt="R" style="position:absolute; top:'.floor(74+14.5*$outer).'px; left:'.(447+$counter*9).'px;"/>';
											}
										}
									} 
									for($outer = 1;$outer<7;$outer++) {
										for($counter =1;$counter<16;$counter++) { 
											$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
											if(!empty($row['ticketid'])) {
												echo '<img src="http://thejustinveatchfund.org/tickets/reserved.png" alt="R" style="position:absolute; top:'.floor(74+14.5*$outer).'px; left:'.floor(159+$counter*17.25).'px;"/>';
											}
										}
									} 
								?>									
								<img src="http://thejustinveatchfund.org/tickets/seating.png" alt="seating chart"/>
							</div>
						</div>
</body>
</html>