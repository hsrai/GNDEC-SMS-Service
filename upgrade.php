<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04
 *  
 *  upgrade.php
 *  Upgrades 1.03 installation to 1.04
 *	
 *************************************************************/

//error_reporting  (E_ERROR | E_WARNING | E_PARSE); 

// ** GET CONFIGURATION DATA **
    require_once('constants.inc');
    require_once(FILE_FUNCTIONS);
    require_once('languages/english.php');
?>
<HTML>
<HEAD>
  <TITLE>Address Book - Upgrade 1.03 to 1.04</TITLE>
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

	if ($_POST["installStep"] == "3") {
		// CHECK THE CONFIG VARIABLES
		// Make sure required variables exist
		$errorMsg = "<P><B>Upgrade aborted:</B> config.php is missing the following information:<P>";
		$errorStatus = 0;
	    if (!$db_name) {
			$errorMsg .= "- MySQL database name<BR>";
			$errorStatus = 1;
	    }
        if (!$db_username) {
			$errorMsg .= "- MySQL user name<BR>";
			$errorStatus = 1;
	    }
        if (!$db_password) {
			$errorMsg .= "- MySQL password<BR>";
			$errorStatus = 1;
		}
		if ($errorStatus == 1) {
			echo $errorMsg . "<P>Please check your config.php for missing or incomplete data and try again.";
			exit();
		}

		// OPEN CONNECTION TO THE DATABASE
		$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

		//*******************
		// doQuery();
		//*******************
		function doQuery($sql) {
			global $db_link;
			mysql_query($sql)
				or die(reportSQLError($sql));
		}

	//  BEGIN 1.03 to 1.04 UPGRADE  
	echo "Upgrading from 1.03 to 1.04, the debut of multi-language version !!! <br>
		The multi language capability is not present for install.php nor upgrade.php<br>
		This upgrade makes the following changes to the database:<p>
		1. Removes the counter and implements auto increment<br>
		2. Removes the country table and replaces it with an array imported from a language file.<br>
		3. Updates the address information from old numeric country code to new alpha country code. <br>
		4. Makes several changes and additions to the options table<br>
		5. Makes changes to the user table<br>
		6. Improves address information handling and flexibility.<br>
		<br>";
	// function numb2alph() TAKES A NUMERIC COUNTRY CODE, FINDS THE COUNTRY NAME, THEN FINDS THAT NAME IN ALPHA TABLE AND RETURNS THE ALPHA CODE.
	function numb2alph($countryNumber) {
		global $db_link;
		global $db_prefix;
		global $country; #this array is loaded by the language file and contains 2 letter country codes 
		reset($country);
		$countryAlpha = 0; //default to 0, unless a match is found.
		$oldCountry = mysql_fetch_assoc(mysql_query("SELECT * FROM " . $db_prefix . "country WHERE id=$countryNumber"));
			while (list ($aphaCountryCode, $countryName) = each ($country)) {
				if ($countryName==$oldCountry['countryname']) {
					$countryAlpha = $aphaCountryCode; 
				}
					switch($countryNumber){  //special cases where there are mispelled or old names or other silly special requirements
					case '27':
						$countryAlpha='ba';
						break;
					case '32':
						$countryAlpha='bn';
						break;
					case '70':
						$countryAlpha='fo';
						break;
					case '74':
						$countryAlpha='fr';
						break;
					case '77':
						$countryAlpha='fr';
						break;
					case '113':
						$countryAlpha='kr';
						break;
					case '230':
						$countryAlpha='vn';
						break;
					case '237':
						$countryAlpha='cg';
						break;
					}	// end switch
			}  // end while
		return $countryAlpha;
	} // END FUNCTION 
	
	// UPGRADE TABLE_OPTIONS
	echo "\n<br>Starting upgrades to table ".$db_prefix."options... ";
	doQuery("ALTER TABLE " . $db_prefix . "options CHANGE countryDefault countryDefault CHAR(3) DEFAULT '0' NULL"); 
	doQuery("ALTER TABLE " . $db_prefix . "options DROP pathMugshots"); 
	doQuery("ALTER TABLE " . $db_prefix . "options ADD language varchar(25) NOT NULL AFTER requireLogin"); 
	doQuery("ALTER TABLE " . $db_prefix . "options ADD defaultLetter char(2) NULL AFTER language");
	doQuery("ALTER TABLE " . $db_prefix . "options ADD limitEntries smallint(3) DEFAULT '0' NOT NULL AFTER defaultLetter");
	doQuery("ALTER TABLE " . $db_prefix . "options ADD eMailAdmin int(1) NOT NULL default '0' AFTER allowUserReg");

	// Country code upgrade
	$oldCountryCode = mysql_fetch_assoc(mysql_query("SELECT countryDefault FROM " . $db_prefix . "options"));
	$countryAlpha = numb2alph($oldCountryCode['countryDefault']); #give this function the country number and it returns the 2 letter alpha code as $countryAlpha
	doQuery("UPDATE " . $db_prefix . "options SET countryDefault='$countryAlpha', language='english'");
	echo "OK.\n";

	// UPGRADE TABLE_USERS
	echo "<br>Starting upgrades to table ".$db_prefix."users... ";
	// add the new user option fields
	doQuery("ALTER TABLE " . $db_prefix . "users ADD bdayInterval int(3) default NULL"); 
	doQuery("ALTER TABLE " . $db_prefix . "users ADD bdayDisplay int(1) default NULL"); 
	doQuery("ALTER TABLE " . $db_prefix . "users ADD displayAsPopup int(1) default NULL"); 
	doQuery("ALTER TABLE " . $db_prefix . "users ADD useMailScript int(1) default NULL"); 
	doQuery("ALTER TABLE " . $db_prefix . "users ADD language varchar(25) default NULL"); 
	doQuery("ALTER TABLE " . $db_prefix . "users ADD defaultLetter char(2) default NULL");
	doQuery("ALTER TABLE " . $db_prefix . "users ADD limitEntries smallint(3) default NULL");
	echo "OK.\n";

	// UPGRADE TABLE_ADDRESS	
	echo "<br>Starting upgrades to table ".$db_prefix."address... ";
	doQuery("ALTER TABLE " . $db_prefix . "address CHANGE state state VARCHAR(50) DEFAULT NULL, ADD refid INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
	
	// Change the value of contact.primaryAddress from type to refid
	echo "<br>Converting types... ";
	$oldAddType = mysql_query("SELECT contact.id, primaryAddType, refid FROM " . $db_prefix . "contact AS contact LEFT JOIN " . $db_prefix . "address AS address ON contact.id=address.id AND contact.primaryAddType=address.type");
	while ($tblAddType = mysql_fetch_array($oldAddType)) {
		doQuery("UPDATE  " . $db_prefix . "contact SET primaryAddType='".$tblAddType['refid']."' WHERE id=".$tblAddType['id']."");
		echo "<!-- ID ".$tblAddType['id']." | Old type: ".$tblAddType['primaryAddType']." | New refid: ".$tblAddType['refid']." -->\n"; 
	}
	// Make the change to type INT *after* new refid values are given.
	doQuery("ALTER TABLE " . $db_prefix . "contact CHANGE primaryAddType primaryAddress INT DEFAULT NULL");

	// THE ADDRESS TABLE WAS IN NUMERIC CODE, NOW ALPHA CODE, SO CONVERT IT NOW
	echo "<br>Converting old country codes... ";
	doQuery("ALTER TABLE " . $db_prefix . "address CHANGE country country VARCHAR(3) "); // change to type VARCHAR first
	$numericAddressHandle = mysql_query("SELECT * FROM " . $db_prefix . "address");
	while ($numericAddressArray = mysql_fetch_array($numericAddressHandle)) {
		$countryAlpha = numb2alph($numericAddressArray['country']);
		$query = "UPDATE " . $db_prefix . "address SET country='$countryAlpha' WHERE id=" .$numericAddressArray['id']. " AND refid='".$numericAddressArray['refid']."'";
		doQuery($query);
		echo "<br> ID ".$numericAddressArray['id']." | Old code: ".$numericAddressArray['country']." | New code: ".$countryAlpha." \n";
	}

	echo "<br>OK.\n";

	// REMOVE COUNTRY TABLE
	echo "<br>Removing table ".$db_prefix."country from database... ";
	doQuery("DROP TABLE IF EXISTS " . $db_prefix . "country");
	echo "OK.\n";

	// CONTACT TABLE CHANGES HERE
	// alter the contact.id field to be auto_increment
	echo "<br>Implementing auto increment... ";
	doQuery("ALTER TABLE " . $db_prefix . "contact CHANGE id id INT(11) NOT NULL AUTO_INCREMENT");
	doQuery("DROP TABLE IF EXISTS " . $db_prefix . "counter"); //with auto_increment, we no longer need the counter table.
	echo "OK.\n";
	doQuery("ALTER TABLE " . $db_prefix . "groups CHANGE groupid groupid INT(11) NOT NULL DEFAULT '0'"); //changed from tinyint(4) to allow more than 127 groups
	echo "<p>Finished! Upgrade appears to be successful.\n";	
	
// ** END 1.03 to 1.04 UPGRADE

?>
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="headTitle">
       Address Book Upgrade Complete!
    </TD>
  </TR>
  <TR>
    <TD CLASS="infoBox">

        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
           <TR VALIGN="top">
              <TD CLASS="data">
                 <P>
                 Thats it! Your installation has successfully upgraded from 1.03 to 1.04.

                 <P>
                 <A HREF="index.php">Click here</A> to enter the Address Book.
                 <P>
                 <B><FONT STYLE="color:#FF0000">IMPORTANT!</FONT></B> You may want to delete the files <I>upgrade.php</I> and <I>install.php</I> from your server so that other people can not abuse it. Leaving these files on your server will pose a <B>great security risk</B> and could potentially damage your Address Book installation.
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
<FORM NAME="Options" ACTION="upgrade.php" METHOD="post">
<INPUT TYPE="hidden" NAME="installStep" VALUE="3">
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="headTitle">
       Address Book Upgrade (version <?php echo(VERSION_NO);?>)
    </TD>
  </TR>
  <TR>
    <TD CLASS="infoBox">
        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
           <TR VALIGN="top">
              <TD CLASS="data">
				 <P><FONT STYLE="color: #FF0000;"><B>WARNING</B></FONT> <B>This is for upgrading your installation of the Address Book ONLY.</B>
					<P>This will only upgrade previous installations of The Address Book version 1.03 to version 1.04. If you are beginning a new installation (or want to overwrite a previous installation), please use <A HREF="install.php">install.php</A>.
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
        <!-- later make it so link versions dont appear -->
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
