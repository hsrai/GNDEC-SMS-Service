<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04e
 *    
 *****************************************************************
 *  register.php
 *  Registers new users
 *
 *************************************************************/

// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	require_once(FILE_LIB_MAIL);	

// ** START SESSION **
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();
	$mail = new PHPMailer();
	$mail->CharSet = $lang['CHARSET'];        	
	$mail->SetLanguage(LANGUAGE_CODE, "lib/phpmailer/language/");
	$mail->From = 'noreply@'.$_SERVER['SERVER_NAME'];
	$mail->FromName = 'noreply@'.$_SERVER['SERVER_NAME'];

// ** DENY ACCESS IF REGISTRATION IS NOT ALLOWED
// If mode is "confirm", permission must be granted so that e-mail changes can be confirmed.
	if (($options->allowUserReg != 1) && ($_GET['mode'] != "confirm")) {
		reportScriptError("User registration has been turned off in this installation.");
		exit();
	}
	if($options->eMailAdmin = 1){
		$sql = "SELECT * FROM ". TABLE_USERS ." WHERE usertype='admin'";
		$admins = mysql_query($sql, $db_link);
		while ($tbl_admins = mysql_fetch_array($admins)) {
			$mail->AddBCC($tbl_admins['email']) ;
		}
		$copyAdmins = "Yes";
	}
// initial message
	$message = $lang[REG_NEW];

	if ($_POST['registerSubmit'])  {	
		global $feedback, $hidden_hash_var, $db_link;
		$username = $_POST['username'];
		$password1 =  $_POST['password1'];
		$password2 = $_POST['password2'];
		$email = $_POST['email'];
		//all vars present and passwords match?
		if ($username && $password1 && $password1==$password2 && $email && validate_email($email)) {
			//password and name are valid?
			if (account_namevalid($username) && account_pwvalid($password1)) {
				$username=strtolower($username);
				//does the name exist in the database?
				$sql="SELECT * FROM " .TABLE_USERS. " WHERE username='$username'";
				$result=mysql_query($sql, $db_link);
				if ($result && mysql_numrows($result) > 0) {
					$feedback .=  "ERR_USERNAME_RESERVED";
				} else {
					//create a new hash to insert into the db and the confirmation email
					$hash=md5($email.$hidden_hash_var);
					$sql="INSERT INTO ".TABLE_USERS." (username, usertype, password, email, confirm_hash, is_confirmed) ".
						"VALUES ('$username','user','". md5($password1) ."','$email', '$hash','0')";
					$result=mysql_query($sql, $db_link);
					if (!$result) {
						$feedback .= ' MySQL ERROR - '.mysql_error();
					} else {
						//send the confirm email 
						$message = $lang[SALUTATION]." ".$_POST['username'].",\n".
							$lang[REG_MAIL_MSG_1].
							"\n\n  http://" .$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']). "/register.php?mode=confirm&hash=$hash&email=$email".
							"\n\n".$lang[REG_MAIL_MSG_2];
						$mail->Subject = $lang[TAB].' - '.$lang[REG_CONFIRM_TITLE];			
						$mail->Body  = $message ;				
						$mail->AddAddress($email);
						if (!$mail->Send()) {
							reportScriptError($lang['ERR_MAIL_NOT_SENT'] . $mail->ErrorInfo);
						}else{
							$feedback = "ERR_USER_REGISTER_SUCCESS";
						}	
					} //end if !$result
				} // end if $result num rows
			} else {
				//$feedback .=  ' Account Name or Password Invalid ';
			} // end if account name valid
		} else {	
			$feedback .= "ERR_USER_REGISTER_MISSING_DATA";
		} //end if match
		$message = $lang[$feedback];		
	} //end if post registerSubmit	
			
	if ($_POST['lost'])  {
		global $message, $hidden_hash_var, $db_link, $lang;
		$username = $_POST['username'];
		$email = $_POST['email'];
		if ($email && $username) {
			$username=strtolower($username);
			$sql="SELECT * FROM " .TABLE_USERS. " WHERE username='$username' AND email='$email'";
			$result=mysql_query($sql, $db_link);
			if (!$result || mysql_numrows($result) < 1) {
				//no matching user found
				$message = $lang[ERR_USER_INCORRECT_NAME_OR_EMAIL];
			} else {
				//create a secure, new password
				$new_pass=strtolower(substr(md5(time().$username.$hidden_hash_var),1,14));
				//update the database to include the new password
				$sql="UPDATE ".TABLE_USERS." SET password='" .md5($new_pass). "' WHERE username='$username' LIMIT 1";
				$result=mysql_query($sql, $db_link);
				//send a simple email with the new password
				$mail->Subject = $lang[MAIL_LOST_PASSWORD_SUBJECT];		
				$mail->Body  = $lang[MAIL_LOST_PASSWORD_MESSAGE]. "\n".$new_pass;
				$mail->AddAddress($email);
				if (!$mail->Send()) {
					reportScriptError($lang['ERR_MAIL_NOT_SENT'] . $mail->ErrorInfo);
				}else{
					$message = $lang[ERR_USER_REGISTER_SUCCESS];	
				}
				$message = $lang[ERR_USER_NEW_PASSWORD];
			}
		} else {
			$message = $lang[ERR_USER_REQUIRED_NAME_OR_EMAIL];
		}
	}

	if ($_GET['mode'] == "confirm") {
		$hashFromURL=$_GET['hash'];
		$emailFromURL=$_GET['email'];
		// ** Use new function in userFunctions.php to confirm the GET data	
		user_confirm($hashFromURL,$emailFromURL);
		$message = stripslashes($lang[$feedback]);
	}
?>
<HTML>
<HEAD>
	<TITLE><?php echo $lang[TITLE_REGISTER].$lang[TITLE_TAB]; ?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	
	
</HEAD>

<BODY ONLOAD="document.register.username.focus();">

<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<TBODY>
<TR><TD ALIGN="center">
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
<TBODY>	
	<TR><TD><IMG SRC="images/title.gif" WIDTH=570 HEIGHT=90 ALT="" BORDER=0></TD></TR>
	<TR><TD CLASS="error" ALIGN="center" HEIGHT=80><?php
// PRINT ERROR MESSAGES IF ANY
	echo($message);
?></TD></TR>
<?php
	if ($_GET['mode'] != "confirm" ) {

?>
	<TR>
		<TD CLASS="data"><CENTER>
		<!----FORM NAME="register" METHOD="post" ACTION="<?php echo FILE_REGISTER."?login=OK"?>">
		<FORM NAME="register" METHOD="post" ACTION="<?php echo FILE_REGISTER?>">
		<BR>
		<P><B><?php echo $lang[LBL_USERNAME]; ?></B>
		<BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="username">
		<P><B><?php echo $lang[LBL_EMAIL]; ?></B>
		<BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="email">		
		<P><B><?php echo $lang[LBL_PASSWORD]; ?></B>
		<BR><INPUT TYPE="password" SIZE=20 CLASS="formTextbox" NAME="password1">		
		<P><B><?php echo $lang[LBL_PASSWORD_REPEAT]; ?></B>
		<BR><INPUT TYPE="password" SIZE=20 CLASS="formTextbox" NAME="password2">

		<P><INPUT TYPE="submit" CLASS="formButton" NAME="registerSubmit" VALUE="<?php echo  $lang[BTN_REGISTER]; ?>">
		<P><INPUT TYPE="submit" CLASS="formButton" NAME="lost" VALUE="<?php echo  $lang[BTN_LOST]; ?>">
		<P><A HREF="<?php echo(FILE_INDEX); ?>"><?php echo  $lang[BTN_CANCEL]?></A>

		</FORM>
		<BR><BR><BR><BR><BR>
		</CENTER></TD>
	</TR>
<?php
	}
	printFooter();
?>
</TBODY>
</TABLE>

</TD></TR>
</TBODY>
</TABLE>

</BODY>
</HTML>
