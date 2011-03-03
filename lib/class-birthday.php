<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04
 *  
 *  lib/class-birthday.php
 *  Object: Creates birthday list
 *
 *************************************************************/
// NOT DONE.
// Maybe this file should extent Contact. All the information such as names, dates, etc. should be
// determined by ID in the Contact object.
// Birthday class should only retrieve a list of ID's by date order and that way would determine
// which ID's to call in instances of Contact object.

 
class Birthday {
	
	// DECLARE OPTION VARIABLES
	var $bdayInterval;
	var $bdayDisplay;
	
	// CONSTRUCTOR FUNCTION
	function Birthday() {
		
	}
	
/*
	// SET BIRTHDAY INTERVAL
	// If this file is included in list.php, $options->bdayInterval will be set according to the options.
	// If this file is accessed separately, we will set $options->bdayInterval to 365 days.
	if (!$options->bdayInterval) {
		$options->bdayInterval = 365;
	}
*/

	// RETRIEVE BIRTHDAY LIST
	$bdaysql = "SELECT id, CONCAT(firstname,' ',lastname) AS fullname, 
					   DATE_FORMAT(birthday, '%M %e, %Y') AS birthday, 
                       MONTHNAME(birthday) AS month,
                       DAYOFMONTH(birthday) AS day,
                       YEAR(birthday) AS year,
					   (YEAR(NOW()) - YEAR(birthday) + (RIGHT(CURRENT_DATE,5)>RIGHT(birthday,5))) AS age, 
				       (TO_DAYS((birthday + INTERVAL (YEAR(CURRENT_DATE)-YEAR(birthday) + (RIGHT(CURRENT_DATE,5)>RIGHT(birthday,5))) YEAR)) - TO_DAYS(CURRENT_DATE)) as daysAway 
				FROM " . TABLE_CONTACT . " AS contact 
				WHERE birthday != ''
					AND (TO_DAYS((birthday + INTERVAL (YEAR(CURRENT_DATE)-YEAR(birthday) + (RIGHT(CURRENT_DATE,5)>RIGHT(birthday,5)) ) YEAR)) - TO_DAYS(CURRENT_DATE)) < $options->bdayInterval 
					AND contact.hidden != 1
				ORDER BY daysAway ASC, age DESC"; 
	$r_bday = mysql_query($bdaysql, $db_link); 

	// DISPLAY THE LIST
    echo("                <TABLE WIDTH=\"100%\" BORDER=0 CELLPADDING=0 CELLSPACING=0>\n");
    echo("                  <TR><TD CLASS=\"headText\" COLSPAN=3>Upcoming Birthdays (Next $options->bdayInterval Days)</TD></TR>\n");

    while ($tbl_birthday = mysql_fetch_array($r_bday)) {
        $birthday_id = $tbl_birthday['id'];
        $birthday_fullname = stripslashes($tbl_birthday['fullname']);
        //$birthday_birthday = $tbl_birthday['birthday'];
		$birthday_month = $tbl_birthday['month'];
		$birthday_day = $tbl_birthday['day'];
		$birthday_year = $tbl_birthday['year'];
        $birthday_age = $tbl_birthday['age'];

        if ($options->displayAsPopup == 1) {
            $popupLink = " onClick=\"window.open('" . FILE_ADDRESS . "?id=$birthday_id','addressWindow','width=600,height=450,scrollbars,resizable,menubar,status'); return false;\"";
        }

        echo("                  <TR>\n");
        echo("                    <TD CLASS=\"listEntry\"><A HREF=\"" . FILE_ADDRESS . "?id=$birthday_id\"$popupLink>$birthday_fullname</A></TD>\n");
        echo("                    <TD CLASS=\"listEntry\">$birthday_month $birthday_day");
		if ($birthday_year > 0) {
			echo(", $birthday_year");
		}
		echo("</TD>\n");
		if ($birthday_year > 0) {
        	echo("                    <TD CLASS=\"listEntry\">$birthday_age yrs</TD>\n");
		}
		else {
        	echo("                    <TD CLASS=\"listEntry\">&nbsp;</TD>\n");
		}
        echo("                  </TR>\n");
    }
    echo("                </TABLE>\n");


/*
MONTH DIVIDERS...
	$thismonth = date("m"); 
	echo(" <TR>\n"); 
	echo(" <TD COLSPAN=3 CLASS=\"monthHeader\">".date("F")."</TD>\n"); 
	echo(" </TR>\n"); 

	while ($tbl_birthday = mysql_fetch_array($r_bday)) { 
        $birthday_id = $tbl_birthday['id'];
        $birthday_fullname = $tbl_birthday['fullname'];
        $birthday_birthday = $tbl_birthday['birthday'];
        $birthday_age = $tbl_birthday['age'];

		// Add month dividers 
		$birthday_month = date("m", strtotime($birthday_birthday)); 
		if ($thismonth != $birthday_month) { 
			echo(" <TR>\n"); 
			echo(" <TD COLSPAN=3 CLASS=\"monthHeader\">".date("F", strtotime($birthday_birthday))."</TD>\n"); 
			echo(" </TR>\n"); 
			$thismonth = $birthday_month; 
		} 


		echo(" <TR>\n"); 
		echo(" <TD CLASS=\"listEntry\"><A HREF=\"" . FILE_ADDRESS . "?id=$birthday_id\">$birthday_fullname</A></TD>\n"); 
		if (!($t_year > 0)) { 
			$birthday_birthday = date("M-d", strtotime($birthday_birthday)); 
		} 
		echo(" <TD CLASS=\"listEntry\">$birthday_birthday</TD>\n"); 
		if ($t_age > 0) { 
			echo(" <TD CLASS=\"listEntry\">$birthday_age yrs</TD>\n"); 
		} 
		echo(" </TR>\n"); 
	} 

	echo(" </TABLE>\n"); 
*/


		
	
// END Birthday
}

/*
	<div id="birthday">
		<div class="databoxHead"><p>Upcoming Birthdays (Next 20 Days)</p></div>
		<div class="birthdayRow">
			<div style="width:120px;" class="listCell"><a href="address.php?id=186">Lisa Simpson</a></div>
			<div style="width:120px;" class="listCell">June 21, 1990</div>
			<div style="width:35px;" class="listCell">8 yrs</div>
		</div>
		<div class="birthdayRow">
			<div style="width:120px;" class="listCell"><a href="address.php?id=63">Bart Simpson</a></div>
			<div style="width:120px;" class="listCell">June 23, 1988</div>
			<div style="width:35px;" class="listCell">10 yrs</div>
		</div>
		<div class="birthdayRow">
			<div style="width:120px;" class="listCell"><a href="address.php?id=140">Maggie Simpson</a></div>
			<div style="width:120px;" class="listCell">June 27, 1996</div>
			<div style="width:35px;" class="listCell">2 yrs</div>
		</div>
		<div class="birthdayRow">
			<div style="width:120px;" class="listCell"><a href="address.php?id=140">Maggie Simpson</a></div>
			<div style="width:120px;" class="listCell">June 27, 1996</div>
			<div style="width:35px;" class="listCell">2 yrs</div>
		</div>
	</div>
	*/
?>