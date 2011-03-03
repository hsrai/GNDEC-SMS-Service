<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *  
 *****************************************************************  
 *  mailto.php
 *  Sends e-mail to one or more addresses
 *  Originally written by Joe Chen
 *
 *************************************************************/

// BUG: Mailing List displays entries without email addresses.


// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	require_once(FILE_CLASS_CONTACTLIST);

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
//    list($userGroup, $userHomeName, $userHomePage, $userCapabilities) = checkForLogin($address_session_name, CAP_MAIL);
	checkForLogin('admin','user');

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options($db_link);

/*
	// JOE_DEBUG: check for viewing restrictions
	if ($userGroup) {
		if (($list->group_id <= 2) || (!$list->group_id)) {
			$r_check_GroupList = mysql_query("SELECT * FROM " . TABLE_GROUPLIST . " AS GroupList WHERE GroupName LIKE '%$userGroup%'", $db_link);
			$check_GroupList = mysql_fetch_array($r_check_GroupList); 
			$list->group_id = $check_GroupList["GroupID"];
			$list->group_name = $check_GroupList["GroupName"];
		} else {
			$r_check_GroupList = mysql_query("SELECT * FROM " . TABLE_GROUPLIST . " AS GroupList WHERE GroupID=$list->group_id", $db_link);
			$check_GroupList = mysql_fetch_array($r_check_GroupList); 
			$check_GroupName = $check_GroupList["GroupName"];

			if (!eregi( $userGroup, $check_GroupName)) {
				echo("<P>Invalid GroupID: not allowed to view this group. ");
				exit();
			}
		}
	}
*/


// ** GET DESTINATION EMAIL ADDRESS **
// If there is an e-mail address either via POST or GET we will e-mail to that single address.
// If not, then we will default to a mailing list setup.
	if ($_POST['to']) {				// Look for a target e-mail in POST first, which has priority.
		$mail_to = $_POST['to'];
	}
	elseif ($_GET['to']) {			// If there is no target e-mail in POST, look in GET
		$mail_to = $_GET['to'];
	}
	else {							// If there is no target e-mail in either, then we go to default mailing list mode.
		// RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
		$options = mysql_fetch_array(mysql_query("SELECT displayAsPopup, useMailScript FROM " . TABLE_OPTIONS . " LIMIT 1", $db_link))
			or die(reportScriptError("Unable to retrieve options."));
		// CREATE THE LIST.	
		$list = &new ContactList($options->defaultLetter, $options->limitEntries);
	
		// THIS PAGE TAKES SEVERAL GET VARIABLES
		if ($_GET['groupid'])  $list->group_id = $_GET['groupid'];
		if ($_GET['page'])     $list->current_page = $_GET['page'];
		if ($_GET['letter'])   $list->current_letter = $_GET['letter'];	
		if ($_GET['limit'])    $list->max_entries = $_GET['limit'];	

		// Set group name (group_id defaults to 0 if not provided)
		$list->group_name();

		// ** RETRIEVE CONTACT LIST BY GROUP **
		$r_contact = $list->retrieve();
		
		
	}

// ** RETRIEVE USER CONTACT INFORMATION **
	$mail_from = '';
	$r_user = 'gndec.sms.service@gmail.com';
	$mail_from = $r_user;
	$SendMailButton = "Yes";
	if(!$mail_from){
		$mail_from = $lang['ERR_NO_EMAIL1']."<A HREF =\"".FILE_USERS."\"> ".$lang['ERR_NO_EMAIL2'];
		$SendMailButton = "No";
	}		

?>
<HTML>
<HEAD>
	<TITLE><?php echo $lang['TAB']." - ".$lang['TITLE_OPT']?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	

	
</HEAD>

<BODY>
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
		<TR>
		<TD CLASS="navMenu"><A HREF="javascript:history.go(-1)"><?php echo $lang['BTN_RETURN'] ?></A></TD>
	</TR>
	<TR>
	    <TD>

			<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=570>
				<TR VALIGN="bottom">
					<TD CLASS="headTitle">
<?php // HEADER
	echo (empty($mail_to) ? $lang['TOOLBOX_MAILINGLIST']  : $lang['TITLE_OPT']);
?>
					</TD>
<?php // MAILING LIST
	if (empty($mail_to)) {
?>					<TD CLASS="headText" ALIGN="right">
						<FORM NAME="selectGroup" METHOD="get" ACTION="<?php echo(FILE_MAILTO); ?>">
						select group <SELECT NAME="groupid" CLASS="formSelect" onChange="document.selectGroup.submit();">
<?php
	// -- GENERATE GROUP SELECTION LIST --
	// Only admins can view hidden entries.
	if ($_SESSION['usertype'] == "admin") {
		$groupsql = "SELECT groupid, groupname FROM " . TABLE_GROUPLIST . " AS grouplist WHERE groupid >= 0 ORDER BY groupname";
	}
	else {
		$groupsql = "SELECT groupid, groupname FROM " . TABLE_GROUPLIST . " AS grouplist WHERE groupid >= 0 AND groupid != 2 ORDER BY groupname";
	}
	$r_grouplist = mysql_query($groupsql, $db_link);
	while ($tbl_grouplist = mysql_fetch_array($r_grouplist)) {
		$selectGroupID = $tbl_grouplist['groupid'];
		$selectGroupName = $tbl_grouplist['groupname'];
		echo("                       <OPTION VALUE=$selectGroupID");
		if ($selectGroupID == $list->group_id) {
			echo(" SELECTED");
		}
		echo(">$selectGroupName</OPTION>\n");
	}

?>
						</SELECT>
						</FORM>
					</TD>
<?php
	// END MAILING LIST
	}
?>

				</TR>
			</TABLE>

		</TD>

	</TR>
	<TR>
		<TD CLASS="infoBox">
			<BR>
			<CENTER>
			<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=10 WIDTH=560>
			<FORM NAME="mail_form" METHOD="post" ACTION="<?php echo(FILE_MAILSEND); ?>">
<?php
//** MAILING LIST **
	if (empty($mail_to)) {

		// INITIATE CHECKBOX NUMBER
		// here's the test.
		// take out checkbox numbers and assign them to array. the checked ones will automatically be submitted?
		// $cb_id = 1;

		// DISPLAY GROUP NAME
	    echo("                 <TR VALIGN=\"top\">\n");
	    echo("                   <TD WIDTH=560 COLSPAN=4 CLASS=\"listHeader\">$list->group_name</TD>\n");
	    echo("                 </TR>\n");
		// DISPLAY IF NO ENTRIES UNDER GROUP
		if (mysql_num_rows($r_contact)<1) {
	        echo("                 <TR VALIGN=\"top\">\n");
	        echo("                   <TD WIDTH=560 COLSPAN=4 CLASS=\"listEntry\">No entries.</TD>\n");
	        echo("                 </TR>\n");
		}
		// DISPLAY ENTRIES
	    while ($tbl_contact = mysql_fetch_array($r_contact)) {

	        $contact_fullname = $tbl_contact['fullname'];
	        $contact_lastname = $tbl_contact['lastname'];
	        $contact_firstname = $tbl_contact['firstname'];
	        $contact_id = $tbl_contact['id'];

	        echo("<TR VALIGN=\"top\">\n");
	        // DISPLAY NAME -- links are shown either as regular link or popup window
	        if ($options['displayAsPopup'] == 1) {
	            $popupLink = " onClick=\"window.open('" . FILE_ADDRESS . "?id=$contact_id','addressWindow','width=600,height=450,scrollbars,resizable,location,menubar,status'); return false;\"";
	        }
			if (!$contact_firstname) { 
				echo("<TD WIDTH=150 CLASS=\"listEntry\"><B><A HREF=\"" . FILE_ADDRESS . "?id=$contact_id\"$popupLink>$contact_lastname</A></B></TD>\n");
			}
	        else {
				echo("<TD WIDTH=150 CLASS=\"listEntry\"><B><A HREF=\"" . FILE_ADDRESS . "?id=$contact_id\"$popupLink>$contact_fullname</A></B></TD>\n");
			}
	        // DISPLAY E-MAILS
	        echo("<TD WIDTH=410 CLASS=\"listEntry\">");
	        $r_email = mysql_query("SELECT id, email, type FROM " . TABLE_EMAIL . " AS email WHERE id=$contact_id", $db_link);
	        $tbl_email = mysql_fetch_array($r_email);
			$email_address = stripslashes( $tbl_email['email'] );
			$email_type = stripslashes( $tbl_email['type'] );
	        if ( (eregi("old", $email_type)) || (empty($email_address)) ) {
	           $checkme = "";
	        } else {
	           $checkme = " CHECKED";
	        }
	        echo("<INPUT TYPE=\"checkbox\" NAME=\"mail_to[]\" VALUE=\"$email_address\"$checkme>");
	        $cb_id++;

			if ($options['useMailScript'] == 1) {
				echo("<A HREF=\"" .FILE_MAILTO. "?to=$email_address\">$email_address</A>");
			}
			else {
				echo("<A HREF=\"mailto:$email_address\">$email_address</A>");
			}

			if ($email_type) {
	    		echo(" ($email_type)");
			}

	        while ($tbl_email = mysql_fetch_array($r_email)) {
				$email_address = stripslashes( $tbl_email['email'] );
				$email_type = stripslashes( $tbl_email['type'] );

	            echo("<BR><INPUT TYPE=\"checkbox\" NAME=\"mail_to[]\" VALUE=\"$email_address\">");
	            $cb_id++;

				if ($options['useMailScript'] == 1) {
					echo("<A HREF=\"" .FILE_MAILTO. "?to=$email_address\">$email_address</A>");
				}
				else {
					echo("<A HREF=\"mailto:$email_address\">$email_address</A>");
				}
	       		if ($email_type) {
	           		echo(" ($email_type)");
	       		}
	        }
	        echo("&nbsp;</TD>\n");
	        echo("                 </TR>\n");

	    // END WHILE
	    }
?>
				<SCRIPT LANGUAGE="JavaScript">
				<!--
					 function restart() { 
						 for (var i=0;i<=1000;++i) {
							 document.mail_form.elements[i].checked=false
						 } 
					 }
				// -->
				</SCRIPT>
				<TR>
					<TD  width="150" class="data"></TD>
					<TD WIDTH=410 CLASS="data">
						<A HREF="#" onClick="restart();return false;"><?php echo $lang['GROUP_NONE']?></A>
						<BR><BR><BR>
					</TD>
				</TR>
<?php
	// END MAILING LIST LIST GENERATION
	} else {
	// THIS IS FOR SINGLE E-MAILS
?>
				<TR>
					<TD  width="200" class="data"><H4>To Email:</H4></TD>
					<TD  width="300" class="data">
					<INPUT TYPE="text" CLASS="formMailbox" VALUE="<?php echo($mail_to);?>" NAME="mail_to" ><BR><BR>
					</TD>
				</TR>
<?php
	}
	// END, AND BEGIN COMMON STUFF
?>				
				<TR>
					<TD WIDTH=200 CLASS="data"><H4>CC:</H4></TD>
					<TD WIDTH=300 CLASS="data">
					<INPUT TYPE="text" CLASS="formMailbox" VALUE="" NAME="mail_cc" SIZE=80><BR><BR>
					</TD>
				</TR>
				<TR>
					<TD WIDTH=200 CLASS="data"><H4>BCC:</H4></TD>
					<TD WIDTH=300 CLASS="data">
					<INPUT TYPE="text" CLASS="formMailbox" VALUE="" NAME="mail_bcc" SIZE=80><BR><BR>
					</TD>
				</TR>

				<TR><TD WIDTH=200 CLASS="data"><H4>From:</H4></TD>
					<TD WIDTH=300 CLASS="data"><?php echo $_SESSION['username']; ?>
					<INPUT TYPE="hidden"  VALUE="<?php echo $_SESSION['username'] ; ?>" NAME="mail_from_name" ><BR><BR>
				</TD></TR>


				<TR><TD WIDTH=200 CLASS="data"><H4>From Email:</H4></TD>
					<TD  width="300" class="data"><?php echo$mail_from; ?></TD></TR>
				<TR><TD WIDTH=200 CLASS="data"><H4><?php  echo $lang['MAIL_SUBJ']?>:</H4></TD>
					<TD WIDTH=300 CLASS="data">
					<INPUT TYPE="text" CLASS="formTextbox" VALUE="" NAME="mail_subject" SIZE=80><BR><BR>
				</TD></TR>
				<TR><TD WIDTH=200 CLASS="data"><H4><?php echo $lang['MAIL_MSG']?>:</H4></TD>
					<TD WIDTH=300 CLASS="data">
					<TEXTAREA CLASS="formTextarea" ROWS="20" COLS="75" NAME="mail_body"></TEXTAREA><BR><BR>
				</TD></TR>
				<TR><TD WIDTH=200 CLASS="data"></TD>
					<TD WIDTH=300 CLASS="data">
<?php
//   If there is valid email in FROM, then send mail from to mailsend with other values and dispaly the send mail button. Value set above when contact info obtained
if($SendMailButton == "Yes"){	
echo " 					<INPUT TYPE=\"submit\" VALUE=\"".$lang['BTN_SEND']."\" NAME=\"sendEmail\" CLASS=\"formButton\"><BR>";
echo"					<INPUT TYPE=\"hidden\"  VALUE=\"$mail_from\" NAME=\"mail_from\" ><BR><BR>";

}  ?>
				</TD></TR>
				</FORM>
			</TABLE>
			</CENTER>
			<BR>
	    </TD>
	</TR>
<?php
	printFooter();
?>
</TABLE>
</CENTER>

</BODY>
</HTML>
