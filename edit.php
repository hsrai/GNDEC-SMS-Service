<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04e
 *  
 *
 *************************************************************
 *  edit.php
 *  Edit address book entries. 
 *
 *************************************************************/

error_reporting (E_ALL);

// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin("admin","user");

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();

// ** CHECK FOR ID **
	$mode = $_GET['mode'];
	if ($mode == 'new') {
		$id = '0'; // this is to create empty variables from the database
	}
	else {
		$mode = 'edit';
		$id = check_id();
	}
 
// ** END INITIALIZATION *******************************************************

	// RETRIEVE ENTRY INFORMATION GIVEN AN ID
	if (isset($id)) {
		
		$r_contact = mysql_query("SELECT * FROM " . TABLE_CONTACT . " AS contact WHERE contact.id=$id", $db_link)
			or die(reportSQLError());
		$r_additionalData = mysql_query("SELECT * FROM " . TABLE_ADDITIONALDATA . " AS additionaldata WHERE additionaldata.id=$id", $db_link);
		$r_address = mysql_query("SELECT * FROM " . TABLE_ADDRESS . " AS address WHERE address.id=$id", $db_link);
		$r_email = mysql_query("SELECT * FROM " . TABLE_EMAIL . " AS email WHERE email.id=$id", $db_link);
		$r_messaging = mysql_query("SELECT * FROM " . TABLE_MESSAGING . " AS messaging WHERE messaging.id=$id", $db_link);
		$r_otherPhone = mysql_query("SELECT * FROM " . TABLE_OTHERPHONE . " AS otherphone WHERE otherphone.id=$id", $db_link);
		$r_websites = mysql_query("SELECT * FROM " . TABLE_WEBSITES . " AS websites WHERE websites.id=$id", $db_link);
		$r_lastUpdate = mysql_query("SELECT DATE_FORMAT(lastUpdate, \"%W, %M %e %Y (%h:%i %p)\") AS lastUpdate FROM " . TABLE_CONTACT . " AS contact WHERE contact.id=$id", $db_link);
			
		// NOTE: Groups is determined with a special query that will be run at the bottom of the page.
	
		// Turns query results into an array from where variables can then be extracted from it.
		$tbl_contact = mysql_fetch_array($r_contact); 
		$tbl_lastUpdate = mysql_fetch_array($r_lastUpdate); 
	
	
		// Put data into variable holders -- taken from arrays that are created from query results.
		$contact_firstname = stripslashes( $tbl_contact['firstname'] );
		$contact_lastname = stripslashes( $tbl_contact['lastname'] );
		$contact_middlename = stripslashes( $tbl_contact['middlename'] );
		$contact_primaryAddress = stripslashes( $tbl_contact['primaryAddress'] );
		$contact_birthday = stripslashes( $tbl_contact['birthday'] );
		$contact_nickname = stripslashes( $tbl_contact['nickname'] );
		$contact_pictureURL = stripslashes( $tbl_contact['pictureURL'] );
		$contact_notes = stripslashes( $tbl_contact['notes'] );
		$contact_lastUpdate = stripslashes( $tbl_lastUpdate['lastUpdate'] );
		$contact_hidden = $tbl_contact['hidden'];
		$contact_whoAdded = stripslashes( $tbl_contact['whoAdded'] );
	
		// BIRTHDAY... if field is empty, make it equal to "0000-00-00" 
		if (!$contact_birthday) {
			$contact_birthday = "0000-00-00";
		}
		
		// Check to see if the person who got to this edit record is the person whoAdded it. 
		// Without this code, someone could click on a record they are allowed to edit, then change the id in the URL to any other.
		if ((($contact_whoAdded != $_SESSION['username']) AND ($_SESSION['usertype'] != 'admin') AND ($mode != 'new')) OR ($_SESSION['usertype'] == 'guest')){
			$_SESSION = array();
		 	session_destroy();
			reportScriptError("URL tampering detected. You have been logged out.");
		}

	}
	
	// BEGIN OUTPUT BUFFER
	ob_start("callback");

?>
<HTML>
<HEAD>
	<TITLE><?php echo $lang['TITLE_TAB']." " ;
	if ($mode == 'new') {
		echo($lang['EDIT_TITLE_ADD']);
	}
	else { 
		echo($lang['EDIT_TITLE_EDIT']." $contact_firstname $contact_lastname\n");
	}
?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	
	<SCRIPT LANGUAGE="JavaScript">
	<!--

	function deleteEntry() {
		if (confirm("<?php echo $lang['DELETE_CONFIRM'] ?>")) {
			window.location.href = '<?php echo(FILE_SAVE); ?>?id=<?php echo($id); ?>&mode=delete';
		}
	}

	function deleteAddress(x) {
		document.getElementsByName('address_type_'+x).item(0).value = '';
		document.getElementsByName('address_line1_'+x).item(0).value = '';
		document.getElementsByName('address_line2_'+x).item(0).value = '';
		document.getElementsByName('address_city_'+x).item(0).value = '';
		document.getElementsByName('address_state_'+x).item(0).value = '';
		document.getElementsByName('address_zip_'+x).item(0).value = '';
		document.getElementsByName('address_phone1_'+x).item(0).value = '';
		document.getElementsByName('address_phone2_'+x).item(0).value = '';
		document.getElementsByName('address_country_'+x).item(0).value = '';
	}

	function saveEntry() {
		document.EditEntry.submit();
	}

	// -->
	</SCRIPT>
</HEAD>

<BODY>
<FORM NAME="EditEntry" ACTION="<?php echo(FILE_SAVE)."?mode=$mode"; ?>" METHOD="post">
<INPUT TYPE="hidden" NAME="id" VALUE="<?php echo($id); ?>">

<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
	<TD CLASS="navMenu">
	  <A HREF="#" onClick="saveEntry(); return false;"><?php echo $lang['BTN_SAVE']?></A>
<?php
// PRINT CANCEL AND/OR DELETE BUTTONS
	if ($mode == 'new') {
		echo("      <A HREF=\"" . FILE_LIST . "\">".$lang['BTN_CANCEL']."</A>\n");
	}
	else { 
		echo("      <A HREF=\"#\" onClick=\"deleteEntry(); return false;\">".$lang['BTN_DELETE']."</A>\n");
		echo("      <A HREF=\"" . FILE_ADDRESS . "?id=$id\">".$lang['BTN_CANCEL']."</A>\n");
	}
?>
	</TD>
  </TR>
  <TR>
	<TD>

		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=570>
		   <TR VALIGN=bottom>
			  <TD CLASS="headTitle">
<?php
	if ($mode == 'new') {
		echo($lang['EDIT_TITLE_ADD']."\n");
	}
	else { 
		echo($lang['EDIT_TITLE_EDIT']." $contact_lastname, $contact_firstname $contact_middlename\n");
	}
?>
			  </TD>
			  <TD CLASS="headText" ALIGN=right>
				 &nbsp;
			  </TD>
		   </TR>
		</TABLE>


	</TD>
  </TR>
  <TR>
	<TD CLASS="infoBox">

	  
		<TABLE BORDER=0 CELLSPACING=10 CELLPADDING=0 WIDTH=560>
		   <TR VALIGN="top">
			  <TD COLSPAN=3 CLASS="data">
				 <?php echo $lang['EDIT_HELP_NAME']?>
			  </TD>
		   </TR>
		   <TR VALIGN="bottom">
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_LASTNAME_COMPANY']." </B>".$lang['LBL_REQUIRED']?> 
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="lastname" VALUE="<?php echo($contact_lastname); ?>">
			  </TD>
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_FIRSTNAME']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="firstname" VALUE="<?php echo($contact_firstname); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_MIDDLENAME']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="middlename" VALUE="<?php echo($contact_middlename); ?>">
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_ADDRESSES']?></TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="data">
					<?php echo $lang['EDIT_HELP_ADDRESS']?>
			  </TD>
			  </TR>
<?php
	// ADDRESSES
	// A do-while loop is made to ensure that there is 2 blank entries if person has NO address information.
	$tbl_address = mysql_fetch_array($r_address);
	$addnum = 0;
	do {
		$address_refid = $tbl_address['refid'];
		$address_type = stripslashes( $tbl_address['type'] );
		$address_line1 = stripslashes( $tbl_address['line1'] );
		$address_line2 = stripslashes( $tbl_address['line2'] );
		$address_city = stripslashes( $tbl_address['city'] );
		$address_state = stripslashes( $tbl_address['state'] );
		$address_zip = stripslashes( $tbl_address['zip'] );
		$address_phone1 = stripslashes( $tbl_address['phone1'] );
		$address_phone2 = stripslashes( $tbl_address['phone2'] );
		$address_country = stripslashes( $tbl_address['country'] );
?>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_TYPE']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_type_<?php echo($addnum); ?>" VALUE="<?php echo($address_type); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <INPUT TYPE="radio" NAME="address_primary_select" VALUE="address_primary_<?php echo($addnum); ?>"<?php if ($contact_primaryAddress == $address_refid) { echo (" CHECKED"); } ?>> <B><?php echo $lang['LBL_SET_AS_PRIMARY']?></B>
			  </TD>
			  <TD WIDTH=185 CLASS="data">
					<a href="#" onClick="deleteAddress(<?php echo $addnum ?>); return false;"><?php echo $lang['EDIT_DEL_ADD']?></a>
					<input type="hidden" name="address_refid_<?php echo $addnum ?>" value="<?php echo $address_refid ?>">
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_ADDRESS_LINE1']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_line1_<?php echo($addnum); ?>" VALUE="<?php echo($address_line1); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_ADDRESS_LINE2']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_line2_<?php echo($addnum); ?>" VALUE="<?php echo($address_line2); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   &nbsp;
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_CITY']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_city_<?php echo($addnum); ?>" VALUE="<?php echo($address_city); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_STATE']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_state_<?php echo($addnum); ?>" VALUE="<?php echo($address_state); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_ZIPCODE']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_zip_<?php echo($addnum); ?>" VALUE="<?php echo($address_zip); ?>">
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_PHONE1']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_phone1_<?php echo($addnum); ?>" VALUE="<?php echo($address_phone1); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_PHONE2']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_phone2_<?php echo($addnum); ?>" VALUE="<?php echo($address_phone2); ?>">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_COUNTRY']?></B>
					<BR><SELECT NAME="address_country_<?php echo($addnum); ?>" CLASS="formSelect" STYLE="width:160px;">
<?php
	// -- GENERATE COUNTRY SELECTION LIST --
	// This sort routine can handle country names with special characters
	foreach ($country as $country_id=>$val) {
		$sortarray[$country_id] = strtr($val,"¿¡¬√ƒ≈»… ÄÀÃÕŒœ—“”‘’÷Ÿ⁄€‹›‡·‚„‰ÂËÈÍÎÏÌÓÔÒÚÛÙıˆ˘˙˚¸˝ˇ", "AAAAAAAEEEEIIIINOOOOOUUUUYaaaaaaeeeeiiiinooooouuuuyy");
	}
	asort($sortarray);
	$addressOK=0;
	foreach(array_keys($sortarray) as $country_id) {
		echo("<option value=$country_id");
		if ($mode == 'new' AND $country_id == $options->countryDefault){
		echo(" selected");
		}
		if ($country_id == $address_country AND $mode=='edit') {
			echo(" selected");
			$addressOK=1;
		}
		elseif ($country_id == $options->countryDefault AND $addressOK==0) {
			echo(" selected");
		}
		echo ">" . $country[$country_id] . "</option>\n";
	}
?>
					</SELECT>
			  </TD>
		   </TR>


		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listDivide">&nbsp;</TD>
		   </TR>
<?php
		// drop back into PHP mode and close off the loop
		$addnum++;
	} while ($tbl_address = mysql_fetch_array($r_address));
	
	// PRINT A BLANK ADDRESS FIELD
?>
		<!--   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_TYPE']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_type_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <INPUT TYPE="radio" NAME="address_primary_select" VALUE="address_primary_<?php echo($addnum); ?>"> <B><?php echo $lang['LBL_SET_AS_PRIMARY']?></B>
			  </TD>
			  <TD WIDTH=185 CLASS="data">
					<a href="#" onClick="deleteAddress(<?php echo $addnum ?>); return false;"><?php echo $lang['EDIT_DEL_ADD']?></a>
					<input type="hidden" name="address_refid_<?php echo $addnum ?>" value="">
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_ADDRESS_LINE1']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_line1_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_ADDRESS_LINE2']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_line2_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   &nbsp;
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_CITY']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_city_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_STATE']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_state_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_ZIPCODE']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_zip_<?php echo($addnum); ?>" VALUE="">
			  </TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
				   <B><?php echo $lang['LBL_PHONE1']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_phone1_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_PHONE2']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="address_phone2_<?php echo($addnum); ?>" VALUE="">
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <B><?php echo $lang['LBL_COUNTRY']?></B>
					<BR><SELECT NAME="address_country_<?php echo($addnum); ?>" CLASS="formSelect" STYLE="width:160px;"
<?php
	// -- GENERATE COUNTRY SELECTION LIST --
	foreach ($country as $country_id=>$val) {
		$sortarray[$country_id] = strtr($val,"¿¡¬√ƒ≈»… ÄÀÃÕŒœ—“”‘’÷Ÿ⁄€‹›‡·‚„‰ÂËÈÍÎÏÌÓÔÒÚÛÙıˆ˘˙˚¸˝ˇ", "AAAAAAAEEEEIIIINOOOOOUUUUYaaaaaaeeeeiiiinooooouuuuyy");
	}
	asort($sortarray);

	$addressOK=0;
	foreach(array_keys($sortarray) as $country_id) {
		echo("<option value=$country_id");
		if ($country_id == $options->countryDefault){
		echo(" selected");
		}
		echo ">" . $country[$country_id] . "</option>\n";
	}
?>
					</SELECT> -->
				   <!-- sends to SAVE the last number of the address block. -->
				   <INPUT TYPE="hidden" NAME="addnum" VALUE="<?php echo($addnum); ?>">
			  </TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_EMAIL_ADDRESSES']?></TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
<TEXTAREA STYLE="width:150px;" ROWS=6 CLASS="formTextarea" NAME="<?php echo(TABLE_EMAIL); ?>" WRAP=off>
<?php
	// E-mail
	while ($tbl_email = mysql_fetch_array($r_email)) {
		$email_address = stripslashes( $tbl_email['email']);
		$email_type = stripslashes( $tbl_email['type']);
		echo("$email_address|$email_type\n");
	}
?>
</TEXTAREA>
			  </TD>
			  <TD WIDTH=370 CLASS="data" COLSPAN=2>
			  <?php echo $lang['EDIT_HELP_EMAIL']?>
			  </TD>
		   </TR>


<!--		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_OTHERPHONE']?></TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
<TEXTAREA STYLE="width:150px;" ROWS=6 CLASS="formTextarea" NAME="<?php echo(TABLE_OTHERPHONE); ?>" WRAP=off>
<?php
	// Other Phone Numbers
	while ($tbl_otherPhone = mysql_fetch_array($r_otherPhone)) {
		$otherphone_phone = stripslashes( $tbl_otherPhone['phone'] );
		$otherphone_type = stripslashes( $tbl_otherPhone['type'] );
		echo("$otherphone_phone|$otherphone_type\n");
	}
?>
</TEXTAREA>
			  </TD>
			  <TD WIDTH=370 CLASS="data" COLSPAN=2>
					<?php echo $lang['EDIT_HELP_OTHERPHONE']?>
			 </TD>
		   </TR> -->

		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_MESSAGING']?></TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
<TEXTAREA STYLE="width:150px;" ROWS=6 CLASS="formTextarea" NAME="<?php echo(TABLE_MESSAGING); ?>" WRAP=off>
<?php
	// Messaging
	while ($tbl_messaging = mysql_fetch_array($r_messaging)) {
		$messaging_handle = stripslashes( $tbl_messaging['handle'] );
		$messaging_type = stripslashes( $tbl_messaging['type'] );
		echo("$messaging_handle|$messaging_type\n");
	}
?>
</TEXTAREA>
			  </TD>
			  <TD WIDTH=370 CLASS="data" COLSPAN=2>
					<?php echo $lang['EDIT_HELP_MESSAGING']?>
			  </TD>
		   </TR>



		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_WEBSITES']; ?></TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=375 CLASS="data" COLSPAN=2>
<TEXTAREA STYLE="width:340px;" ROWS=6 CLASS="formTextarea" NAME="<?php echo(TABLE_WEBSITES); ?>" WRAP=off>
<?php
	// Websites
	while ($tbl_websites = mysql_fetch_array($r_websites)) {
		$website_URL = stripslashes( $tbl_websites['webpageURL'] );
		$website_name = stripslashes( $tbl_websites['webpageName'] );
		echo("$website_URL|$website_name\n");
	}
?>
</TEXTAREA>
			  </TD>
			  <TD WIDTH=185 CLASS="data">
					 <?php echo $lang['EDIT_HELP_WEBSITES']?>
			  </TD>
		   </TR>


			<TR VALIGN="top">
				<TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_OTHERINFO']?></TD>
			</TR>

			<TR VALIGN="top">
				<TD WIDTH=190 CLASS="data">
					<B><?php echo $lang['LBL_BIRTHDATE']?>(yyyy-mm-dd)</B>
					<BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="birthday" VALUE="<?php echo($contact_birthday); ?>">
				</TD>
				<TD WIDTH=185 CLASS="data">
					<B><?php echo $lang['LBL_PICTURE_URL']?></B>
					<BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="pictureURL" VALUE="<?php echo($contact_pictureURL); ?>">
<?php
	// Display Upload link if allowed by options
	if (($options->picAllowUpload == 1) || ($_SESSION['usertype'] == "admin")) {
		echo("<BR><A HREF=\"#\" onClick=\"window.open('" . FILE_UPLOAD . "','uploadWindow','width=450,height=250'); return false;\">".$lang['LBL_UPLOAD_PICTURE']."</A>\n");
	}
?>
				</TD>
				<TD WIDTH=185 CLASS="data">
					<B><?php echo $lang['LBL_NICKNAME']?></B>
					<BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="nickname" VALUE="<?php echo($contact_nickname); ?>">
				</TD>
			</TR>

		   <TR VALIGN="top">
			  <TD WIDTH=375 CLASS="data" COLSPAN=2>
<TEXTAREA STYLE="width:340px;" ROWS=9 CLASS="formTextarea" NAME="<?php echo(TABLE_ADDITIONALDATA); ?>" WRAP=off>
<?php
	// AdditionalData
	while ( $tbl_additionalData = mysql_fetch_array($r_additionalData) ) {
		$additionaldata_type = stripslashes( $tbl_additionalData['type'] );
		$additionaldata_value = stripslashes( $tbl_additionalData['value'] );
		echo("$additionaldata_type|$additionaldata_value\n");
	}
?>
</TEXTAREA>
			  </TD>
			  <TD WIDTH=185 CLASS="data">
					 <?php echo $lang['EDIT_HELP_OTHERINFO']?>
			  </TD>
		   </TR>


		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_NOTES']?></TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=560 CLASS="data" COLSPAN=3>
<?php echo $lang['EDIT_HELP_NOTES']?><BR>

<TEXTAREA STYLE="width:530px;" ROWS=6 CLASS="formTextarea" NAME="notes" WRAP=virtual>
<?php
	// Notes
	echo("$contact_notes");
?>
</TEXTAREA>
			  </TD>
		   </TR>


		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['LBL_GROUPS']?></TD>
		   </TR>
		   <TR VALIGN="top">
			  <TD WIDTH=190 CLASS="data">
<?php

	// Display Group Checkboxes.
	$groupsql = "SELECT grouplist.groupid, groupname, id 
				 FROM " . TABLE_GROUPLIST . " AS grouplist
				 LEFT JOIN " . TABLE_GROUPS . " AS groups
				 ON grouplist.groupid=groups.groupid AND id=$id
				 WHERE grouplist.groupid >= 3
				 ORDER BY groupname";
	$r_grouplist = mysql_query($groupsql, $db_link);
	$numGroups = mysql_num_rows($r_grouplist);
	$numGroups = round($numGroups/2);  // assigns to $numGroups the number of Groups to display in the first column.
	$x = 0;
	$groupCheck = ""; 

	// COLUMN 1
	// $x is checked FIRST because if that fails, $tbl_grouplist will have already been evaluated
	while ( ($x < $numGroups) && ($tbl_grouplist = mysql_fetch_array($r_grouplist)) ) {
		$group_id = $tbl_grouplist['groupid'];
		$group_name = $tbl_grouplist['groupname'];
		if ( $tbl_grouplist['id'] == $id ) {
			$groupCheck = " CHECKED";
		}
		echo("<INPUT TYPE=\"checkbox\" NAME=\"groups[]\" VALUE=\"$group_id\"$groupCheck><B>$group_name</B>\n<BR>");
		//reset $groupCheck so that it doesn't stay set if the next ID does not equal $id.
		$groupCheck = "";
		$x++;
	}

?>
			  </TD>
			  <TD WIDTH=185 CLASS="data">
<?php
	// COLUMN 2
	while ($tbl_grouplist = mysql_fetch_array($r_grouplist)) {
		$group_id = $tbl_grouplist['groupid'];
		$group_name = $tbl_grouplist['groupname'];
		if ( $tbl_grouplist['id'] == $id ) {
			$groupCheck = " CHECKED";
		}
		echo("<INPUT TYPE=\"checkbox\" NAME=\"groups[]\" VALUE=\"$group_id\"$groupCheck><B>$group_name</B>\n<BR>");
		//reset $groupCheck so that it doesn't stay set if the next ID does not equal $id.
		$groupCheck = "";
	}
?>
			  </TD>
			  <TD WIDTH=185 CLASS="data">
				   <INPUT TYPE="checkbox" NAME="groupAddNew" VALUE="addNew"><B><?php echo $lang['EDIT_ADD_NEW_GROUP']?></B>
				   <BR><INPUT TYPE="text" SIZE=20 CLASS="formTextbox" NAME="groupAddName" VALUE="" MAXLENGTH=60>
			  </TD>
		   </TR> 


		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listDivide">&nbsp;</TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=560 CLASS="data" COLSPAN=3>
<?php
	echo("<INPUT TYPE=\"checkbox\" NAME=\"hidden\" VALUE=\"1\"");
	if ( $contact_hidden == 1 ) {
			echo(" CHECKED");
	}
	echo("><B>".$lang['EDIT_HIDE_ENTRY']."</B>");
?>
			  </TD>
		   </TR> 

		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="listDivide">&nbsp;</TD>
		   </TR>

		   <TR VALIGN="top">
			  <TD WIDTH=560 COLSPAN=3 CLASS="navmenu">
	  <A HREF="#" onClick="saveEntry(); return false;"><?php echo $lang['BTN_SAVE']?></A>
<?php
// PRINT CANCEL AND/OR DELETE BUTTONS
	if ($mode == 'new') {
		echo("      <A HREF=\"" . FILE_LIST . "\">".$lang['BTN_CANCEL']."</A>\n");
	}
	else { 
		echo("      <A HREF=\"#\" onClick=\"deleteEntry(); return false;\">".$lang['BTN_DELETE']."</A>\n");
		echo("      <A HREF=\"" . FILE_ADDRESS . "?id=$id\">".$lang['BTN_CANCEL']."</A>\n");
	}
?>
			  </TD>
		   </TR>


		</TABLE>

			   
	</TD>
  </TR>
  <TR>
	<TD CLASS="update">
<?php
	if ($mode == 'new') {
		echo("&nbsp;");
	}
	else { 
		echo "<br>".$lang['LAST_UPDATE']." ". $contact_lastUpdate;
	}
?>
	</TD>
  </TR>
</TABLE>
</CENTER>

</FORM>

</BODY>
</HTML>
<?php

	// DECLARE CALLBACK FUNCTION
	// The callback function looks for the text VAR_ADDNUM and replaces it with a number
	// equal to the number of address entries present in the document. This number is
	// not determined until the address code is processed, and it is necessary at the
	// top of the edit script for the "save" link. Therefore an output buffer is created
	// which allows address code to be processed, and then the callback function goes
	// through the buffer and replaces the VAR_ADDNUM before displaying the buffer.
	function callback($buffer) 	{
		global $addnum;
		return (str_replace("VAR_ADDNUM", $addnum, $buffer));
	}

	// OUTPUT BUFFER
	ob_end_flush();
?>
