<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *  
 * 
  *************************************************************
 *  functions.php
 *  Defines functions to be used within other scripts.
 *
 *************************************************************/
session_start();

function chronometer($msg) {
global $elapsed;
global $CHRONO_STARTTIME;
	$now = microtime(TRUE);
	if ($CHRONO_STARTTIME > 0){
		$elapsed = "$msg: ".round($now * 1000 - $CHRONO_STARTTIME * 1000, 3)." milli seconds";
		$CHRONO_STARTTIME = 0;
	return $elapsed;
	}else {
		$CHRONO_STARTTIME = $now;
 	}
 } 
 
 
# Following are registration/mail functions formerly found in /lib/userfunctions
## ########////////////*********            programming note - all values for feedback eventually need to be names of $lang[] array NAMES
// USED @ confirm page, accessed via confirmation e-mail

function user_confirm($hash,$email) { 
	global $feedback, $hidden_hash_var, $db_link;
	//verify that they didn't tamper with the email address - David temporarily put != where = was due to error troubleshooting.
	$new_hash=md5($email.$hidden_hash_var);
	if ($new_hash && ($new_hash==$hash)) {
		//find this record in the db
		$sql="SELECT * FROM ".TABLE_USERS." WHERE confirm_hash LIKE '$hash'";
		$result=mysql_query($sql, $db_link);
		if (mysql_numrows($result) < 1) {
			$feedback = "ERR_USER_HASH_NOT_FOUND";
			return false;
		} else {
			//confirm the email and set account to active
			$feedback ="REG_CONFIRMED";
			$sql="UPDATE ".TABLE_USERS."  SET email='$email',is_confirmed='1' WHERE confirm_hash='$hash'";
			$result=mysql_query($sql, $db_link);
			return true;
		}
	} else {
		$feedback = "ERR_USER_HASH_INVALID";
		return false;
	}
}

function account_pwvalid($pw) {
	global $feedback;
	if (strlen($pw) < 4) {
		$feedback .= "ERR_PSWD_SORT";
		return false;
	}
	return true;
}

function account_namevalid($name) {
	global $feedback;
	// no spaces
	if (strrpos($name,' ') > 0) {
		$feedback .= "ERR_LOGIN_SPACE";
		return false;
	}
	// must have at least one character
	if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") == 0) {
		$feedback .= "ERR_ALPHA";
		return false;
	}
	// must contain all legal characters
	if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_")
		!= strlen($name)) {
		$feedback .= "ERR_CHAR_ILLEGAL";
		return false;
	}
	// min and max length
	if (strlen($name) < 1) {
		$feedback .= "ERR_NAME_SHORT";
		return false;
	}
	if (strlen($name) > 15) {
		$feedback .= "ERR_NAME_LONG";
		return false;
	}
	// illegal names
	if (eregi("^((root)|(bin)|(daemon)|(adm)|(lp)|(sync)|(shutdown)|(halt)|(mail)|(news)"
		. "|(uucp)|(operator)|(games)|(mysql)|(httpd)|(nobody)|(dummy)"
		. "|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(ns)|(download))$",$name)) {
		$feedback .= "ERR_RSRVD";
		return 0;
	}
	if (eregi("^(anoncvs_)",$name)) {
		$feedback .= "ERR_RSRVD_CVS";
		return false;
	}

	return true;
}

function validate_email ($address) {
	return (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'. '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $address));
}
## end registration/mail functions
//
// CHECK FOR LOGIN - checkForLogin(usertype, ...);
// This function takes a variable number of arguments which defines what user types are allowed.
//
// There are a lot of issues with the security on this check, please check forums for more details....
// Security code currently does not work and has been commented out.
function checkForLogin() {
	session_start();
	global $db_link;

	// IF AUTHENTICATION IS TURNED OFF (requires database connection)
	$options = mysql_fetch_array(mysql_query("SELECT requireLogin FROM " . TABLE_OPTIONS . " LIMIT 1", $db_link))
		or die(reportScriptError("Unable to retrieve options in authorization check."));
	$requireLogin = $options['requireLogin'];
	if ($requireLogin != 1) {
		// If there is no current user logged in, set the user to @auth_off.
		// If there is a user logged in, it will proceed normally.

		if (!isset($_SESSION['username'])) {
			$_SESSION['username'] = "@auth_off";
			$_SESSION['usertype'] = "guest";
		}
	}

	// Redirect user to the login page if correct session variables are not defined.
	if ( !isset($_SESSION['username']) || !isset($_SESSION['usertype']) || (isset($_SESSION['abspath']) && $_SESSION['abspath'] != dirname($_SERVER['SCRIPT_FILENAME'])) ) {
		session_destroy();
		header("Location: " . FILE_INDEX);
		exit();
	}

	// Refuse access to restricted users
	// allowed users must be specified by name in the function argument list.
	$numargs = func_num_args();
	if ($numargs >= 1) {
	    $arg_list = func_get_args();
		for ($i = 0; $i < $numargs; $i++) {
			if ($_SESSION['usertype'] == $arg_list[$i]) {
				$userAllowed = 1;
			}
		}
		if ($userAllowed != 1) {
?>
<HTML>
<HEAD>
  <TITLE>Address Book - Access Denied</TITLE>
  <LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
  <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="EXPIRES" CONTENT="-1">
</HEAD>
<BODY>
<P><B>You do not have permission to conduct this operation. <A HREF="<?php echo(FILE_LIST); ?>">Click here to return.</A>
</BODY>
</HTML>
<?php
				exit();
	    }
	}
}
// end



//
// CHECK ID - check_id();
// Checks to see if an variable 'id' has been passed to the document, via GET or POST.
// In addition, it checks to see if the 'id' corresponds to an entry already in the database, or else returns an error.
function check_id() {
	global $db_link;
	global $lang;

	// Get 'id' if passed through GET
	$id = (integer) $_GET['id'];
	// If 'id' is provided through POST, it takes precedence over the GET value.
	if ($_POST['id']) {
		$id = (integer) $_POST['id'];
	}
	
	// Check if anything was given for ID
	if (empty($id)) {
		reportScriptError("<b>invalid entry ID</b>");
		exit();
	}
	
	// Check to see if contact exists
	$exists = mysql_num_rows(mysql_query("SELECT id FROM " . TABLE_CONTACT . " WHERE id=$id LIMIT 1", $db_link));
	if ($exists != 1) {
		reportScriptError("<b>no entry by that id</b>");
		exit();
	}	
	
	// Return id
	return $id;

}
// end


// 
// IS ALPHANUMERIC - isAlphaNumeric();
// Checks a string to see if it contains letters a-z, A-z, numbers 0-9, or the
// underscore _ character. If it does not, it returns false.
//
function isAlphaNumeric($string) {
	if (preg_match("/[^a-z,A-Z,0-9_]/", $string) == 0) {
		return true;
	}
	else {
		return false;
	}
}


//
// OPEN DATABASE - openDatabase();
// Connects to the MySQL server and retrieves the database.
//
function openDatabase($db_hostname, $db_username, $db_password, $db_name) {
session_start();
	// Default to local host if a hostname is not provided
	if (!$db_hostname) {
		$db_hostname = "localhost";
	}

	// Opens connection to MySQL server
	$db_link = @mysql_connect($db_hostname, $db_username, $db_password)
		or die(reportScriptError("<B>An error occurred while trying to connect to the MySQL server.</B> MySQL returned the following error information: " .mysql_error(). " (error #" .mysql_errno(). ")"));

	// Retrieves the database.
	$db_get = mysql_select_db($db_name, $db_link)
		or die(reportScriptError("<B>Unable to locate the database.</B> Please double check <I>config.php</I> to make sure the <I>\$db_name</I> variable is set correctly."));
	
	// Return the connection
	return $db_link;
}
// end



//
// PRINT FOOTER - printFooter();
// Prints a table row containing version, copyright, and links.
//
function printFooter() {
	global $lang;

	echo("  <TR>\n");
	echo("    <TD CLASS=\"data\"><CENTER>\n");
	echo("    <BR><BR><B>" . $lang['TITLE_TAB'] . "</B> " . $lang['FOOTER_VERSION'] ." ". VERSION_NO. " | <A HREF=\"" . URL_HOMEPAGE . "\" TARGET=\"_blank\">" . $lang['FOOTER_HOMEPAGE_LINK'] . "</A> | <A HREF=\"" . URL_SOURCEFORGE . "\" TARGET=\"_blank\">". $lang['FOOTER_SOURCEFORGE_LINK'] ."</A> \n");
	echo("    <BR>" . $lang['FOOTER_COPYRIGHT'] . "\n");
	echo("    <BR>\n");
	echo("    </CENTER></TD>\n");
	echo("  </TR>\n");
}
// end



//
// SCRIPT ERROR MESSAGE - reportScriptError();
// If an error is encountered, report it to the user and halt further execution of script.
//
function reportScriptError($msg) {
?>
<html>
<head>
	<title>Address Book - Error</title>
	<link rel="stylesheet" href="styles.css" type="text/css">
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
	<meta http-equiv="PRAGMA" content="NO-CACHE">
	<meta http-equiv="EXPIRES" content="-1">
</head>

<body>

<p>
<b><font style="color:#FF0000;"><?php echo $lang['ERROR_ENCOUNTERED']?></font></b> 

<p>The following error occurred:

<div class="error"><?php echo($msg); ?></div>

<p>
If necessary, please press the BACK button on your browser to return to the previous screen and correct any possible mistakes.
<br>If you still need help, or you believe this to be a bug, please consult the <a href="http://www.wordpress.com/" target="_blank">Author</a>.


<p>
<table border=0 cellpadding=0 cellspacing=0 width=570>
<tbody>
<?php
	printFooter();
?>
</tbody>
</table>

</body>
</html>
<?php
	// and then exit the script
	exit();
}
// end




//
// SQL ERROR MESSAGE - reportSQLError();
// If an error is encountered, report it to the user and halt further execution of script.
//
function reportSQLError() {

?>
<html>
<head>
	<title>Address Book - Error</title>
	<link rel="stylesheet" href="styles.css" type="text/css">
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
	<meta http-equiv="PRAGMA" content="NO-CACHE">
	<meta http-equiv="EXPIRES" content="-1">
</head>
<body>

<p>
<b><font style="color:#FF0000;">The Address Book has encountered a problem.</font></b> 

<p>MySQL returned the following error message:

<div class="error"><?php echo("MySQL error number " . mysql_errno() . ": " . mysql_error()); ?></div>

<p>
If necessary, please press the BACK button on your browser to return to the previous screen and correct any possible mistakes.
<br>If you still need help, or you believe this to be a bug, please consult the <a href="http://www.harbhag.wordpress.com/" target="_blank">Author</a>.

<P>

<table border=0 cellpadding=0 cellspacing=0 width=570>
<tbody>
<?php
	printFooter();
?>
</tbody>
</table>

</body>
</html>
<?php
	// and then exit the script
	exit();
}
// end


// END OF FILE
?>
