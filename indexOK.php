<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *    
 *  
 *************************************************************
 *
 *  index.php
 *  Welcome screen
 *  
 *************************************************************/

// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);

// ** START SESSION **
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();

	// ** FIGURE OUT WHAT'S GOING ON
	switch($_GET['mode']) {

		// **LOGOUT **
		case "logout":
			session_destroy();
			require_once('languages/' . $options->language . '.php');			
			// PRINT MESSAGE
			$errorMsg = $lang[MSG_LOGGED_OUT];
			header("Location: " . FILE_INDEX); //required to force site language to override user language at sign in screen
			break;

		// ** AUTHENTICATE A USER
		case "auth":
		
			// LOOK FOR USERNAME AND PASSWORD IN THE DATABASE.
			$usersql = "SELECT username, usertype, nature, batch, department, designation, password, is_confirmed FROM " . TABLE_USERS . " AS users WHERE username='" . $_POST['username'] . "' AND password=MD5('" . $_POST['password'] . "') LIMIT 1";
			$r_getUser = mysql_query($usersql, $db_link)
				or die(ReportSQLError($usersql));
			$numrows = mysql_num_rows($r_getUser);
		    $t_getUser = mysql_fetch_array($r_getUser); 
		    
			// THE USERNAME IS FOUND AND ACCOUNT IS CONFIRMED
			if (($numrows != 0) && ($t_getUser['is_confirmed'] == 1)) {
				
				// REGISTER SESSION VARIABLES
				$_SESSION['username'] = $t_getUser['username'];
				$_SESSION['usertype'] = $t_getUser['usertype'];
				$_SESSION['nature']   = $t_getUser['nature'];
				$_SESSION['batch']   = $t_getUser['batch'];
				$_SESSION['department']   = $t_getUser['department'];
				$_SESSION['designation']   = $t_getUser['designation'];
				if (!isset($_SESSION['abspath'])) {
					$_SESSION['abspath'] = dirname($_SERVER['SCRIPT_FILENAME']);
				}

				// REDIRECT TO LIST
				header("Location: " . FILE_LIST);
				exit();
				
			}

			// ACCOUNT MUST BE CONFIRMED
			elseif (($numrows != 0) && ($t_getUser['is_confirmed'] != 1)) {
				// END SESSION
				session_destroy();
				// PRINT ERROR MESSAGE AND LOGIN SCREEN
				$errorMsg = $lang[ERR_USER_CONFIRMED_NOT];
			}

			// WRONG USERNAME
			else {
				// END SESSION
				session_destroy();
				// PRINT ERROR MESSAGE AND LOGIN SCREEN
				$errorMsg = $lang[MSG_LOGIN_INCORRECT];
			}
			break;
		
		// ** REGISTER A NEW USER
		case "register":
			header("Location: " . FILE_REGISTER);
			exit();
			break;
		
		// ** LOST PASSWORD
		case "lostpwd":
			header("Location: " . FILE_REGISTER . "?mode=lostpwd");
			exit();
			break;
		
		// ** FORCE LOGIN
		case "login":
			// This must be set to bypass the redirection to list if requireLogin is off.
			$forceLoginScreen = 1;
			break;

		// ** DEFAULT CASE
		default:
			if ($forceLoginScreen != 1) {
				// ** IF THERE IS A USER LOGGED IN, THEY DON'T NEED TO BE HERE. REDIRECT TO LIST
				if (isset($_SESSION['username']) && isset($_SESSION['usertype']) && ($_SESSION['abspath'] == dirname($_SERVER['SCRIPT_FILENAME'])) ) {
					header("Location: " . FILE_LIST);
					exit();
				}
				// ** IF AUTHENTICATION IS TURNED OFF (via config.php)
				// Set the user type to "guest" and proceed to list.
				// If a user is already logged in, the above code will redirect to list before
				// getting to here.
				if (($options->requireLogin != 1) && ($enableLogin!=1)) {
					// REGISTER SESSION VARIABLES
					$_SESSION['username'] = "@auth_off";
					$_SESSION['usertype'] = "guest";
					$_SESSION['abspath'] = dirname($_SERVER['SCRIPT_FILENAME']);
					// REDIRECT TO LIST
					header("Location: " . FILE_LIST);
					exit();
				}
			}

	// END SWITCH
	}
	

?>
<HTML>
<HEAD>
	<TITLE> <?php  echo "$lang[TITLE_WELCOME] - $lang[TITLE_TAB]" ?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	
</HEAD>
<BODY onload="document.login.username.focus();">
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH="100%" HEIGHT="100%">
<TBODY>
<TR><TD ALIGN="center">
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
<TBODY>
	<TR><TD><IMG SRC="images/title.png" WIDTH=570 HEIGHT=90 ALT="" BORDER=0></TD></TR>
	<TR>
		<TD CLASS="data"><CENTER>
		<FORM NAME="login" METHOD="post" ACTION="index.php?mode=auth">
<?php
	// PRINT LOGIN MESSAGE
	if ($options->msgLogin != "") {
		echo("<P>$options->msgLogin\n");
	}
	// PRINT ERROR MESSAGES
	if ($errorMsg != "") {
		echo("<P><FONT COLOR=\"#FF0000\"><B>$errorMsg</B></FONT>\n");
	}
?>
		<P><B><?php echo $lang[LBL_USERNAME]?></B>
		<BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="username">
		<P><B><?php echo $lang[LBL_PASSWORD]?></B>
		<BR><INPUT TYPE="password" SIZE=20 CLASS="formTextbox" NAME="password">
		<P><INPUT TYPE="submit" CLASS="formButton" NAME="loginSubmit" VALUE="<?php echo $lang[BTN_LOGIN]?>">
<?php
	if ($options->allowUserReg == 1) {
		echo("<P><A HREF=\"" .FILE_INDEX. "?mode=register\">$lang[MSG_REGISTER_LOST]</A>\n");
	}
	if ($options->requireLogin != 1) {
		echo("	<P><A HREF=\"" . FILE_LIST ."\">$lang[GUEST]</A>\n");


	}


echo "<br><br><br><br><br>";
	echo "<b>Created By : <a href='http://harbhag.wordpress.com/harbhag' target='_blank'>Harbhag Singh Sohal</a></h3>";

?>
</FORM><p>

</TBODY>
</TABLE>
</TD></TR>
</TBODY>
</TABLE>
</BODY>
</HTML>
