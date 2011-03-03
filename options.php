<?php
/*************************************************************
 *	THE ADDRESS BOOK  :  version 1.04d
 *	  
 *****************************************************************
 *	options.php
 *	Sets options for address book.
 *
 *************************************************************/
// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin("admin");
	
// ** GET OPTIONS
	$options = new Options();
	
// CHECK TO SEE IF A FORM HAS BEEN SUBMITTED, AND SAVE THE OPTIONS.
	if ($_POST['saveOpt'] == "YES") {
		$options->save_global();
	}
	$options->set_global(); // This page does not yet have separate areas for admin and user settings, so we must reset all options to admin only.

	
?>

<HTML>
<HEAD>
	<TITLE><?php echo $lang['TITLE_TAB']." - ".$lang['OPT_TITLE']?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	
</HEAD>
<BODY>
<SCRIPT LANGUAGE="JavaScript">
<!--
function saveEntry() {
	document.Options.submit();
}
// -->
</SCRIPT>

<FORM NAME="Options" ACTION="<?php echo(FILE_OPTIONS); ?>" METHOD="post">
<INPUT TYPE="hidden" NAME="saveOpt" VALUE="YES">
<CENTER>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0 WIDTH=570>
<TR align="right"><TD ><b><A HREF="<?php echo(FILE_LIST); ?>"><?php echo $lang['BTN_RETURN']?></b></A></TD> </TR>
<TBODY>
	<TR><TD CLASS="headTitle"><?php echo $lang['OPT_TITLE']?></TD></TR>
	<TR>
		<TD CLASS="infoBox">
		<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=5 WIDTH=560>
		<TBODY>
			<TR VALIGN="top"><TD COLSPAN=3 CLASS="data">
<?php
		echo("<P STYLE=\"color: #FF0000\">$options->message</P>\n");
?>
			</TD></TR>

			<TR VALIGN="top">
				<TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['OPT_HEADER_MESSAGES']?></TD>
			</TR>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MSG_LOGIN_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<TEXTAREA STYLE="width:300px;" ROWS=4 CLASS="formTextarea" NAME="msgLogin" WRAP=off><?php echo($options->msgLogin); ?></TEXTAREA>
					<BR><?php echo $lang['OPT_MSG_LOGIN_HELP']?>
					<BR><B><?php echo $lang['OPT_MSG_ALLOWED_HTML']?>
				</TD>
			</TR>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MSG_WELCOME_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<INPUT TYPE="text" SIZE=20 STYLE="width:300px;" CLASS="formTextbox" NAME="msgWelcome" VALUE="<?php echo($options->msgWelcome); ?>" MAXLENGTH=255>
					<BR><?php echo $lang['OPT_MSG_WELCOME_HELP']?>.
					<BR><B><?php echo $lang['OPT_MSG_ALLOWED_HTML']?>
				</TD>
			</TR>


			<TR VALIGN="top">
				<TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['OPT_HEADER_BIRTHDAY']?></TD>
			</TR>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_BIRTHDAY_DISPLAY_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->bdayDisplay == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"bdayDisplay\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_BIRTHDAY_DISPLAY_HELP']?><br><b>
					<?php echo $lang['LBL_DEFAULT']?>:</B> </b>ON
				</TD>
			</TR>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_BIRTHDAY_DAYS_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><INPUT TYPE="text" SIZE=3 STYLE="width:30px;" CLASS="formTextbox" NAME="bdayInterval" VALUE="<?php echo($options->bdayInterval); ?>" MAXLENGTH=3></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_BIRTHDAY_DAYS_HELP']?><br><b>
					<?php echo $lang['LBL_DEFAULT']?>:</B> </b> 21 days
			</TR>


			<TR VALIGN="top">
				<TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['OPT_HEADER_MUGSHOT']?></TD>
			</TR>

			<?php /* $picAlwaysDisplay */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MUG_DISPLAY_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->picAlwaysDisplay == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"picAlwaysDisplay\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_MUG_DISPLAY_HELP']?>
				</TD>
			</TR>

			<?php /* $options->picWidth */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MUG_WIDTH_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><INPUT TYPE="text" SIZE=3 STYLE="width:30px;" CLASS="formTextbox" NAME="picWidth" VALUE="<?php echo($options->picWidth); ?>" MAXLENGTH=3></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_MUG_WIDTH_HELP']?>
					<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> 140 pixels.
				</TD>
			</TR>

			<?php /* $options->picHeight */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MUG_HEIGHT_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><INPUT TYPE="text" SIZE=3 STYLE="width:30px;" CLASS="formTextbox" NAME="picHeight" VALUE="<?php echo($options->picHeight); ?>" MAXLENGTH=3></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_MUG_HEIGHT_HELP']?>
					<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> 140 pixels.
				</TD>
			</TR>

			<?php /* $options->picDupeMode */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MUG_DUPLICATE_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<?php echo $lang['OPT_MUG_DUPLICATE_HELP']?>
					<BR><INPUT TYPE="radio" NAME="picDupeMode" VALUE="1"<?php if ($options->picDupeMode == 1) { echo (" CHECKED"); }?>> <?php echo $lang['OPT_MUG_DUPE_CHOICE_OVERWRITE']?>
					<BR><INPUT TYPE="radio" NAME="picDupeMode" VALUE="2"<?php if ($options->picDupeMode == 2) { echo (" CHECKED"); }?>> <?php echo $lang['OPT_MUG_DUPE_CHOICE_UPLOAD']?>
					<BR><INPUT TYPE="radio" NAME="picDupeMode" VALUE="3"<?php if ($options->picDupeMode == 3) { echo (" CHECKED"); }?>> <?php echo $lang['OPT_MUG_DUPE_CHOICE_NO']?>
				</TD>
			</TR>

			<?php /* $picAllowUpload */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_MUG_ALLOW_UPLOAD_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->picAllowUpload == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"picAllowUpload\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_MUG_ALLOW_UPLOAD_HELP']?>
					<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> ON.
				</TD>
			</TR>


			<TR VALIGN="top">
				<TD WIDTH=560 COLSPAN=3 CLASS="listHeader"><?php echo $lang['OPT_HEADER_MISC']?></TD>
			</TR>
			<?php /* $options->displayAsPopup */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_OPEN_POPUP_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->displayAsPopup == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"displayAsPopup\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_OPEN_POPUP_HELP']?>
				</TD>
			</TR>

			<?php /* $useMailScript */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_USE_MAIL_SCRIPT_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->useMailScript == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"useMailScript\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_USE_MAIL_SCRIPT_HELP']?>
				</TD>
			</TR>

			<?php /* $countryDefault */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_DEFAULT_COUNTRY_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>

					<SELECT NAME="countryDefault" CLASS="formSelect" STYLE="width:160px;">
<?php
	// ********** $country array is loaded with the language include file *******
		foreach ($country as $country_id=>$val)
		$sortarray[$country_id]= strtr($val,"¿¡¬√ƒ≈»… ÄÀÃÕŒœ—“”‘’÷Ÿ⁄€‹›‡·‚„‰ÂËÈÍÎÏÌÓÔÒÚÛÙıˆ˘˙˚¸˝ˇ", "AAAAAAAEEEEIIIINOOOOOUUUUYaaaaaaeeeeiiiinooooouuuuyy");
		# the above line ensures that special characters in some languages do not sort strangely by replacing them in a sort array, sorting same, then reading our original values in sort array order
		asort($sortarray);
		foreach(array_keys($sortarray) as $country_id) {
		echo("<option value=$country_id");
		if ($country_id == $address_country) {
			echo(" selected");
		}
		elseif ($country_id == $options->countryDefault) {
			echo(" selected");
		}
		echo ">";	
		echo($country[$country_id].'</option>\n');
}
?>
					</SELECT>
					<BR><?php echo $lang['OPT_DEFAULT_COUNTRY_HELP']?>
					<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> <?php echo $country[0] //blank?>
				</TD>
			</TR>

			<?php /* $options->allowUserReg */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_ALLOW_REGISTER_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->allowUserReg == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"allowUserReg\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_ALLOW_REGISTER_HELP']?>
				</TD>
			</TR>
			<?php /* $options->eMailAdmin */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_EMAIL_ADMIN']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->eMailAdmin == 1) {
						$check = " CHECKED";
					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"eMailAdmin\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_EMAIL_ADMIN_HELP']?>
				</TD>
			</TR>

			<?php /* $options->requireLogin */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_REQUIRE_LOGIN_LBL']?></B></TD>
				<TD WIDTH=60 CLASS="data"><?php
					if ($options->requireLogin == 1) {
						$check = " CHECKED";

					}
					echo("<INPUT TYPE=\"checkbox\" NAME=\"requireLogin\" VALUE=\"1\"$check>");
					$check = "";
				?></TD>
				<TD WIDTH=300 CLASS="data">
					<?php echo $lang['OPT_REQUIRE_LOGIN_HELP']?>
					<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> ON.
				</TD>
			</TR>


			
			<?php /* $language */ ?>
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_LANGUAGE_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
				<SELECT NAME="language" CLASS="formSelect" STYLE="width:160px;">
<?php
	// ** LANGUAGE DROP DOWN GENERATION
	// Obtain the list of language modules from the 'languages' directory.
	$dh = opendir("languages") or die ("Open Directory failed"); 
	while (false !== ($filename = readdir($dh))) { 
		if ($filename == "." OR $filename == "..") continue;
		$files[] = $filename; 
	} 
	sort($files); 
	closedir($dh);

	// Generate the selections
	// This may not necessary be the quickest way to do it, but it works.
	for ($i = 0; $i < count($files); $i++) { 
		// Files will be parsed to obtain the value of LANGUAGE_NAME.
		// If the language name cannot be found, then it must be a faulty module (or not a module at all!) -- and it will not be displayed in the drop down list.
		$languagename = implode(" ", file("languages/" . $files[$i]));
  		$languagename = explode("LANGUAGE_NAME', \"", $languagename, 2);  // Find the variable name and the first set of double quotes
   		$languagename = explode("\"", $languagename[1], 2);              // Find the second set of double quotes
		// The result should be the name of the language. If nothing is found, display no option.
		if ($languagename[0] != "") {
			// value used is the filename minus extension.
			$filename = (explode(".", $files[$i]));
    		echo("<option value=\"" . $filename[0] . "\"" . (($filename[0] == $options->language) ? " selected" : "") . ">" . $languagename[0] . "</option>\n");
		}
	}
     ?>
</SELECT> 
					<BR><?php echo $lang['OPT_LANGUAGE_HELP']?>
					<BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> english
				</TD>
			</TR>

		<!--HDK: view by letter option-->
		<?php /* $defaultLetter */ 
			$abc=array(A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z);
		?>
		
			<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_VIEW_LTR_LABEL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<SELECT NAME="defaultLetter" CLASS="formSelect" STYLE="width:160px;">	
							<OPTION VALUE="0">(off)</OPTION>
<?php
		
	foreach ($abc as $letter){
		echo("						<OPTION VALUE=\"$letter\"");
		if ($letter == $options->defaultLetter) {
			echo(" SELECTED");
		}
		echo(">$letter</OPTION>\n");
	}
?>
</SELECT>
					<?php echo $lang['OPT_VIEW_LTR_HELP']?>
				</TD>
			</TR>
			<!-- //HDK end change-->
		<!--HDK: limit entries per page -->
		<?php /* $limitEntries */?>
		<!--		<TR VALIGN="top">
				<TD WIDTH=200 CLASS="data" ALIGN="right"><B><?php echo $lang['OPT_LIMIT_ENTRIES_LBL']?></B></TD>
				<TD WIDTH=360 CLASS="data" COLSPAN=2>
					<INPUT TYPE="text" NAME="limitEntries" VALUE="<?php echo $options->limitEntries ?>"
					<?php echo $lang['OPT_LIMIT_ENTRIES_HELP']?>
				</TD>
			</TR>-->
			<!-- //HDK end change-->	
			<?php /* $modifyTime */ ?>
<!-- DISABLED

		   <TR VALIGN="top">
			  <TD WIDTH=200 CLASS="data" ALIGN="right">
				   <B>Last Update Time</B>
			  </TD>
			  <TD WIDTH=60 CLASS="data">

				   <SELECT SIZE=3 NAME="modifyTime">
					   <OPTION VALUE="-23">-23</OPTION>
					   <OPTION VALUE="-22">-22</OPTION>
					   <OPTION VALUE="-21">-21</OPTION>
					   <OPTION VALUE="-20">-20</OPTION>
					   <OPTION VALUE="-19">-19</OPTION>
					   <OPTION VALUE="-18">-18</OPTION>
					   <OPTION VALUE="-17">-17</OPTION>
					   <OPTION VALUE="-16">-16</OPTION>
					   <OPTION VALUE="-15">-15</OPTION>
					   <OPTION VALUE="-14">-14</OPTION>
					   <OPTION VALUE="-13">-13</OPTION>
					   <OPTION VALUE="-12">-12</OPTION>
					   <OPTION VALUE="-11">-11</OPTION>
					   <OPTION VALUE="-10">-10</OPTION>
					   <OPTION VALUE="-9">-9</OPTION>
					   <OPTION VALUE="-8">-8</OPTION>
					   <OPTION VALUE="-7">-7</OPTION>
					   <OPTION VALUE="-6">-6</OPTION>
					   <OPTION VALUE="-5">-5</OPTION>
					   <OPTION VALUE="-4">-4</OPTION>
					   <OPTION VALUE="-3">-3</OPTION>
					   <OPTION VALUE="-2">-2</OPTION>
					   <OPTION VALUE="-1">-1</OPTION>
					   <OPTION VALUE="0">0</OPTION>
				   </SELECT>
				   <INPUT TYPE="text" SIZE=3 STYLE="width:30px;" CLASS="formTextbox" NAME="modifyTime" VALUE="<?php echo($modifyTime); ?>" MAXLENGTH=3>
			  </TD>
			  <TD WIDTH=300 CLASS="data">
				   This changes the time "Last Updated" time based on your time zone offset.
				   <BR><B><?php echo $lang['LBL_DEFAULT']?>:</B> 0 hours.
			  </TD>
		   </TR>
// -->
			<TR VALIGN="top"><TD WIDTH=560 COLSPAN=3 CLASS="listDivide">&nbsp;</TD></TR>
			<TR VALIGN="top">
				<TD WIDTH=560 COLSPAN=3 CLASS="navmenu">
					<NOSCRIPT>
					<!-- Will display Form Submit buttons for browsers without Javascript -->
					<!-- is there even such a thing anymore?? -->
					<INPUT TYPE="submit" VALUE="Save">
					</NOSCRIPT>
					<A HREF="#" onClick="saveEntry(); return false;"><?php echo $lang['BTN_SAVE']?></A>
					<A HREF="<?php echo(FILE_LIST); ?>"><?php echo $lang['BTN_RETURN']?></A>
				</TD>
			</TR>
		</TBODY>
		</TABLE>
		</TD>
	</TR>
</TBODY>
</TABLE>
</CENTER>
</FORM>
</BODY>
</HTML>
