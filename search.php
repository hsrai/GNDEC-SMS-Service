<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *   
 *  CHANGE LOG since 1.04 release on 30 May 05 where + sign indicates if this particular file was modified
 *			1.04a - Corrected <? to <?php for all instances due to some servers having short_open_tags set to off. 
 *			1.04b - Corrected 3 more cases of <? which were missed earlier. Only affects index.php
 *			1.04c - Corrected mailsend.php syntax error (extraneous open bracket [).
 *			1.04d - Corrected Javascript error in mailto.php
 *					Corrected improper default country select in edit.php
 *					Removed use of file_get_contents() from options.php and users.php due to pre php4.3 incompatibility
 *		+			Changed call to charset iso-8859-1 from hard code in header to variable to accomodate greek and other non- 8859-1 languages
 ****************************************************************  
 *  search.php
 *  Searches address book entries. 
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
    require_once('constants.inc');
    require_once(FILE_FUNCTIONS);
    require_once(FILE_CLASS_OPTIONS);
    session_start();
    $username = $_SESSION['username'];
    //echo $username;

// ** OPEN CONNECTION TO THE DATABASE **
    $db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin();

// RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE
	$options = new Options();

    $options = mysql_fetch_array(mysql_query("SELECT displayAsPopup FROM " . TABLE_OPTIONS . " LIMIT 1", $db_link))
		or die(reportScriptError("Unable to retrieve options."));
    $options->displayAsPopup = $options['displayAsPopup'];


// PHP code is placed BEFORE sending any HTML information because we want the script to 
// stop processing and send another file instead if a single entry is found.
// Because we don't rely on the browser to redirect, this allows pressing 'back' on the
// browser to take us back to the list, and not keep forwarding.


// See if search terms have been passed to this page.
	$goTo = $_POST['goTo'];
    if (!$goTo AND !$search) {
        echo("<P>".$lang['SEARCH_TERMS']);
        exit();
    }

// goTo functionality
// Search does not work so we'll make it do the same thing as goTo for now.
    if ($search) {
        $goTo = $search;
    }
    if ($goTo) {
        $gotosql = "SELECT id, CONCAT(lastname,', ',firstname) AS fullname, lastname, firstname
                   FROM " . TABLE_CONTACT . "
                   WHERE
                     CONCAT(firstname,' ', lastname) LIKE '%$goTo%' OR
                     CONCAT(firstname,' ', middlename,' ', lastname) LIKE '%$goTo%' OR
                     nickname LIKE '%$goTo%' 
                   ORDER BY fullname";
        $r_goto = mysql_query($gotosql, $db_link);
        $numGoTo = mysql_num_rows($r_goto);

    }

// print results
    if ($numGoTo == 1) {
        $t_goto = mysql_fetch_array($r_goto); 
        $contact_id = $t_goto['id'];
		if ($options->displayAsPopup == 1) { 
        	$theAddress = FILE_ADDRESS."?id=".$contact_id; 
?>

<HTML>
<HEAD>
	<TITLE> <?php  echo "$lang[TITLE_TAB] - $lang[SEARCH_LBL]" ?></TITLE>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<SCRIPT LANGUAGE="JavaScript">
	<!--
	window.open('<?php echo($theAddress); ?>',null,'width=600,height=450,scrollbars,resizable,menubar,status'); history.back();
	// -->
	</SCRIPT>
</HEAD>
<BODY>

<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="headTitle"><?php echo $lang['SEARCH_RESULTS']?></TD>
  </TR>
  <TR>
    <TD CLASS="infoBox">

      <TABLE BORDER=0 CELLPADDING=10 CELLSPACING=0 WIDTH=500><TR VALIGN="top"><TD CLASS="data">
One entry found. It will appear in a new window. If no window appears, <A HREF="#" onClick="window.open('<?php echo($theAddress); ?>',addressWindow,'width=600,height=450,scrollbars,resizable,menubar,status'); return false;">click here</A>.
      </TD></TR></TABLE>

    </TD>
  </TR>
</TABLE>
</CENTER>


</BODY>
</HTML>
<?php
      	}         
		else {
	        header("Location: " . FILE_ADDRESS . "?id=$contact_id"); 
		}
        exit;
    }

?>
<HTML>
<HEAD>
	<TITLE> <?php  echo "$lang[TITLE_TAB] - $lang[SEARCH_LBL]" ?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">
</HEAD>
<BODY>

<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="headTitle"><?php echo $lang['SEARCH_LBL'] ?></TD>
  </TR>
  <TR>
    <TD CLASS="infoBox">

      <TABLE BORDER=0 CELLPADDING=10 CELLSPACING=0 WIDTH=500><TR VALIGN="top"><TD CLASS="data">
<?php

    // print search info
    echo("<B>".$lang['SEARCH_MATCH']. " $goTo ".$lang['SEARCH_IN_NAME']."</B>");

    // print results in case $numGoTo did not equal 1
    if ($numGoTo == 0) {
        echo("<P>".$lang['SEARCH_NONE']);
        echo("<P><B><A HREF=\"" . FILE_LIST . "\">".$lang['BTN_RETURN']."</A></B>");
    }
    else {
        echo("<P>".$lang['SEARCH_MULTIPLE']."<P>");
        while ($t_goto = mysql_fetch_array($r_goto) ) {
            $contact_id = $t_goto['id'];
            $contact_name = $t_goto['fullname'];
            $contact_firstname = $t_goto['firstname'];
            $contact_lastname = $t_goto['lastname'];
			if (!$contact_firstname) { $contact_name = $contact_lastname; }

	        if ($options->displayAsPopup == 1) {
	            $popupLink = " onClick=\"window.open('" . FILE_ADDRESS . "?id=$contact_id','addressWindow','width=600,height=450,scrollbars,resizable,menubar,status'); return false;\"";
	        }

            echo("<BR><A HREF=\"" . FILE_ADDRESS . "?id=$contact_id\"$popupLink>$contact_name</A>\n");
        }
        echo("<P><B><A HREF=\"" . FILE_LIST. "\">".$lang['BTN_RETURN']."</A></B>");
    }

?>
      </TD></TR></TABLE>

    </TD>
  </TR>
</TABLE>
</CENTER>




</BODY>
</HTML>
