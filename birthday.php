<?php 
/************************************************************* 
 *  THE ADDRESS BOOK  :  version 1.04
 * 
****************************************************************
 *  birthday.php 
 *  Lists upcoming birthdays 
 * 
 *************************************************************/ 

// This file is called within list.php to display birthdays.
// It may also be called independently for more options.

// ** GET CONFIGURATION DATA **
    require_once('constants.inc');
    require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASSES);

// ** OPEN CONNECTION TO THE DATABASE **
    $db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin();


// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();
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
    echo("                  <TR><TD CLASS=\"headText\" COLSPAN=3>". $lang[BIRTHDAY_UPCOMING1] ." $options->bdayInterval ". $lang[BIRTHDAY_UPCOMING2]."</TD></TR>\n");

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



?>
