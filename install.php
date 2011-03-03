<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *  
 * 
 *	Changed groups.groupid from tinyint(4) to int(11)
 *  
 *  
 *  
 *  
 *  
 *  install.php
 *  Installs address book.
 *
 *************************************************************/

error_reporting  (E_ERROR | E_WARNING | E_PARSE); 
session_start();
// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	$langForDB = 'english';
//*******************
//doQuery();  used to update Datatabse later down the program-
//*******************
	function doQuery($sql, $db_link) {
		mysql_query($sql, $db_link)
				or die(ReportSQLError($sql));
	}
?>

<HTML>
<HEAD>
<TITLE>The Address Book - Installation</TITLE>
<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
</HEAD>
<BODY>
<SCRIPT LANGUAGE="JavaScript">
<!--
function saveEntry() {
    document.Options.submit();
}
// -->
</SCRIPT>
<?php

	if ($_POST["installStep"] == "2") {
// CHECK THE CONFIG VARIABLES
// Make sure required variables exist
		$errorMsg = "<P><b>Installation aborted !!</b><br> config.php has incorrect or missing information !<P>";
		$errorStatus = 0;
	    	if (empty($db_name)) {
			$errorMsg .= "- MySQL database name is empty<br>";
			$errorStatus = 1;
		}
        		if (empty($db_username)) {
			$errorMsg .= "- MySQL user name is empty<br>";
			$errorStatus = 1;
	    	}
// OPEN CONNECTION TO THE DATABASE
		$db_link = @mysql_connect($db_hostname, $db_username, $db_password, $db_name);
		$opps = mysql_errno();
		if($opps ==1045){
			$errorMsg .= "<br>Your config.php file has either incorrect username or password.<br>";
			$errorStatus = 1;
		}	
		if (!$db_get = @mysql_select_db($db_name, $db_link)){
			$errorMsg .= "The following error occurred:";
			$errorStatus = 1;
		}
		/*
		$tableCheck= @mysql_query("SELECT id FROM ".$db_prefix . "contact", $db_link);
		if($tableCheck){
			$errorMsg .= "The prefix you have chosen in config has exisiting tables.";
			$errorStatus = 1;
		}*/

		if ($errorStatus == 1) {			
			echo "<center><TABLE  border=\"2\"><TR><TD CLASS=\"headTitle\">";
			echo "<center>The Address Book - Installation Error</center></TR></TD><TR><TD CLASS=\"data\">";
			echo "<center><font color=\"red\">$errorMsg  Please fix your config.php file, then try again.</center></font>";
			echo "</TABLE></TD></TR></center>";
			$erroMsg=" ";
		exit();
		}		

	// the if  POST above has an else further down, so no closing } needed here

		// DROP TABLES IF A PREVIOUS INSTALLATION EXISTS
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "additionaldata", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "address", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "contact", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "counter", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "country", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "email", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "grouplist", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "groups", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "lang", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "messaging", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "options", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "otherphone", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "websites", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "users", $db_link);
		doQuery("DROP TABLE IF EXISTS " . $db_prefix . "scratchpad", $db_link);

		// GENERATE TABLES
		
		doQuery("CREATE TABLE " . $db_prefix . "additionaldata (id INT(11) NOT NULL DEFAULT '0', type VARCHAR(20) DEFAULT NULL, value TEXT) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "address (refid INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id INT(11) NOT NULL DEFAULT '0', type VARCHAR(20) NOT NULL DEFAULT '', line1 VARCHAR(100) DEFAULT NULL, line2 VARCHAR(100) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, state VARCHAR(50) DEFAULT NULL, zip VARCHAR(20) DEFAULT NULL, country VARCHAR(3) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, phone2 VARCHAR(20) DEFAULT NULL ) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "contact (id INT(11) NOT NULL AUTO_INCREMENT, firstname VARCHAR(40) NOT NULL DEFAULT '', lastname VARCHAR(80) NOT NULL DEFAULT '', middlename VARCHAR(40) DEFAULT NULL, primaryAddress INT(11) DEFAULT NULL, birthday DATE DEFAULT NULL, nickname VARCHAR(40) DEFAULT NULL, pictureURL VARCHAR(255) DEFAULT NULL, notes TEXT, lastUpdate DATETIME DEFAULT NULL, hidden INT(1) DEFAULT '0' NOT NULL, whoAdded VARCHAR(15), PRIMARY KEY (id)) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "email (id INT(11) NOT NULL DEFAULT '0', email VARCHAR(100) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "grouplist (groupid INT(11) NOT NULL DEFAULT '0', groupname VARCHAR(60) DEFAULT NULL, PRIMARY KEY (groupid)) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "groups (id INT(11) NOT NULL DEFAULT '0', groupid INT(11) NOT NULL DEFAULT '0') TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "messaging (id INT(11) NOT NULL DEFAULT '0', handle VARCHAR(30) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "options (bdayInterval INT(3) DEFAULT '21' NOT NULL, bdayDisplay INT(1) DEFAULT '1' NOT NULL, displayAsPopup INT(1) DEFAULT '0' NOT NULL, useMailScript INT(1) DEFAULT '1' NOT NULL, picAlwaysDisplay INT(1) DEFAULT '0' NOT NULL, picWidth INT(1) DEFAULT '140' NOT NULL, picHeight INT(1) DEFAULT '140' NOT NULL, picDupeMode INT(1) DEFAULT '1' NOT NULL, picAllowUpload INT(1) DEFAULT '1' NOT NULL, modifyTime VARCHAR(3) DEFAULT '0' NOT NULL, msgLogin TEXT NULL, msgWelcome VARCHAR(255) NULL, countryDefault CHAR(3) DEFAULT '0' NULL, allowUserReg INT(1) DEFAULT '0' NOT NULL, eMailAdmin int(1) NOT NULL default '0', requireLogin INT(1) DEFAULT '1' NOT NULL, language VARCHAR(25) NOT NULL, defaultLetter char(2) default NULL, limitEntries smallint(3) NOT NULL default '0') TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "otherphone (id INT(11) NOT NULL DEFAULT '0', phone VARCHAR(20) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "websites (id INT(11) NOT NULL DEFAULT '0', webpageURL VARCHAR(255) DEFAULT NULL, webpageName VARCHAR(255) DEFAULT NULL) TYPE=MyISAM", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "users (id INT(2) NOT NULL AUTO_INCREMENT, username VARCHAR(15) NOT NULL, usertype ENUM('admin','user','guest') NOT NULL DEFAULT 'user', password VARCHAR(32) NOT NULL DEFAULT '', email VARCHAR(50) NOT NULL, confirm_hash VARCHAR(50) NOT NULL, is_confirmed TINYINT(1) DEFAULT '0' NOT NULL, bdayInterval int(3) default NULL, bdayDisplay int(1) default NULL, displayAsPopup int(1) default NULL, useMailScript int(1) default NULL, language varchar(25) default NULL, defaultLetter char(2) default NULL, limitEntries smallint(3) default NULL, PRIMARY KEY (id), UNIQUE KEY username (username)) TYPE=MyISAM;", $db_link);
		doQuery("CREATE TABLE " . $db_prefix . "scratchpad (notes TEXT NOT NULL) TYPE=MyISAM", $db_link);
		// POPULATE DEFAULT GROUPS
		
		doQuery("INSERT INTO " . $db_prefix . "grouplist VALUES(0,'(all entries)')", $db_link);
		doQuery("INSERT INTO " . $db_prefix . "grouplist VALUES(1,'(ungrouped entries)')", $db_link);
		doQuery("INSERT INTO " . $db_prefix . "grouplist VALUES(2,'(hidden entries)')", $db_link);
		// POPULATE SUNDRY DATABASE ENTRIES
		doQuery("INSERT INTO " . $db_prefix . "scratchpad VALUES('')", $db_link);
		// SET DEFAULT OPTIONS
		
		doQuery("INSERT INTO " . $db_prefix . "options VALUES(21,1,0,1,0,140,140,1,1,0,'<P>Please log in to access the Address Book.','<B>welcome to the Address Book!</B>','',0,0,1,'$langForDB','',0)", $db_link);
		// CREATE TEMPORARY USERS		
		//doQuery("INSERT INTO " . $db_prefix . "users VALUES (1, 'admin', 'admin', MD5( 'admin' ), '', '', 1,21,1,0,1,'$langForDB','',0)", $db_link);
		//doQuery("INSERT INTO " . $db_prefix . "users VALUES (2, 'guest', 'user', MD5( 'guest' ), '', '', 1,21,1,0,1,'$langForDB','',0)", $db_link);
		doQuery("INSERT INTO " . $db_prefix . "users (id, username, usertype, password, email, confirm_hash, is_confirmed) VALUES (1, 'admin', 'admin', MD5( 'admin' ), '', '', 1)", $db_link);
		doQuery("INSERT INTO " . $db_prefix . "users (id, username, usertype, password, email, confirm_hash, is_confirmed) VALUES (2, 'guest', 'user', MD5( 'guest' ), '', '', 1)", $db_link);
		$_SESSION['username'] = 'admin';
		$_SESSION['usertype'] = 'admin';
		$_SESSION['abspath'] = dirname($_SERVER['SCRIPT_FILENAME']);
		

?>
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="headTitle">
	Address Book Installation Complete!
  </TR>
  <TR>
    <TD CLASS="infoBox">
        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
           <TR VALIGN="top">
              <TD CLASS="data">
		<p> That's it!    
		<p>  <A HREF="list.php">Click here</A> to go straight to the main list of your Address Book and begin entries.
		<br> You will be automatically logged in as admin. If you wish to add entries as some other user, then either create
		<br> a new user or toggle on "Allow User Self Registration" in options and log off and self register as somebody else.
		<p>You should remove this file (install.php) from the server so others won't try to abuse it.
              </TD>
           </TR>
        </TABLE>
    </TD>
  </TR>
</TABLE>
</CENTER>
<?php
	// ADD INSTALLATION STEP TWO
	}
	else {
?>

<FORM NAME="Options" ACTION="<?php echo(FILE_INSTALL); ?>" METHOD="post">
<INPUT TYPE="hidden" NAME="installStep" VALUE="2">

<CENTER>
<TABLE BORDER=5 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="headTitle">
        The Address Book Installation (version <?php echo(VERSION_NO);?>)
    </TD>
  </TR>
  <TR>
    <TD CLASS="infoBox">

        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
           <TR VALIGN="top">
              <TD CLASS="data">
                 <P> Thank you for choosing The Address Book!</p>
                 <P>By now, you should have configured your database login information in <b>config.php</b>.  If you have not, do that now and then upload it to the same folder as your Address Book installation. If you attempt to proceed with an incomplete or incorrect configuration The Address Book will not be installed correctly. <p> If you are ready to go, click Next to log into the database and set up all the tables pertaining to the Address Book.
                 <P><FONT COLOR="red"><b>Warning: Clicking Next will overwrite a previous installation of the Address Book.</b></FONT> <br>If you want to <a href="upgrade.php">upgrade</a> your installation, do not click Next.
              </TD>
           </TR>
           <TR VALIGN="top">
              <TD WIDTH=560 CLASS="listDivide">&nbsp;</TD>
           </TR>
           <TR VALIGN="top">
              <TD WIDTH=560 CLASS="navmenu">
      <NOSCRIPT>
        <!-- Will display Form Submit buttons for browsers without Javascript -->
        <INPUT TYPE="submit" VALUE="Next">
        <!-- There is no delete button -->
        <!-- later make it so link versions don't appear -->
      </NOSCRIPT>
      <A HREF="#" onClick="saveEntry(); return false;">next</A>
              </TD>
           </TR>
        </TABLE>
    </TD>
  </TR>
</TABLE>
</CENTER>
</FORM>
<?php
	// END DEFAULT ACTION
	}
?>
</BODY>
</HTML>
