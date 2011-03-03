<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *  
***************************************************************
 *  classes.php
 *  Sets options for address book.
 *
 *************************************************************/

class Contact {
	
	// DECLARE MEMBER VARIABLES
	var $contact;
	var $id;
	
	var $firstname;
	var $middlename;
	var $lastname;
	var $primary_address;
	var $birthday;
	var $nickname;
	var $picture_url;
	var $notes;
	var $last_update;
	var $hidden;
	var $who_added;

	var $fullname;
	
	// CONSTRUCTOR
	function Contact($id) {
		global $db_link;

		$this->id = $id; // Assume the ID given is legit. No checks are performed.
		$this->contact = mysql_fetch_array(mysql_query("SELECT * FROM " . TABLE_CONTACT . " AS contact WHERE contact.id=" . $this->id, $db_link))
			or die(reportSQLError());

		// Fill in variables from database			
		$this->firstname        = stripslashes( $this->contact['firstname'] );
		$this->lastname         = stripslashes( $this->contact['lastname'] );
		$this->middlename       = stripslashes( $this->contact['middlename'] );
		$this->primary_address  = stripslashes( $this->contact['primaryAddress'] );
		$this->birthday         = $this->birthday('%M %e, %Y');
		$this->nickname         = stripslashes( $this->contact['nickname'] );
		$this->picture_url      = stripslashes( $this->contact['pictureURL'] );
		$this->notes            = stripslashes( nl2br( $this->contact['notes'] ));
		$this->last_update      = $this->last_update('%W, %M %e %Y (%h:%i %p)'); 
		$this->hidden           = $this->contact['hidden'];
		$this->who_added        = stripslashes( $this->contact['whoAdded'] );
		
		// Create other variables
		$this->fullname       = $this->lastname . ", " . $this->firstname;

				// Put data into variable holders -- taken from arrays that are created from query results.
	}

	// METHODS!
	
	function last_update($format) {
		global $db_link;
		global $options;
		
		$tbl_lastUpdate = mysql_fetch_array(mysql_query("SELECT DATE_FORMAT(DATE_ADD(lastUpdate, INTERVAL " . $options->modifyTime . " HOUR), \"$format\") AS last_update FROM " . TABLE_CONTACT . " AS contact WHERE contact.id=$this->id", $db_link))
			or die(reportSQLError());
		return $tbl_lastUpdate['last_update'];
	}
	
	function birthday($format) {
		global $db_link;
		
		$tbl_birthday = mysql_fetch_array(mysql_query("SELECT DATE_FORMAT(birthday, \"$format\") AS birthday FROM " . TABLE_CONTACT . " AS contact WHERE contact.id=$this->id", $db_link))
			or die(reportSQLError());
		return $tbl_birthday['birthday'];
		/*
		Note on saving birthdays.
		We can use strtotime() (see http://us2.php.net/manual/en/function.strtotime.php)
		to take common date writing methods such as "September 27, 1983" or "3/1/86"
		and convert it to a timestamp which we can then use to save as the mysql date
		format.
		This is more user friendly.
		If the year is omitted then it uses the current year. In our implementation this
		should be a 0000 year which also means "I do not know". When there is a 0000 year
		found, the birthday method should refuse to display any such year or age.
		*/
	}

	function age() {
		// Returns the upcoming age of the person on his or her next birthday.
		return 0;
	}

	function age_current() {
		// Returns the current age of the person.
		$x = $this->age() - 1;
		return $x;
		// Note: What happens if the function is called on the day of?
	}

	// How to store addresses? Maybe another class?
	function address_primary() {
	}
	function address_all() {
	}
	function address($type) {
	}
	function phone_primary() {
	}
	function phone_all() {
		// retrieves all phone numbers associated with addresses and in other phone numbers table
	}
	function phone($type) {
		// retrieves all phone numbers of a specified type (check both address and otherphone tables)
	}
	function email_all() {
	}
	function email($type) {
	}

}



?>