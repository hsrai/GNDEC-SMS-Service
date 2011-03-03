<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04e
 *     
 *****************************************************************
 *  save.php
 *  Saves address book entries.
 *
 *************************************************************/

// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	session_start();
	$username = $_SESSION['username'];

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin("admin","user");

// ** CHECK FOR ID **
	$mode = $_GET['mode'];
	if ($mode != 'new') {
		$id = check_id();
		// Check user for whoAdded
		$tbl_contact = mysql_fetch_array(mysql_query("SELECT whoAdded FROM " . TABLE_CONTACT . " AS contact WHERE contact.id=$id", $db_link))
			or die(reportSQLError());
		$contact_whoAdded = stripslashes($tbl_contact['whoAdded']);
		if ((($contact_whoAdded != $_SESSION['username']) AND ($_SESSION['usertype'] != 'admin')) OR ($_SESSION['usertype'] == 'guest')){
			$_SESSION = array();
		 	session_destroy();
			reportScriptError("URL tampering detected. You have been logged out.");
		}
	}else{
		$contact_whoAdded = $_SESSION['username'];
	}	
 
// ** END INITIALIZATION *******************************************************
// *****************************************************************************
// -- FUNCTION DECLARATIONS --


function runQuery($sql) {
	global $db_link;
	$result = mysql_query($sql, $db_link)
		or die(reportSQLError($sql));
	return $result;
	// end function
}


function parseTextArea($table) {
	// make outside variables accessible within the function scope
	global $id, $db_link;

	// Get number of existing (old) rows so we know how many to remove later.
	global $numOldRows;
	$numOldRows = mysql_num_rows(mysql_query("SELECT * FROM $table WHERE ID=$id", $db_link)); 

	// trim any whitespace characters from the beginning and end of the textarea string
	// this removes, for instance, any extra newline characters at the end of the string
	$txtarea = trim($_POST[$table]);

	// if textarea is empty and there are no old rows, then exit the function
	if (!$txtarea && $numOldRows == 0) { return 0; }

	// if textarea is empty and there *are* old rows, then there is no updates to be done. proceed to delete
	if (!$txtarea && $numOldRows > 0) { 
		removeOldRows($table, $numOldRows); 
		return 1;
	}


	// Splits textarea lines into an array.
	$newEntry = explode("\n",$txtarea);

	// Obtain the number of new entries.

	$x = 0;
	while (each($newEntry)) {
		$x++;
	} 
	reset($newEntry);

	// pulls the rows out of the array.
	// then splits up the rows into values which are then added to the database.
	for ($y = 0; $y < $x; $y++) {
		$newRow = "newRow" . $y;
		$$newRow = $newEntry[$y];
		$newRowArray = explode("|",$$newRow);   // turns it into another array

/*
// OLD
// All textareas assume that there are 2 fields per row. One is the data itself,
// the other is the "type" of data. Not all fields require types. E-mail, for
// instance, often do not require one. So the second field in each row can be
// assumed to be optional for the purposes of this script.
// The old code allowed for any number of new values in a row, seperated by the
// pipe character. In reality, this is useless. No rows have more or less than
// two fields.
// The following code is retained for future-use purposes, in case we do need
// code that allows for variable fields per row. However, for the purposes of the
// current script, we will use code that assumes 2 fields per row. If a second
// field is empty, it will create blank (empty) data. If a first field is empty,
// it will ignore the row.
		// obtains number of values in row.
		$z = 0;
		while (each($newRowArray)) {
			$z++;
		} 
		reset($newRowArray);

		// creates initial sql command
		$sql = "INSERT INTO $table VALUES ($id";

		// now we store each seperate value of the row into variables.
		for ($a = 0; $a < $z; $a++) {
			$newValue = newValue . $a;
			$$newValue = $newRowArray[$a];

			// fix up the string - first strips whitespace, then strips tags, then adds slashes to the final product
			$$newValue = addslashes( strip_tags( trim($$newValue) ) );

			// concatenate this to $sql
			$sql = $sql . ", '" . $$newValue . "'";

		}

		// create mysql command
		$sql = $sql . ")";
*/
// NEW TEXT AREA PARSE CODE

		// Turn the contents of the textarea row into something we can attach to the query
		// $newRowArray[0] = First value (required for entry)
		// $newRowArray[1] = Second value (optional)
		// $newRowArray[2,3,...] = ignored completely

		// Clean up
		$newRowArray[0] = addslashes(strip_tags(trim($newRowArray[0])));
		$newRowArray[1] = addslashes(strip_tags(trim($newRowArray[1])));
		
		// Insert
		if ($newRowArray[0] != "") {
			$sql = "INSERT INTO $table VALUES ($id, '$newRowArray[0]', '$newRowArray[1]')";
			runQuery($sql);
		}
	}

	// remove old entries, which should exist in the table before the new entries, and optimize
	removeOldRows($table, $numOldRows);
	optimizeTable($table);

	//end function
}


function removeOldRows($table, $numOldRows) {
	global $id, $db_link;

	// Once additions are completed, remove old rows.
	// This must check to make sure old rows actually exist, otherwise it'll delete everything you just added. 
	// Deleting is done only if $numOldRows is greater than 0, because 'LIMIT 0' is a meaningless statement in MySQL.
	if ($numOldRows > 0) {
		runQuery("DELETE FROM $table WHERE ID=$id LIMIT $numOldRows");
	}

	// reset to 0 just in case
	$numOldRows = 0;

	// end function
}


function optimizeTable($table) {
	global $db_link;
	mysql_query("OPTIMIZE TABLE $table", $db_link)
		or die(reportScriptError("<B>There was a problem optimizing table $table.</B>"));
	// end function
}

// -- END FUNCTION DECLARATION --
// *****************************************************************************

// -- DETERMINE SAVE MODE --

	/*
	There are 3 save modes. $_GET['mode'] can be equal to:
	1. 'new' 	Add a new entry.
	2. 'edit' 	Edit an existing entry.
	3. 'delete'	Remove the entry.
	*/
	$mode = $_GET['mode'];
	if (($mode != 'new') && ($mode != 'edit') && ($mode != 'delete')) {
		reportScriptError("No save mode or invalid save mode.");
	}

// -- VARIABLE PROCESSING --
	
	// Obtain and fix up submitted contact information
	if (($mode == 'new') || ($mode == 'edit')) {
		if (empty($_POST['lastname'])) {
			reportScriptError("Last Name or Company Name field is empty. A last name or a company name must be provided for an entry to exist.");
		}

		// Get $LastUpdate variable
		$lastUpdate = mysql_fetch_array(mysql_query("SELECT NOW() AS lastUpdate", $db_link));
		$lastUpdate = $lastUpdate['lastUpdate'];
	
		// fix up any strings
		$contact_firstname = addslashes(strip_tags(trim( $_POST['firstname'] )));
		$contact_lastname = addslashes(strip_tags(trim( $_POST['lastname'] )));
		$contact_middlename = addslashes(strip_tags(trim( $_POST['middlename'] )));
		$contact_birthday = addslashes(strip_tags(trim( $_POST['birthday'] )));
		$contact_nickname = addslashes(strip_tags(trim( $_POST['nickname'] )));
		$contact_pictureURL = addslashes(strip_tags(trim( $_POST['pictureURL'] )));
		$contact_notes = htmlspecialchars(addslashes(strip_tags(trim( $_POST['notes'] ))));
		
		// shall we hide entry?
		$contact_hidden = ($_POST['hidden']) ? 1 : 0;
	}
	
	// If new, insert it now so we can get the id.
	// Barebones insertion only! The rest will be done at the end.
	if ($mode == 'new') {
		$sql = "INSERT INTO " . TABLE_CONTACT . " (id) VALUES ('')";
//		$sql = "INSERT INTO " . TABLE_CONTACT . " VALUES
//					  ('', '$contact_firstname', '$contact_lastname', '$contact_middlename', '$contact_primaryAddress',
//					  '$contact_birthday', '$contact_nickname', '$contact_pictureURL', '$contact_notes', '$lastUpdate', $contact_hidden, '" .$_SESSION['username']. "')";
		runQuery($sql);
		$getID = mysql_fetch_row(mysql_query("SELECT LAST_INSERT_ID()", $db_link));
		$id = $getID[0];  
	}	


// -- PROCESS ADDRESSES --

	// Add Addresses
	// $addnum is a variable sent from EDIT. It equals ((number of address blocks displayed) - 1).
	// The first address block starts from 0 and counts upward.
	for ( $x = 0; $x <= $_POST['addnum']; $x++ ) {

		// Retrieve form data, clean up, and assign to variables
		$address_refid = $_POST['address_refid_' . $x];
		$address_type = addslashes( strip_tags( trim($_POST['address_type_' . $x]) ) );
		$address_line1 = addslashes( strip_tags( trim($_POST['address_line1_' . $x]) ) );
		$address_line2 = addslashes( strip_tags( trim($_POST['address_line2_' . $x]) ) );
		$address_city = addslashes( strip_tags( trim($_POST['address_city_' . $x]) ) );
		$address_state = addslashes( strip_tags( trim($_POST['address_state_' . $x]) ) );
		$address_zip = addslashes( strip_tags( trim($_POST['address_zip_' . $x]) ) );
		$address_phone1 = addslashes( strip_tags( trim($_POST['address_phone1_' . $x]) ) );
		$address_phone2 = addslashes( strip_tags( trim($_POST['address_phone2_' . $x]) ) );
		$address_country = addslashes( strip_tags( trim($_POST['address_country_' . $x]) ) );
		$address_primary = "address_primary_" . $x;
		
		// Check for blanks. If not, use REPLACE INTO
		if (empty($address_type) && empty($address_line1) && empty($address_line2) && empty($address_city) && empty($address_state) && empty($address_zip) && empty($address_phone1) && empty($address_phone2)) {
			// If there is a refid, that means the blank address is marked for deletion
			if (!empty($address_refid)) {
				runQuery("DELETE FROM " . TABLE_ADDRESS . " WHERE refid=$address_refid LIMIT 1");
			}
			// Else it is a unfilled blank, in which case ignore
		}
		else {
			runQuery("REPLACE INTO " . TABLE_ADDRESS . " VALUES ('$address_refid', $id, '$address_type', '$address_line1', '$address_line2', '$address_city', '$address_state', '$address_zip', '$address_country', '$address_phone1', '$address_phone2')");
			// If there is no refid already provided, obtain the new one created by auto_increment
			if (empty($address_refid)) {
				$address_refid = mysql_fetch_row(runQuery("SELECT LAST_INSERT_ID()"));
				$address_refid = $address_refid[0];  
			}
		}
		
		// Check to see if radio checkbox address_primary_select was selected.
		// If it is, Browser will send "address_primary_x" 
		// If it matches, that means this is the primary address.
		if ($address_primary == $_POST['address_primary_select']) {
			$contact_primaryAddress = $address_refid;
		}

	}
	
	// Delete addresses if contact is to removed
	if ($mode == 'delete') {
		runQuery("DELETE FROM " . TABLE_ADDRESS . " WHERE id=$id");
	}
		
	// Optimize address table.
	optimizeTable(TABLE_ADDRESS);

	
// -- END ADDRESS PROCESSING CODE --


// -- PROCESS TEXT AREAS --

	// Note that in case of entry deletion, this is treated as parsing "empty" text fields.
	parseTextArea(TABLE_EMAIL);
	parseTextArea(TABLE_OTHERPHONE);
	parseTextArea(TABLE_MESSAGING);
	parseTextArea(TABLE_WEBSITES);
	parseTextArea(TABLE_ADDITIONALDATA);


// -- PROCESS GROUPS --

	// Get number of existing (old) rows so we know how many to remove later.
	$numOldRows = mysql_num_rows(mysql_query("SELECT * FROM " . TABLE_GROUPS . " WHERE id=$id", $db_link));

	// remove old entries, which should exist in the table before the new entries, and optimize
	removeOldRows(TABLE_GROUPS, $numOldRows);
	optimizeTable(TABLE_GROUPS);

	// getting old rows and removing rows does not exist in the while loop because unchecking
	// groups assumes you want to delete the associated groups.
	// Also, deleting occurs FIRST because inserting first MAY result in duplicate entries.
	// Since the entire Groups table is set as a primary key an error will occur when inserting
	// a duplicate.

	// Insert "new" Group ID's into Groups table.
	// This WILL NOT do query batches, since I'm assuming no error checking needs
	// to be done on this data.
	if ($_POST['groups']) {
		while (list ($x_key, $x_gid) = each ($_POST['groups'])) {
			$groupsql = "INSERT INTO " . TABLE_GROUPS . " VALUES ($id,$x_gid)";
			runQuery($groupsql);
		}
	}
  
	// ADD A NEW GROUP?
	// if EDIT returns a new Group Addition, obtain a new GroupID for that Group, then
	// add the data!

	if (($_POST['groupAddNew'] == "addNew") && ($_POST['groupAddName'] != "")) {
		$r_newGroupID = mysql_query("SELECT groupid FROM " . TABLE_GROUPLIST . " ORDER BY groupid DESC LIMIT 1", $db_link);
		$t_newGroupID = mysql_fetch_array($r_newGroupID);
		$newGroupID = $t_newGroupID['groupid'];
		$newGroupID = $newGroupID + 1;

		// Insert New Group Data
		$newgroupsql = "INSERT INTO " . TABLE_GROUPLIST . " (groupid, groupname, whoAdded) VALUES ($newGroupID, '" . $_POST['groupAddName'] . "', '".$username."')";
		runQuery($newgroupsql);

		// Insert New Group entry for this person into the Groups list.
		$groupsql = "INSERT INTO " . TABLE_GROUPS . " (id, groupid) VALUES ($id, '" . $newGroupID . "')";
		runQuery($groupsql);

	}


// -- ENTER CONTACT INFO INTO DATABASE --

/*  The Contact table works differently from other tables.
	It works under the assumption that all entries MUST have a contact entry and that
	there is only ONE row of data per entry.
	This is designed to test for 3 conditions:
	  - If the $_GET['mode'] variable equals 'delete' then it is an indication to DELETE the
		entry. In this case removeOldRows() is called to remove the one row in Contact.
	  - If the $id variable does not equal the $nextContact number, then it is assumed
		to be an entry that already exists. Therefore it UPDATEs the row rather than
		INSERT/DELETE in order to preserve the ID order. If this row is deleted it
		may not be possible to UPDATE therefore causing this id to never be reused
		again.
	  - If neither of the above two conditions are met then the entry is assumed
		to be a new one, and an INSERT is performed.
*/

	// If contact is to be deleted
	if ($mode == 'delete') {
		removeOldRows(TABLE_CONTACT, 1);
		optimizeTable(TABLE_CONTACT);
		echo("<B>".$lang['EDIT_REMOVED']."</B>\n");
		echo("<P><B><A HREF=\"" . FILE_LIST . "\">".$lang['BTN_LIST']."</B>\n");
		exit();
	}

	// Update the contact if mode is 'edit' or 'new'
	if (($mode == 'edit') || ($mode == 'new')) {
		$sql = "UPDATE " . TABLE_CONTACT . " SET 
								firstname = '$contact_firstname',
								lastname = '$contact_lastname',
								middlename = '$contact_middlename',
								primaryAddress = '$contact_primaryAddress',
								birthday = '$contact_birthday',
								nickname = '$contact_nickname',
								pictureURL = '$contact_pictureURL',
								notes = '$contact_notes',
								lastUpdate = '$lastUpdate',
								hidden = $contact_hidden,
								whoAdded = '$contact_whoAdded'
							WHERE id=$id LIMIT 1";
		runQuery($sql);
	}	


// -- END PROCESSING OF DATA --

	// Now let's redirect the person back to the entry.
	header("Location: " . FILE_ADDRESS . "?id=$id");

?>
