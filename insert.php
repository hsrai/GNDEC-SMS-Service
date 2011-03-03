<?php



/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *   
 *************************************************************
 *
 *  list.php
 *  Lists address book entries. This is the main page.
 *
 *************************************************************/

// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	require_once(FILE_CLASS_CONTACTLIST);
	require_once(FILE_CLASSES);
	session_start();
	$by = $_SESSION['username'];

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin();

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();
	
// ** END INITIALIZATION *******************************************************

	// CREATE THE LIST.	
	$list = &new ContactList();
	
	// THIS PAGE TAKES SEVERAL GET VARIABLES
	// ie. list.php?group_id=6&page=2&letter=c&limit=20
	if ($_GET['groupid'])         $list->group_id = $_GET['groupid'];
	if ($_GET['page'])            $list->current_page = $_GET['page'];
	if (isset($_GET['letter']))   $list->current_letter = $_GET['letter'];	
	if (isset($_GET['limit']))    $list->max_entries = $_GET['limit'];	

	// Set group name (group_id defaults to 0 if not provided)
	$list->group_name();

	// ** RETRIEVE CONTACT LIST BY GROUP **
	$r_contact = $list->retrieve($_SESSION['username']);
	
                   
$sms_time = date("F j, Y, g:i a");
//echo $sms_time;
?>
<HTML>
<HEAD>
	<TITLE><?php echo "$lang[TITLE_TAB] - $lang[TITLE_LIST]"?></TITLE>
	
	
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">
</HEAD>

<BODY onLoad="document.goToEntry.goTo.focus();">
<A NAME="top"></A>
<P>
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
		<TR><TD><IMG SRC="images/title.png" WIDTH=570 HEIGHT=90 ALT="" BORDER=0></TD></TR>	
  <TR>
    <TD>
        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=570>
           <TR VALIGN="top">
              <TD WIDTH=285 CLASS="data">
<?php
	// PRINT WELCOME MESSAGE
	if ($options->msgWelcome != "") {
		echo("<B>$options->msgWelcome</B>\n");
	}
	// PRINT SITE LANGUAGE [disabled for release]
	// if($options->global_options[language] != $options->user_options[language]) echo "<br>".$lang[WELCOME_SITE_LANG].": ".$options->global_options[language];
	// if($options->global_options[language] != $options->user_options[language] AND isset($options->user_options[language]))	echo "<br>".$lang[WELCOME_UR_LANG].": ".$options->user_options[language];	
	// PRINT LOGGED IN USER
	if (($_SESSION['username'] == "@auth_off") || ($_SESSION['usertype'] == "guest")) {
?>
			<BR><?php  echo $lang[MSG_LOGIN_NOT]. '<a href=" '.FILE_INDEX.'?mode=login"> '.$lang[WELCOME_LOGIN].'</a>' ?>
<?php
	} else {
?>
                <BR><?php echo $lang[WELCOME_CURRENT_LOGIN].' <b>'.$_SESSION['username'].'</b>'?>
<?php	
	if ($_SESSION['usertype'] == "admin") {
		echo('<BR>'.$lang[WELCOME_ADMIN_ACCESS]);
	}
	if ($_SESSION['usertype'] == "user") {
		echo('<BR>'.$lang[WELCOME_USER_ACCESS]);
	}
	echo '<br><a href=" '.FILE_INDEX.'?mode=logout"> '.$lang[WELCOME_LOGOUT].'</a>';
	}

?>

<?php
//    $sql2 = "SELECT firstname FROM contact";
  //  $result = mysql_query($sql2);
    //while ($num = mysql_fetch_array($result))
   // {
    //    echo $num['firstname'];
     //   echo "<br />";
   // }
?>

                <BR><BR><BR>
              </TD>

<html>
<head>
<title>Thanks</title>
<link type="text/css" rel="stylesheet" href="../style.css" />
</head>
<body bgcolor="#CBD5E9">
<p><h2>Thanks for using SMS Service.</h2></p>
<h3>SMS sent to the following Persons:</h3>

		

<?php
$con = mysql_connect($db_hostname,$db_username,$db_password);
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("adbook", $con);
$msgdata = mysql_real_escape_string($_POST[msgdata]);
$numbers=explode(",",$_POST[receiver]);
$count=0;
foreach ($numbers as $number)
{
if($number == '')
continue;

$sql="INSERT INTO send_sms (sender, receiver, msgdata)
VALUES
('".$_SESSION['username']."','$number','$msgdata')";


if (!mysql_query($sql,$con))
  {
  die('Error: ' . mysql_error());
  }
$count++;
$resulth = mysql_query($sql2 = "SELECT id FROM address WHERE phone1='".$number."'");
if(mysql_num_rows($resulth)!=0)
{
$shname = mysql_fetch_array($resulth);
$hresulth = mysql_query($sql3 = "SELECT firstname, middlename, lastname FROM contact WHERE id='".$shname[0]."'");
$shnames = mysql_fetch_array($hresulth);
$rep_name = $shname[middlename]. " " .$shnames[firstname]. " " .$shnames[lastname];
mysql_query("INSERT INTO report (receiver,mobile,msgdata,sender,time)
VALUES ('".$rep_name."','".$number."','".$msgdata."','".$by."','".$sms_time."')");

echo $shnames[firstname]. " " .$shnames[middlename]. " " .$shnames[lastname] . "<br />";
}
else
{
echo $number. "<br />";
mysql_query("INSERT INTO report (receiver,mobile,msgdata,sender,time)
VALUES ('NA','".$number."','".$msgdata."','".$by."','".$sms_time."')");
}
}
mysql_close($con);
echo "<br><h3>Total Number of Recipients : ".$count."</h3>"; 
?>
<br>
<table>
<tr>
<td><form action="list.php" method="post">
<input type="image" src="images/addressbook.png" />
</form></td>

<td><form action="sendsms.php" method="post">
<input type="image" src="images/send_more.png" />
</form></td>
</body>
</html>
             
