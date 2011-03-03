<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04
 *  
 *  cell.php
 *  Displays other phone numbers. Can generate a useful list
 *  of cell phone numbers. A hastily-coded feature for personal
 *  use; may not be included in future versions, or will be
 *  integrated in a more streamlined manner.
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
    require_once('constants.inc');
    require_once(FILE_FUNCTIONS);

// ** OPEN CONNECTION TO THE DATABASE **
    $db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin("admin","user");


// ** RETRIEVE INFORMATION **
    $sql = "SELECT DISTINCT contact.id, otherphone.id, CONCAT(lastname,', ',firstname) AS fullname
                FROM ".TABLE_CONTACT." as contact, ".TABLE_OTHERPHONE." as otherphone
                WHERE contact.id=otherphone.id
                ORDER BY fullname";
    $r_contact = mysql_query($sql, $db_link)
		or exit(ReportSQLError());

?>
<HTML>
<HEAD>
	<TITLE>Address Book - Other Phone Numbers</TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
</HEAD>

<BODY>


<P>
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR><TD CLASS="headTitle"><B>Secondary Phone Information</B></TD></TR>

  <TR>
    <TD CLASS="infoBox">

           <BR>
              <CENTER>
              <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=560>
<?php

    while ($tbl_contact = mysql_fetch_array($r_contact)) {

        $contact_fullname = $tbl_contact['fullname'];
        $contact_id = $tbl_contact['id'];


        echo("                 <TR VALIGN=\"top\">\n");
        // Name -- Display links either as regular link or popup window
        if ($displayAsPopup == 1) {
            $popupLink = " onClick=\"window.open('" . FILE_ADDRESS . "?id=$id','addressWindow','width=600,height=450,scrollbars,resizable,location,menubar,status'); return false;\"";
        }
            echo("                   <TD WIDTH=150 CLASS=\"listEntry\"><B><A HREF=\"" . FILE_ADDRESS . "?id=$contact_id\"$popupLink>$contact_fullname</A></B></TD>\n");
        echo("                   <TD WIDTH=150 CLASS=\"listEntry\">");
            $r_otherPhone = mysql_query("SELECT * FROM ".TABLE_OTHERPHONE." AS otherphone WHERE id=$contact_id", $db_link);
            $tbl_otherPhone = mysql_fetch_array($r_otherPhone);
                $phone_number = $tbl_otherPhone['phone'];
                $phone_type = $tbl_otherPhone['type'];
                echo("$phone_number ($phone_type)");
            while ($tbl_otherPhone = mysql_fetch_array($r_otherPhone)) {
                $phone_number = $tbl_otherPhone['phone'];
                $phone_type = $tbl_otherPhone['type'];
                echo("<BR>$phone_number ($phone_type)");
            }
            echo("&nbsp;</TD>\n");
        echo("                 </TR>\n");

    // end while
    }

?>
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