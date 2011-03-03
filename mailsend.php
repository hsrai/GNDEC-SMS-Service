<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *   
 *  
 *
 ****************************************************************
 *
 *  mailsend.php
 *  Delivery service for mailto.php
 *  Originally written by Joe Chen
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_LIB_MAIL);	
	require_once(FILE_CLASS_OPTIONS);

// ** START SESSION **
	session_start();
	
// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);


// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();
	
// ** CHECK FOR LOGIN **
//    list($userGroup, $userHomeName, $userHomePage, $userCapabilities) = checkForLogin($address_session_name, CAP_USER);
	checkForLogin('admin','user');

// ** GET SOME INFORMATION **
	if(empty($_POST['mail_from'])) {
		reportScriptError($lang['ERR_MAIL_NO_SENDER']);
	}


// ** COMPOSE THE MAIL **
	$mail = new PHPMailer();
	$mail->SetLanguage(LANGUAGE_CODE, "lib/phpmailer/language/");
//	$mail->IsSMTP();                                      // set mailer to use SMTP
//	$mail->Host = "smtp1.example.com;smtp2.example.com";  // specify main and backup server
//	$mail->SMTPAuth = true;     // turn on SMTP authentication
//	$mail->Username = "jswan";  // SMTP username
//	$mail->Password = "secret"; // SMTP password
	$mail->From = $_POST['mail_from'];
	$mail->FromName = $_POST['mail_from_name'];
	// GET EMAIL ADDRESSES
	// There are two ways that mailto.php can send e-mail addresses, based on the two ways
	// of sending. The first is the mailing list and addresses are stored in $_POST['mail_to']
	// as an array. The second method allows the user to write in e-mail addresses and they will
	// be stored in $_POST['mail_to'] as a string. In the event that it is a string (with
	// commas separating each address) we must break up that string into an array.
	// Note: We can split on commas only. any resulting whitespace is trimmed automatically by PHPMailer.
	$mail->ClearAddresses();
	$mailto = $_POST['mail_to'];
	if (is_string($mailto)) {
		$mailto = explode(",", $mailto);
	}
	for ($a=0; $a < count($mailto); $a++) {
		// $mail->AddAddress("josh@example.net", "Josh Adams"); // set names is possible? maybe done later
		$mail->AddAddress($mailto[$a]);
	}
	
	$mail->WordWrap = 50;  
	$mail->CharSet = $lang['CHARSET'];                             
	$mail->Subject = stripslashes($_POST['mail_subject']);		
	$mail->Body    = stripslashes($_POST['mail_body']);	
	$mail->ClearCCs();
	$mail->ClearBCCs();
	$mail->AddCC($_POST['mail_cc']) ;	// To get CC to work, Howe had to modify  class.phpmailer.php at around line 780
	$mail->AddBCC($_POST['mail_bcc']) ;

				

//      ** SEND! **
	if (!$mail->Send()) {
		reportScriptError($lang['ERR_MAIL_NOT_SENT'] . $mail->ErrorInfo);
	}
	
?>
<html>
<head>
	<title><?php echo $lang[TAB]. ' - '. $lang[MSG_MAIL_SENT_TITLE]?></title>
	<link rel="stylesheet" href="styles.css" type="text/css">
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
	<meta http-equiv="PRAGMA" content="NO-CACHE">
	<meta http-equiv="EXPIRES" content="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>"
	
</head>
<body>
<p>

<?php 
echo"
<h3>". $lang[MSG_MAIL_SENT]."</h3>
<h4>". $lang[MAIL_SUBJ]. ": ".$mail->Subject . "</h4>
<h4>TO: ";
reset($mailto);
for ($a=0; $a < count($mailto); $a++) {
	echo $mailto[$a]. "<br>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;";
}
echo "</h4>";
if($_POST['mail_cc']){
	echo "<h4>CC: ". $_POST['mail_cc']."</h4>";
}
if($_POST['mail_bcc']){
	echo "<h4>BCC: ". $_POST['mail_bcc']."</h4><p>";
}
echo "<h4>". $lang[MAIL_MSG].": </h4><br>
<div class=\"error\">". $mail->Body."</div>
<br><br><center><b><a href=\"".FILE_LIST."\">$lang[BTN_LIST]</a></b></center>"; ?>
</body>
</html>
