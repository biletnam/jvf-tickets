<?php
//based on https://gist.github.com/xcommerce-gists/3440401#file-completelistener-php
// STEP 1: read POST data

// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
// Instead, read raw POST data from the input stream. 
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}

 
// STEP 2: POST IPN data back to PayPal to validate

$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set 
// the directory path of the certificate as shown below:
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) {
    // error_log("Got " . curl_error($ch) . " when processing IPN data");
    curl_close($ch);
    exit;
}
curl_close($ch);
 
$message = "";
//from http://stackoverflow.com/questions/6564251/generate-a-unique-key
$key = md5(microtime().rand());
$first = "";
$last = "";
$payer_email = "";
$t0=0;
$t1=0;


// STEP 3: Inspect IPN validation result and act accordingly

if (strcmp ($res, "VERIFIED") == 0) {
	if(strcmp ($_POST['txn_type'], "cart") == 0 && strcmp ($_POST['item_name1'], "Concert Tickets") == 0) {
		// The IPN is verified, process it:
		// check whether the payment_status is Completed
		// check that txn_id has not been previously processed
		// check that receiver_email is your Primary PayPal email
		// check that payment_amount/payment_currency are correct
		// process the notification

		// assign posted variables to local variables
		//$item_name = $_POST['item_name'];
		//$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		
		if(strcmp ($payment_status, "Completed") == 0) {
			$connection = mysqli_connect("","","","");
			$txn_id = mysqli_real_escape_string($connection,$txn_id);
			$test = mysqli_query($connection, "SELECT email FROM customers WHERE pid='$txn_id'");
			if(!$test) {
				mail('', 'JVF IPN', "transaction ".$txn_id." connection failed");
				die();
			}
			if(mysqli_num_rows($test)>0) {
				$row = mysqli_fetch_array($test,MYSQLI_ASSOC);
				mail('', 'JVF IPN', "Duplicate transaction from ".$payer_email." stored as ".$row["email"]);
			} else {
				if(strcmp ($receiver_email, "") == 0 && strcmp ($payment_currency, "USD") == 0) {
					$t = array(0,0,0,0);
					$prices = array(60,38,25,15);
					for($counter = 1;$counter<=$_POST['num_cart_items'];$counter++) {
						$item_number = $_POST['item_number'.$counter];
						$quantity = $_POST['quantity'.$counter];
						$gross = $_POST['mc_gross_'.$counter];
						if($item_number>34||$item_number<31) {
							mail('', 'JVF IPN', "wrong item for ".$txn_id);
							die();
						}
						$arrind = $item_number-31;
						$price = $quantity*$prices[$arrind];
						if($gross==$price) {
							$t[$arrind]=$quantity;
						}
						else {
							mail('', 'JVF IPN', "wrong price for ".$txn_id." item ".$item_number);
							die();
						}
					}
					$first = $_POST['first_name'];
					$last = $_POST['last_name'];
					
					$payer_email = mysqli_real_escape_string($connection,$payer_email);
					$first = mysqli_real_escape_string($connection,$first);
					$last = mysqli_real_escape_string($connection,$last);
					$t0=mysqli_real_escape_string($connection,$t[0]);
					$t1=mysqli_real_escape_string($connection,$t[1]);
					$t2=mysqli_real_escape_string($connection,$t[2]);
					$t3=mysqli_real_escape_string($connection,$t[3]);
					
					$test = mysqli_query($connection, "INSERT INTO customers (email,first,last,t1,t2,t3,t3s,pid,`key`) VALUES ('{$payer_email}', 
						'{$first}', '{$last}', '{$t0}', '{$t1}', '{$t2}', '{$t3}', '{$txn_id}', '{$key}')");
					if(!$test) {
						mail('', 'JVF IPN', "transaction ".$txn_id." not inserted");
						die();
					}
					$message = $txn_id." inserted";
				} else {
					mail('', 'JVF IPN', "transaction ".$txn_id." did not check out");
					die();
				}
			}
		} else {
			mail('', 'JVF Tickets', $payer_email." incomplete");
			die();
		}

		// IPN message values depend upon the type of notification sent.
		// To loop through the &_POST array and print the NV pairs to the screen:
		/*foreach($_POST as $key => $value) {
		  $message.= $key." = ". $value."<br>";
		}*/
	} else {
		die();
	}
} else if (strcmp ($res, "INVALID") == 0) {
    // IPN invalid, log for manual investigation
    mail('', 'JVF IPN', "The response from IPN was: " .$res);
	die();
}
mail('', 'JVF IPN', $message);
include('/ticket/setup.php');
?>