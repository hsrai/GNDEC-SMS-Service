<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04
 *  
 *  lib/class-contactlist.php
 *  Object: Retrieves information relating to the contact list to be displayed.
 *
 *************************************************************/

// Original code by hannelore
 
class ContactList {
	
	var $group_id;
	var $group_name;
	var $current_letter;
	var $max_entries;
	var $current_page;
	var $total_pages;
	var $sql; 
	var $title;
	var $nav_menu;
	
	function ContactList() {
		global $options;
		
		// DEPENDENT VARIABLES -- Values for these variables are passed to the object after ContactList is created
		// If no values are provided, then it uses some defaults
		$this->group_id = 0;                       // defaults to 0 upon creation of object
		$this->current_page = 1;                   // defaults to first page
		$this->current_letter = $options->defaultLetter;	// defaults to value set in options
		$this->max_entries = $options->limitEntries; 		// defaults to value set in options; 0=no maximum (display all on page 1)

		// RESULTANT VARIABLES -- Values for these variables start out blank and will be filled in by this object's methods
		$this->group_name = "";                    // determined in $this->group_name()
		$this->total_pages = 1;                    // total # of pages, determined in $this->retrieve()
		$this->sql = "";                           // determined in $this->retrieve(), useful for debugging purposes
		$this->title = "";                         // determined in $this->title()
		$this->nav_menu = "";                      // determined in $this->create_nav()
	}
	
	function group_name() {
		global $db_link;
		global $lang;
		
		// OBTAIN NAME OF GROUP IN DISPLAYED LIST
		// Force $this->group_id to an integer equal to 0 or greater
		$this->group_id = intval($this->group_id);
		if ($this->group_id <= 0) $this->group_id = 0;
		
		// group_id = 0 --> "All Entries"
		if ($this->group_id == 0) $this->group_name = $lang['GROUP_ALL_LABEL'];
		// group_id = 1 --> "Ungrouped Entries"
		elseif ($this->group_id == 1) $this->group_name = $lang['GROUP_UNGROUPED_LABEL'];
		// group_id = 2 --> "Hidden Entries"
		elseif ($this->group_id == 2) {
			// Admin check
			if ($_SESSION['usertype'] != "admin") {
				reportScriptError("URL tampering detected.");
				exit();
			}
			$this->group_name = $lang['GROUP_HIDDEN_LABEL']; // "Hidden Entries"
		}
		// group_id >= 3 --> Check the database for user-defined group
		else {
			$tbl_grouplist = mysql_fetch_array(mysql_query("SELECT * FROM " . TABLE_GROUPLIST . " AS grouplist WHERE groupid=$this->group_id", $db_link));
			$this->group_name = $tbl_grouplist['groupname'];
			// Reassign to "All Entries" if given a groupid that doesn't exist
			if ($this->group_name == "") {
				$this->group_id = 0;
				$this->group_name = "All Entries";
			}
		}
		// Return value
		return $this->group_name;
	}



	function title() {
		$this->title = $this->group_name;
		
		if (!empty($this->current_letter)) $this->title .= " - $this->current_letter";
		if ($this->total_pages > 1) $this->title .= " (page $this->current_page of $this->total_pages)";
		
		return $this->title;
	}


	function retrieve($uname) {
		global $db_link;
		
	 	// The following needs to be set to retrieve correctly
	 	// $this->group_id
	 	// $this->current_letter
	 	// $this->max_entries
	 	// $this->current_page
	 	
	 	// CREATE INITIAL SQL FRAGMENT
		$this->sql = "SELECT contact.id, CONCAT(lastname,', ',firstname) AS fullname, lastname, firstname,
						refid, line1, line2, city, state, zip, phone1, phone2, country, whoAdded
						FROM ( " . TABLE_CONTACT . " AS contact ";

	    // CREATE SQL FRAGMENTS TO FILTER BY GROUP
		// group_id = 0 --> "All Entries"
		if ($this->group_id == 0) {
			$sql_group = ") LEFT JOIN " . TABLE_ADDRESS . " AS address ON contact.id=address.id AND contact.primaryAddress=address.refid
						WHERE contact.hidden != 1";
    	}
		// group_id = 1 --> "Ungrouped Entries"
	    elseif ($this->group_id == 1) {
			$sql_group = ") LEFT JOIN " . TABLE_ADDRESS . " AS address ON contact.id=address.id AND contact.primaryAddress=address.refid
						LEFT JOIN " . TABLE_GROUPS . " AS groups ON groups.id=contact.id
						WHERE groups.id IS NULL AND contact.hidden != 1 ";
	    }
		// group_id = 2 --> "Hidden Entries"
	    elseif ($this->group_id == 2) {
			$sql_group = ") LEFT JOIN " . TABLE_ADDRESS . " AS address ON contact.id=address.id AND contact.primaryAddress=address.refid
						WHERE contact.hidden = 1 ";
	    }
	    // group_id >= 3 --> Specified user-defined group
	    else { 
			$sql_group = ", " . TABLE_GROUPS . " AS groups)
						LEFT JOIN ". TABLE_ADDRESS ." AS address ON contact.id=address.id AND contact.primaryAddress=address.refid
						WHERE contact.id=groups.id AND groups.groupid=$this->group_id AND contact.hidden != 1 ";
	
	    }

		// CREATE SQL FRAGMENTS TO FILTER BY LETTER
		switch ($this->current_letter) {
			case "":	// No letter filter
				$sql_letter = "";  
				break;	
			case "1":	// If selecting non-alphabetical characters
				$sql_letter = " AND lastname REGEXP  '^[^[:alpha:]]'";
				break;	
			default:	// If a letter is set
				$sql_letter = " AND lastname LIKE '$this->current_letter%'";
				break;
		}

		// CREATE SQL FRAGMENTS TO LIMIT NUMBER OF ENTRIES PER PAGE
		if ($this->max_entries > 0) { //if this option is set, limit the number of entries shown per page
			// Count number of rows (this uses group and letter sql fragments, determined previously)
			$count = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM " . TABLE_CONTACT . " AS contact" . $sql_group . $sql_letter, $db_link));
			$this->total_pages = intval(ceil($count[0]/$this->max_entries)); //divide the total entries by the limit per page. Round up to an integer
		
			// Users like to start counting from 1 in stead of 0
			$lowerLimit = $this->current_page - 1; //deduct 1 from the result page number in the URL, use this to calculate the lower limit of the range
			$lowerLimit = $lowerLimit*$this->max_entries; //lower limit of the range
			$sql_limit = " LIMIT $lowerLimit, $this->max_entries";
		}

		// ASSEMBLE THE SQL QUERY
		$this->sql .= $sql_group . $sql_letter . " ORDER BY fullname" . $sql_limit;
		
		// EXECUTE THE SQL QUERY
		$r_contact = mysql_query($this->sql, $db_link)
			or die(reportSQLError($this->sql));
			
		// RETURN RESULTS OF QUERY
		return $r_contact;
	}
	
	
	function nav_abc($link) {
		$abc = array('A' => 'A',
					'B' => 'B',
					'C' => 'C',
					'D' => 'D',
					'E' => 'E',
					'F' => 'F',
					'G' => 'G',
					'H' => 'H',
					'I' => 'I',
					'J' => 'J',
					'K' => 'K',
					'L' => 'L',
					'M' => 'M',
					'N' => 'N',
					'O' => 'O',
					'P' => 'P',
					'Q' => 'Q',
					'R' => 'R',
					'S' => 'S',
					'T' => 'T',
					'U' => 'U',
					'V' => 'V',
					'W' => 'W',
					'X' => 'X',
					'Y' => 'Y',
					'Z' => 'Z',
					'1' => '[0-9]',
					'' => '[all]');
		foreach ($abc as $key => $letter) {
			$this->nav_menu .= "<a href='$link$key'>$letter</a>\n";
		}
	}
	
	function nav_pages($link) {
		if ($this->total_pages > 1) { //check whether there are multiple result pages for the request
			$this->nav_menu .= "Pages: ";
			for ($i=1; $i <= $this->total_pages; $i++) { //create an array of links to the result pages
				if ($this->current_page == $i) { //indicate the current page in the navigation 
					$this->nav_menu .= "<b>[$i]</b>\n";	
				}
				else { //create links to all other result pages
					$this->nav_menu .= "<a href='$link$this->current_letter&amp;page=$i'>$i</a> \n";
				}
			}
			$this->nav_menu .= "<a href='$link$this->current_letter&amp;limit=0'>[all]</a>\n";
		}
	}
	
	function create_nav() {
		/*
		Here's the logic behind the navigation links:
			If we have...			Then the links will have....
			group	letter	page	
		1.	x						(always occurs) - use letter links w/ #
		2.	x		x				use letter links only (no page links)
		3.	x		x		x		use page links within current letter, and letter links to page 1
		4.	x				x		use page links only (no letter)
		*/
		// Base link
		$link = $_SERVER['PHP_SELF'] . "?groupid=$this->group_id";
		
		// Case 2. Group and letter (no page)
		if ((!empty($this->current_letter)) && ($this->total_pages <= 1)) { 
			$link .= "&amp;letter=";
			$this->nav_abc($link);
		} 
		// Case 3. Group, letter, and page.
		elseif ((!empty($this->current_letter)) && ($this->total_pages > 1)) {
			$link .= "&amp;limit=$this->max_entries&amp;letter=";
			$this->nav_abc($link);
			$this->nav_pages($link);
		}
		// Case 4. Group and page (no letter)
		elseif ((empty($this->current_letter)) && ($this->total_pages > 1)) {
			$link .= "&amp;limit=$this->max_entries";
			$this->nav_pages($link);
		}
		// Case 1. (default) Group only.
		else { 
			//$link .= "#";
			$link = "#";
			$this->nav_abc($link);
		}
		
		return $this->nav_menu;
	}
	

}
// END ContactList
?>
