<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *   
 ****************************************************************
 *  users.php
 *  Manages users of the Address Book.
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_LIB_MAIL);	
	require_once(FILE_CLASS_OPTIONS);
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin("admin","user");
	
// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();
	$nuser = $_POST['newuserName'];
	
	

// ** PERFORM USER UPDATE TASKS **
	$actionMsg = "";
	switch($_GET['action']) {
		// ADD A NEW USER (admin only)
		case "adduser":
			checkForLogin("admin");
			
			
			
			
			// Perform checks and then add if things are OK
			$newuserName = $_POST['newuserName'];
			if ((!empty($newuserName)) && (isAlphaNumeric($newuserName))) {
				if ($_POST['newuserPass'] == $_POST['newuserConfirmPass']) {
					$newuserPass = $_POST['newuserPass'];
					$newuserFullName = $_POST['newuserFullName'];
					$newuserMobile = $_POST['newuserMobile'];
					$newuserType = $_POST['newuserType'];
					$newuserNature = $_POST['newuserNature'];
					$newuserDepartment = $_POST['newuserDepartment'];
					$newuserBatch = $_POST['newuserBatch'];
					$newuserDesignation = $_POST['newuserDesignation'];
					$newuserEmail = $_POST['newuserEmail'];   // NOT VALIDATED
					$sql = "INSERT INTO ". TABLE_USERS ." (fullname, username, usertype, nature, password, email, mobile, is_confirmed) VALUES ('$newuserFullName','$newuserName', '$newuserType', '$newuserNature', MD5('$newuserPass'), '$newuserEmail','$newuserMobile', 1)";
					mysql_query($sql, $db_link);
					$opps = mysql_errno();
					if($opps ==1062) {
						$actionMsg = $lang['ERR_USERNAME_DUPL'];
						break;
					}elseif ($opps != 0){
						die(ReportSQLError($sql));
					}	
					$actionMsg =  $newuserName.' '.$lang['USR_ADDED'];
				}
				else {
					
					$actionMsg = $lang['ERR_USER_PASSWORD_SHORT'];
				}
			}
			else {
				$actionMsg = $lang['ERR_USERNAME_ILLEGAL_CHARS'];
			}
			
			
			
			
			if(isset($_POST['newuserNature']))
			{
				if($_POST['newuserNature']=="teacher"){
					echo "<h1>Please Provide This Additional Info :-</h1>";
				echo "<FORM ACTION = 'users.php?action=addinfo' METHOD = 'post'>
				<B>Department</B>
	              			<TD WIDTH=150 CLASS='data'>
	              			
	              				<SELECT NAME='newuserDepartment' CLASS='formSelect'>
	              				<OPTION VALUE='it' SELECTED>Information Technology</OPTION>
	              				<OPTION VALUE='cse'>Computer Science</OPTION>
	              				<OPTION VALUE='ece'>Electronics And Commumication</OPTION>
	              				<OPTION VALUE='ee'>Electrical</OPTION>
	              				<OPTION VALUE='pe'>Production</OPTION>
	              				<OPTION VALUE='me'>Mechanical</OPTION>
	              				<OPTION VALUE='ce'>Civil</OPTION>
	              				<OPTION VALUE='mca'>MCA</OPTION>
	              				<OPTION VALUE='mba'>MBA</OPTION>
							</SELECT></TD></TR>
						
							<INPUT TYPE='hidden' name ='nnnuser' value='$nuser' />
							<TR VALIGN='top' >
						<TD WIDTH=100 CLASS='data' STYLE='text-align:right'><B>Designation</B></TD>
	              			<TD WIDTH=150 CLASS='data'>
	              				<SELECT NAME='newuserDesignation' CLASS='formSelect'>
	              				<OPTION VALUE='hod' SELECTED>Head Of Department</OPTION>
	              				<OPTION VALUE='assistantprofessor'>Assistant Professor</OPTION>
	              			   <OPTION VALUE='associateprofessor'>Associate Professor</OPTION>
	              			   	              				
							</SELECT></TD></TR>	
							<INPUT TYPE='submit' CLASS='formButton' NAME='addUser' VALUE='add_new_teacher'/>
							</FORM>";
							}elseif($_POST['newuserNature']=="student"){
								echo "<h1>Please Provide This Additional Info :-</h1>";
								echo"<FORM ACTION = 'users.php?action=addinfo' METHOD = 'post'>
							
							<TR VALIGN='top'>
							
						<B>Batch</B>
	              			<TD WIDTH=150 CLASS='data'>
	              				<SELECT NAME='newuserBatch' CLASS='formSelect'>
	              				<OPTION VALUE='2007-2011' SELECTED>2007-2011</OPTION>
	              				<OPTION VALUE='2008-2012'>2008-2012</OPTION>
	              				<OPTION VALUE='2009-2013'>2009-2013</OPTION>
	              				<OPTION VALUE='2010-2014'>2010-2014</OPTION>
	              				<OPTION VALUE='2011-2015'>2011-2015</OPTION>
	              				<OPTION VALUE='2012-2016'>2012-2016</OPTION>
	              				<OPTION VALUE='2013-2017'>2013-2017</OPTION>
	              				<OPTION VALUE='2014-2018'>2014-2018</OPTION>
	              				<OPTION VALUE='2015-2019'>2015-2019</OPTION>
							</SELECT></TD></TR>
							<B>Department</B>
	              			<TD WIDTH=150 CLASS='data'>
	              			
	              				<SELECT NAME='newuserDepartment' CLASS='formSelect'>
	              				<OPTION VALUE='it' SELECTED>Information Technology</OPTION>
	              				<OPTION VALUE='cse'>Computer Science</OPTION>
	              				<OPTION VALUE='ece'>Electronics And Commumication</OPTION>
	              				<OPTION VALUE='ee'>Electrical</OPTION>
	              				<OPTION VALUE='pe'>Production</OPTION>
	              				<OPTION VALUE='me'>Mechanical</OPTION>
	              				<OPTION VALUE='ce'>Civil</OPTION>
	              				<OPTION VALUE='mca'>MCA</OPTION>
	              				<OPTION VALUE='mba'>MBA</OPTION>
							</SELECT></TD></TR>
														<INPUT TYPE='hidden' name ='nnnuser' value='$nuser' />

							<INPUT TYPE='submit' CLASS='formButton' NAME='addUser' VALUE='add_new_student'/>";}
			}
			
			
			
			
		
		break;

		// DELETE A USER (admin only)
		case "deleteuser":
			checkForLogin("admin");
			// Check to see if a user was given
			if (empty($_GET['id'])) {
				ReportScriptError($lang['ERR_USERNAME_NONE']);
				break;
			}
			// Check to see if user exists in the database
			$sql = "SELECT username, usertype FROM ". TABLE_USERS ." WHERE id=". $_GET['id'] ." LIMIT 1";
			$deluser = mysql_query($sql, $db_link)
				or die(ReportSQLError($sql));
			if (mysql_num_rows($deluser)<1) {
				ReportScriptError($lang['ERR_USERNAME_NON_EXIST']);
				break;
			}
			// Get the username and type
			$deluser = mysql_fetch_array($deluser);
			$deluserType = $deluser['usertype'];
			$deluserName = $deluser['username'];
			// Check to see if user is last remaining admin
			if ($deluserType == "admin") {
				$sql = "SELECT usertype FROM ". TABLE_USERS ." WHERE usertype='admin'";
				$isLastAdmin = mysql_query($sql, $db_link)
					or die(ReportSQLError($sql));
				if (mysql_num_rows($isLastAdmin)<=1) {
					$actionMsg = $lang['ERR_USER_LAST_ADMIN'];
					break;
				}
			}
			// Perform the deletion if everything checks out
			$sql = "DELETE FROM ". TABLE_USERS ." WHERE id=". $_GET['id'] ." LIMIT 1";		
			mysql_query($sql, $db_link)
				or die(ReportSQLError($sql));
			$actionMsg = $deluserName.' '. $lang['USR_DELETED'];
			break;

		// CHANGE PERSONAL OPTIONS
		
		case "confirm":
			$id = $_GET['id'];
			$sql = "UPDATE ". TABLE_USERS ." SET is_confirmed=1 WHERE id =  $id";
			$doConfirm = mysql_query($sql, $db_link)
					or die(ReportSQLError($sql));
			$holder = explode(".",$lang['ERR_USER_HASH_CONFIRMED']); //rather than make new $lang[var], chop of first sentence of this thing		
			$actionMsg = $holder[0];
		break;
		
		case "co":		
			$options->save_user();
			$options->set_user();
			$actionMsg = $lang['MSG_PREF_CHANGED'];
		break;

		case "ro":		
			$options->reset_user();
			$options->set_user();
			$actionMsg = $lang['MSG_PREF_RESET'];
		break;		

		// CHANGE PASSWORD (all users)
		case "changepass":
			// Check to see if password and confirmation matches
			if ($_POST['passwordNew'] == $_POST['passwordNewRetype']) {
				// SQL query checks to make sure username and old password is corrrect.
				$sql = "UPDATE ". TABLE_USERS ." SET password=MD5('". $_POST['passwordNew'] ."') WHERE username='". $_SESSION['username'] ."' AND password=MD5('". $_POST['passwordOld'] ."') LIMIT 1";
				$updatePassword = mysql_query($sql, $db_link)
					or die(ReportSQLError($sql));
				if (mysql_affected_rows()<1) {
					$actionMsg = $lang['ERR_USER_PASSWORD_WRONG'];
				}
				else {
					$actionMsg = $lang['ERR_USER_PASSWORD_CHANGED'];
				}
			}
			else {
				$actionMsg = $lang['ERR_USER_PASSWORD_SHORT'];
			}
		break;

		// CHANGE EMAIL (all users)
		case "changeemail":
			$username = $_SESSION['username'];
			$new_email = $_POST['emailNew'];
			if (validate_email($new_email)) {
				$hash = md5($new_email.$hidden_hash_var);
				//change the confirm hash in the db but not the email - 
				//send out a new confirm email with a new hash to complete the process
				$sql = "UPDATE " .TABLE_USERS. " SET confirm_hash='$hash' , is_confirmed = 0 WHERE username='$username' LIMIT 1";
				$result = mysql_query($sql, $db_link);
				if (!$result) {
					//if (!$result || mysql_affected_rows($result) < 1) {
					$feedback .= ' There was a problem updating your e-mail address. ';
					// This used to double check for incorrect username and password, but these
					// are things that should already hopefully be taken care of in a login screen.
					// However, entering the same e-mail address as before will also cause
					// mysql_affected_rows to equal 0, so the error message has changed.
				} else {
					$mail = new PHPMailer();
					$mail->SetLanguage(LANGUAGE_CODE, "lib/phpmailer/language/");
					$mail->From = 'noreply@'.$_SERVER['SERVER_NAME'];
					$mail->FromName = 'noreply@'.$_SERVER['SERVER_NAME'];						
					$message = $lang['SALUTATION']." $username,\n".
					$lang['EMAIL_CHANGE'].
					"\n\n  http://" .$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']). "/register.php?mode=confirm&hash=$hash&email=$new_email";
					$mail->Subject = $lang[TAB].' - '.$lang['EMAIL_CHANGE_SUBJ'];			
					$mail->Body  = $message ;				
					$mail->AddAddress($new_email);
					if (!$mail->Send()) {
						reportScriptError($lang['ERR_MAIL_NOT_SENT'] . $mail->ErrorInfo);
					}else{
						$actionMsg = $lang['MSG_EMAIL_CHANGED'];						
					}	
				}
			} else {
				$actionMsg .= $lang['ERR_USER_EMAIL_INVALID'];
			}
		break;
		
		case "addinfo":
		//$nnuser = $_POST['nnnuser'];
		$nuser = $_POST['nnnuser'];
					$newuserDepartment = $_POST['newuserDepartment'];
					$newuserBatch = $_POST['newuserBatch'];
					$newuserDesignation = $_POST['newuserDesignation'];
					
					$sqlu = "UPDATE ". TABLE_USERS ." SET department='".$newuserDepartment."', batch='".$newuserBatch."', designation = '".$newuserDesignation."' WHERE username = '".$nuser."'";
					mysql_query($sqlu, $db_link);
					
					echo "<h1>New User ". strtoupper($nuser) . " has been added</h1>" ;
					

		// DEFAULT
		default:
		break;
	}

?>
<HTML>
<HEAD>
	<TITLE><?php echo $lang['TITLE_TAB']." - ".$lang['LBL_USR_ACCT_SET']  ?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	
	<SCRIPT LANGUAGE="JavaScript">
	<!--

	function changeUserOptions() {
		document.PersonalOptions.submit();
	}

	// -->
	</SCRIPT>
</HEAD>
<BODY>
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
<TR align="right"><TD ><b><A HREF="<?php echo(FILE_LIST); ?>"><?php echo $lang['BTN_RETURN']?></A></b></TD> </TR>
 	<TR><TD CLASS="headTitle"><?php echo $lang['LBL_USR_ACCT_SET']. " ".$lang['LBL_FOR']. " ".$_SESSION[username];?></TD> </TR>
	<TR><TD CLASS="infoBox"> 
       <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
		<?php
		// DISPLAY ACTION MESSAGE, IF ANY
		if (!empty($actionMsg)) {
			?>
           		<TR VALIGN="top">
             		<TD CLASS="data"><B><FONT STYLE="color:#FF0000"><?php echo($actionMsg); ?></FONT></B></TD></TR>

			<?php
		}
		// DISPLAY USER MANAGEMENT SETTINGS, IF USER IS ADMIN
		if ($_SESSION['usertype'] == "admin") {
			// Retrieve user account settings.
		    	$r_users = mysql_query("SELECT * FROM " . TABLE_USERS, $db_link)
				or die(reportSQLError("SELECT * FROM " . TABLE_USERS));
			?>
           		<TR VALIGN="top"><TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_USR_ADD_USER']?></TD></TR>
			<TR VALIGN="top"><TD CLASS="data"><?php echo $lang['USR_HELP_ADD']?><P>
				<FORM NAME="addUser" ACTION="users.php?action=adduser" METHOD="post">
					<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=500>
					<TR VALIGN="top">
						<TD WIDTH=200 CLASS="data" STYLE="text-align:right"><B><?php echo "Full Name"?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="text" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="newuserFullName" VALUE="" MAXLENGTH=15></TD>
						<TR VALIGN="top">
						<TD WIDTH=200 CLASS="data" STYLE="text-align:right"><B><?php echo "Username"?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="text" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="newuserName" VALUE="" MAXLENGTH=15></TD>
						<TD WIDTH=150 CLASS="data" ROWSPAN=5 VALIGN="bottom"><INPUT TYPE="submit" CLASS="formButton" NAME="addUser" VALUE="<?php echo $lang['BTN_ADD']?>"></TD></TR>
						<TR VALIGN="top">
						<TD WIDTH=200 CLASS="data" STYLE="text-align:right"><B><?php echo $lang['LBL_EMAIL']?></B> <?php echo "(Compulsory)"?></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="text" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="newuserEmail" VALUE="" MAXLENGTH=50></TD></TR>
	              			<TR VALIGN="top">
						<TD WIDTH=200 CLASS="data" STYLE="text-align:right"><B><?php echo $lang['LBL_PHONE1']?></B> <?php echo "(Compulsory)"?></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="text" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="newuserMobile" VALUE="+91" MAXLENGTH=50></TD></TR>
						<TR VALIGN="top" >
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B><?php echo $lang['LBL_USERTYPE']?></B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              				<SELECT NAME="newuserType" CLASS="formSelect">
	              				<OPTION VALUE="user" SELECTED><?php echo $lang['LBL_NORMAL']?></OPTION>
	              				<OPTION VALUE="admin"><?php echo $lang['LBL_ADMIN']?></OPTION>
							</SELECT></TD></TR>
							
							
							
							
							
							
							<TR VALIGN="top" >
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Category</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              				<SELECT NAME="newuserNature" CLASS="formSelect">
	              				<OPTION VALUE="student" SELECTED>Student</OPTION>
	              				<OPTION VALUE="teacher">Teacher</OPTION>
							</SELECT></TD></TR>
							
							
							
							<!--<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Department</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              			
	              				<SELECT NAME="newuserDepartment" CLASS="formSelect">
	              				<OPTION VALUE="it" SELECTED>Information Technology</OPTION>
	              				<OPTION VALUE="cse">Computer Science</OPTION>
	              				<OPTION VALUE="ece">Electronics And Commumication</OPTION>
	              				<OPTION VALUE="ee">Electrical</OPTION>
	              				<OPTION VALUE="pe">Production</OPTION>
	              				<OPTION VALUE="me">Mechanical</OPTION>
	              				<OPTION VALUE="ce">Civil</OPTION>
	              				<OPTION VALUE="mca">MCA</OPTION>
	              				<OPTION VALUE="mba">MBA</OPTION>
							</SELECT></TD></TR>
							<TR VALIGN="top" >
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Designation</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              				<SELECT NAME="newuserDesignation" CLASS="formSelect">
	              				<OPTION VALUE="hod" SELECTED>Head Of Department</OPTION>
	              				<OPTION VALUE="assistantprofessor">Assistant Professor</OPTION>
	              			   <OPTION VALUE="lecturer">Lecturer</OPTION>
	              			   	              				
							</SELECT></TD></TR>	
							
							<TR VALIGN="top">
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Batch</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              				<SELECT NAME="newuserBatch" CLASS="formSelect">
	              				<OPTION VALUE="2007-2011" SELECTED>2007-2011</OPTION>
	              				<OPTION VALUE="2008-2012">2008-2012</OPTION>
	              				<OPTION VALUE="2009-2013">2009-2013</OPTION>
	              				<OPTION VALUE="2010-2014">2010-2014</OPTION>
	              				<OPTION VALUE="2011-2015">2011-2015</OPTION>
	              				<OPTION VALUE="2012-2016">2012-2016</OPTION>
	              				<OPTION VALUE="2013-2017">2013-2017</OPTION>
	              				<OPTION VALUE="2014-2018">2014-2018</OPTION>
	              				<OPTION VALUE="2015-2019">2015-2019</OPTION>
							</SELECT></TD></TR>-->
							
							
							
							
							
												
												
							<TR VALIGN="top">
						<TD WIDTH=200 CLASS="data" STYLE="text-align:right"><B><?php echo $lang['LBL_PASSWORD']?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="password" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="newuserPass" VALUE="" MAXLENGTH=20></TD></TR>
						<TR VALIGN="top">
						<TD WIDTH=200 CLASS="data" STYLE="text-align:right"><B><?php echo $lang['LBL_PASSWORD_REPEAT']?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="password" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="newuserConfirmPass" VALUE="" MAXLENGTH=20></TD></TR>
					</TABLE>
				</FORM>
	</TD></TR>

	<TR VALIGN="top"><TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_USR_MGMT']?></TD></TR>
	<TR  valign="top"><TD CLASS="data">
	<!-- USER LIST BOX --><P>
		<TABLE border="0" cellpadding="3" cellspacing="0" >
		<?php
		while ($t_users = mysql_fetch_array($r_users)) {
			$disp_confirmed = $t_users['is_confirmed'];
			$disp_username = $t_users['username'];
			$disp_usertype = $t_users['usertype'];
			$disp_email = $t_users['email'];
			$disp_userid = $t_users['id'];
			if ($disp_usertype == "admin") {
				$disp_usertype = $lang['LBL_ADMIN'];
			}
			if ($disp_usertype == "user") {
				$disp_usertype = $lang['LBL_NORMAL'];
			}
		?>
		<TR  valign="top">
		<TD WIDTH=30 CLASS="data">&nbsp;</TD>
	       <TD width="70%" class="data"><B><?php echo($disp_username); ?></B> <?php if ($disp_email) { echo("($disp_email)"); } ?> <FONT STYLE="font-size:90%">[<?php echo($disp_usertype); ?>]</A></TD>
	       <TD width="20%" class="data"><A HREF="<?php echo(FILE_USERS."?action=deleteuser&id=$disp_userid"); ?>"><B><?php echo $lang['BTN_DELETE']?></B></A></TD>
	       <?php
	       if($disp_confirmed==0){
	       	echo "<TD  width=\"10%\" class=\"data\"><A HREF=\"".FILE_USERS."?action=confirm&id=$disp_userid\"><B>".$lang['LBL_CONFIRM']."</B></A></TD>";
	  	 }
	       ?>
	           			</TR>
<?php
			}
?>
					</TABLE>
					<!-- END BOX -->	
              </TD>
           </TR>
<?php
	}
?>


           <TR VALIGN="top">
              <TD WIDTH=560 CLASS="listHeader"><?php echo $lang['LBL_CHANGE_PSWD']?></TD>
           </TR>
           <TR VALIGN="top">
              <TD CLASS="data">
					<?php echo $lang['USR_HELP_PSWD']?>
                	<!-- CHANGE PASSWORD BOX -->
					<P>
					<FORM NAME="changePassword" ACTION="<?php echo(FILE_USERS."?action=changepass"); ?>" METHOD="post">
					<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=500>
						<TR VALIGN="top">
							<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['LBL_PASSWORD_OLD']?></B></TD>
							<TD WIDTH=150 CLASS="data"><INPUT TYPE="password" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="passwordOld" VALUE=""></TD>
	           			</TR>
						<TR VALIGN="top">
							<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['LBL_PASSWORD_NEW']?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="password" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="passwordNew" VALUE=""></TD>
	           			</TR>
						<TR VALIGN="top">
							<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['LBL_PASSWORD_RETYPE']?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="password" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="passwordNewRetype" VALUE=""></TD>
							<TD WIDTH=150 CLASS="data" ROWSPAN=3 VALIGN="bottom"><INPUT TYPE="submit" CLASS="formButton" NAME="changePassword" VALUE="<?php echo $lang['BTN_PASSWORD_CHANGE']?>"></TD>
	           			</TR>
					</TABLE>
					</FORM>
					<!-- END BOX -->
              </TD>
           </TR>


           <TR VALIGN="top">
              <TD WIDTH=560 CLASS="listHeader"><?php echo $lang['LBL_EMAIL_ADDRESS_CHANGE']?></TD>
           </TR>
           <TR VALIGN="top">
              <TD CLASS="data">
<?php
	// GET THE USER'S EMAIL ADDRESS
	$r_user = mysql_fetch_array(mysql_query("SELECT email FROM " . TABLE_USERS . " AS users WHERE username='". $_SESSION['username'] ."' LIMIT 1", $db_link))
		or die(reportSQLError());
	$email = $r_user['email'];
	if ($email) {
		echo($lang['USR_HELP_EMAIL_NEW']."<B> $email </B>. ".$lang['USR_HELP_EMAIL_NEW2']);
	}
	else {
		echo($lang['USR_HELP_EMAIL_NONE']);
	}
	echo(" ".$lang['USR_HELP_EMAIL_CONFIRM']);
?>
                	<!-- CHANGE EMAIL BOX -->
					<P>
					<FORM NAME="changeEmail" ACTION="<?php echo(FILE_USERS."?action=changeemail"); ?>" METHOD="post">
					<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=500>
						<TR VALIGN="top">
							<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['LBL_EMAIL_ADDRESS_NEW']?></B></TD>
	              			<TD WIDTH=150 CLASS="data"><INPUT TYPE="text" SIZE=20 STYLE="width:120px;" CLASS="formTextbox" NAME="emailNew" VALUE=""></TD>
							<TD WIDTH=150 CLASS="data"><INPUT TYPE="submit" CLASS="formButton" NAME="changeEmail" VALUE="<?php echo $lang['BTN_EMAIL_CHANGE']?>"></TD>
	           			</TR>
					</TABLE>
					</FORM>
					<!-- END BOX -->
		
              </TD>
           </TR>

<!--- NEW AREA TO DISPLAY AND ACQUIRE PERSONAL OPTIONS WHICH OVER RIDE THE GLOBAL OPTIONS SET BY ADMIN IN OPTIONS --->

	<TR VALIGN="top"><TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['OPT_ASST_PERS_LBL']?></TD>	</TR>
	<TR><TD COLSPAN =3 width= 500 class="data"><?php echo$lang['OPT_ASST_PERS_HELP']?></TD></TR>
		<FORM NAME="PersonalOptions" ACTION="<?php echo(FILE_USERS."?action=co"); ?>" METHOD="post">
			<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=500>
			<TR VALIGN="top">
			<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_BIRTHDAY_DISPLAY_LBL']?></B></TD>
			<TD WIDTH=60 CLASS="data"><?php
					if ($options->user_options['bdayDisplay'] == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"bdayDisplay\" VALUE=\"1\"$check>");
					$check = "";
				?>
			</TD>
			<TD WIDTH=300 CLASS="data">
				<?php echo $lang['OPT_BIRTHDAY_DISPLAY_HELP']?><br><b>
				<?php echo $lang['LBL_DEFAULT']?>:</B> </b><?php echo $lang['OPT_ON']?>
			</TD>
			</TR>
			<TR VALIGN="top">
			<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_BIRTHDAY_DAYS_LBL']?></B></TD>
			<TD WIDTH=60 CLASS="data"><INPUT TYPE="text" SIZE=3 STYLE="width:30px;" CLASS="formTextbox" NAME="bdayInterval" VALUE="<?php echo($options->user_options['bdayInterval']); ?>" MAXLENGTH=3></TD>
			<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_BIRTHDAY_DAYS_HELP']?><br><b>
					<?php echo $lang['LBL_DEFAULT']?>:</B> </b> 21 <?php echo $lang['OPT_DAYS']?>
			</TR>				
			<TR VALIGN="top">
			<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_OPEN_POPUP_LBL']?></B></TD>
			<TD WIDTH=60 CLASS="data"><?php
					if ($options->user_options['displayAsPopup'] == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"displayAsPopup\" VALUE=\"1\"$check>");
					$check = "";
					?>
			</TD>
			<TD WIDTH=300 CLASS="data">
				<?php echo $lang['OPT_OPEN_POPUP_HELP']?>
			</TD>
			</TR>
			<?php /* $useMailScript */ ?>
			<TR VALIGN="top">
			<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_USE_MAIL_SCRIPT_LBL']?></B></TD>
			<TD WIDTH=60 CLASS="data"><?php
				if ($options->user_options['useMailScript'] == 1) {
						$check = " CHECKED";
				}
				echo("<INPUT TYPE=\"checkbox\" NAME=\"useMailScript\" VALUE=\"1\"$check>");
				$check = "";
				?>
			</TD>
			<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_USE_MAIL_SCRIPT_HELP']?>
			</TD>
			</TR>
			<TR VALIGN="top">
			<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_LANGUAGE_LBL']?></B></TD>
			<TD WIDTH=360 CLASS="data" COLSPAN=2>
			<SELECT NAME="language" CLASS="formSelect" STYLE="width:160px;">
			<?php
	// ** LANGUAGE DROP DOWN GENERATION 
	// Obtain the list of language modules from the 'languages' directory.
	$dh = opendir("languages") or die ("Open Directory failed"); 
	while (false !== ($filename = readdir($dh))) { 
		if ($filename == "." OR $filename == "..") continue;
		$files[] = $filename; 
	} 
	sort($files); 
	closedir($dh);

	// Generate the selections

	// This may not necessary be the quickest way to do it, but it works.
	for ($i = 0; $i < count($files); $i++) { 
		// Files will be parsed to obtain the value of LANGUAGE_NAME.
		// If the language name cannot be found, then it must be a faulty module (or not a module at all!) -- and it will not be displayed in the drop down list.
		$languagename = implode(" ", file("languages/" . $files[$i]));
  		$languagename = explode("LANGUAGE_NAME', \"", $languagename, 2);  // Find the variable name and the first set of double quotes
   		$languagename = explode("\"", $languagename[1], 2);              // Find the second set of double quotes
		// The result should be the name of the language. If nothing is found, display no option.
		if ($languagename[0] != "") {
			// value used is the filename minus extension.
			$filename = (explode(".", $files[$i]));
    		echo("<option value=\"" . $filename[0] . "\"" . (($filename[0] == $options->language) ? " selected" : "") . ">" . $languagename[0] . "</option>\n");
		}
	}

	
     ?>
			</SELECT>
			<BR><?php echo $lang['OPT_LANGUAGE_HELP']?>
			<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> english </TD></TR>	
			
		<?php /* $defaultLetter */ 
			$abc=array(A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z);
		?>
		
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_VIEW_LTR_LABEL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<SELECT NAME="defaultLetter" CLASS="formSelect" STYLE="width:160px;">	
							<OPTION VALUE="0">(off)</OPTION>
<?php
		
	foreach ($abc as $letter){
		echo("						<OPTION VALUE=\"$letter\"");
		if ($letter == $options->defaultLetter) {
			echo(" SELECTED");
		}
		echo(">$letter</OPTION>\n");
	}
	
?>
</SELECT>
					<?php echo $lang['OPT_VIEW_LTR_HELP']?>
				</TD>
			</TR>

			<?php /* $limitEntries */?>
	<!--		<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_LIMIT_ENTRIES_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<INPUT TYPE="text" NAME="limitEntries" VALUE="<?php echo $options->limitEntries ?>"
					<?php echo $lang['OPT_LIMIT_ENTRIES_HELP']?>
				</TD>
			</TR>-->
							
			<tr valign="top">
				<td colspan=2 align="right" class="data"><a href="<?php echo(FILE_USERS."?action=ro") ?>"><b><?php echo $lang['BTN_RESET_USER_OPT']?></b></a></td>
				<td colspan=1 align="right" class="data"><a href="#" onClick="changeUserOptions(); return false;"><b><?php echo $lang['BTN_CHANGE_OPT']?></b></a></td>
			</tr>
			</TABLE>
		</FORM>		
		<TR VALIGN="top">
		<TD WIDTH=560 COLSPAN=3 CLASS="navmenu">
		<A HREF="<?php echo(FILE_LIST); ?>"><?php echo $lang['BTN_RETURN']?></A>
		</TD></TR>
	</TABLE>
	</TD></TR>
</TABLE>

</CENTER>
</BODY>
</HTML>
