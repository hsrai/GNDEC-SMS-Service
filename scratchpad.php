<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04
 *  
 *  scratchpad.php
 *  Temporary placeholder for notes and such.
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
    require_once('constants.inc');
    require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
    $db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();

// ** CHECK FOR LOGIN **
	checkForLogin();

?>
<HTML>
<HEAD>
  <TITLE><?php echo "$lang[TITLE_TAB] - $lang[TITLE_SCRATCH]"?></TITLE>
  <LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">
  <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="EXPIRES" CONTENT="-1">
</HEAD>

<BODY>

<?php
// CHECK TO SEE IF A FORM HAS BEEN SUBMITTED, AND SAVE THE SCRATCHPAD.
    if ($_POST['saveNotes'] == "YES") {

	    $notes = addslashes( trim($_POST['notes']) );
	    
        // UPDATES THE SCRATCHPAD TABLE
        $sql = "UPDATE ". TABLE_SCRATCHPAD ." SET notes='$notes'";

        $update = mysql_query($sql, $db_link)
			or die(reportSQLError($sql));

        echo($lang[SCRATCH_SAVED]."\n");
/*
        echo("<P><A HREF=\"" . FILE_LIST . "\"><B>Return to List</B></A>\n");
        echo("</BODY>");
        echo("</HTML>");
        exit();
*/
    }
    
    
?>


<SCRIPT LANGUAGE="JavaScript">
<!--

function saveEntry() {
  //CONFIRMATION DISABLED.
  //if (confirm('Are you sure you want to save?\nChanges cannot be undone.')) {
    document.Scratchpad.submit();
  //}
}

// -->
</SCRIPT>


<FORM NAME="Scratchpad" ACTION="<?php echo(FILE_SCRATCHPAD); ?>" METHOD="post">
<INPUT TYPE="hidden" NAME="saveNotes" VALUE="YES">

<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
  <TR>
    <TD CLASS="navMenu">
      <A HREF="#edit"><?php echo $lang[BTN_EDIT]?></A>
      <A HREF= "<?php echo FILE_LIST?>"><?php echo $lang[BTN_LIST]?></A>
    </TD>
  </TR>
  <TR>
    <TD CLASS="headTitle">
       <?php echo $lang[TITLE_SCRATCH]?>
    </TD>
  </TR>
  <TR>
    <TD CLASS="infoBox">

        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
           <TR VALIGN="top">
              <TD CLASS="data">
                 <?php echo $lang[SCRATCH_HELP]?>
              </TD>
           </TR>
           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="listDivide">&nbsp;</TD>
           </TR>
           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="data">
<?php
// DISPLAY CONTENTS OF SCRATCHPAD.

    // Retrieve data
    $notes = mysql_query("SELECT notes FROM " . TABLE_SCRATCHPAD . " LIMIT 1", $db_link);
    $notes = mysql_fetch_array($notes);
    $notes = stripslashes( $notes["notes"] );

    // Split $notes into an array by newline character
    $displayArray = explode("\n",$notes);

    // Determine the number of lines in the array
    $z = 0;
    while (each($displayArray)) {
        $z++;
    } 
    reset($displayArray);

    // Grab each line of the array and display it
    for ($a = 0; $a < $z; $a++) {
        echo("<BR>$displayArray[$a]");
    }

?>
              </TD>
           </TR>
           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="listDivide">&nbsp;</TD>
           </TR>
                      
           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="listHeader"><A NAME="edit"></A><?php echo ucfirst($lang[BTN_EDIT])?></TD>
           </TR>
           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="data">
<TEXTAREA STYLE="width:530px;" ROWS=30 CLASS="formTextarea" NAME="notes" WRAP=off>
<?php
  echo("$notes");
?>
</TEXTAREA>           
              </TD>
           </TR>

           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="listDivide">&nbsp;</TD>
           </TR>

           <TR VALIGN="top">
              <TD WIDTH=550 CLASS="navmenu">
      <NOSCRIPT>
        <!-- Will display Form Submit buttons for browsers without Javascript -->
        <INPUT TYPE="submit" VALUE="Save">
        <!-- There is no delete button -->
        <!-- later make it so link versions don't appear -->
      </NOSCRIPT>
      <A HREF="#" onClick="saveEntry(); return false;"><?php echo $lang[BTN_SAVE]?></A>
      <A HREF="<?php echo(FILE_LIST); ?>"><?php echo $lang[BTN_RETURN]?></A>
              </TD>
           </TR>


        </TABLE>

    </TD>
  </TR>
</TABLE>
</CENTER>

</FORM>


</BODY>
</HTML>
