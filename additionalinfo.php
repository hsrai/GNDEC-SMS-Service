<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *   
 ****************************************************************
 *  users.php
 *  Manages users of the Address Book.
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_LIB_MAIL);	
	require_once(FILE_CLASS_OPTIONS);
	session_start();

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);
	

// ** CHECK FOR LOGIN **
	checkForLogin("admin","user");
	$nuser = $_POST['newuserName'];
	$nnature = $_POST['newuserNature'];
					
	
// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
switch($_GET['action']) {
	case "addnew":
					$newuserName = $_POST['newuserName'];
					$newuserPass = $_POST['newuserPass'];
					$newuserFullName = $_POST['newuserFullName'];
					$newuserMobile = $_POST['newuserMobile'];
					$newuserType = $_POST['newuserType'];
					$newuserNature = $_POST['newuserNature'];
					$newuserEmail = $_POST['newuserEmail'];   // NOT VALIDATED
					$sql = "INSERT INTO ". TABLE_USERS ." (fullname, username, usertype, nature, password, email, mobile, is_confirmed) VALUES ('$newuserFullName','$newuserName', '$newuserType', '$newuserNature', MD5('$newuserPass'), '$newuserEmail','$newuserMobile', 1)";
					mysql_query($sql, $db_link);
					if($opps ==1062) {
						$actionMsg = $lang['ERR_USERNAME_DUPL'];
						break;
					}elseif ($opps != 0){
						die(ReportSQLError($sql));
					}	
					$actionMsg =  $newuserName.' '.$lang['USR_ADDED'];
				}
				else {
					
					$actionMsg = $lang['ERR_USER_PASSWORD_SHORT'];
				}
			}
			else {
				$actionMsg = $lang['ERR_USERNAME_ILLEGAL_CHARS'];
			}
		
		break;
					
		
	case "addition":
					$nnuser = $_POST['nnnuser'];
					$newuserDepartment = $_POST['newuserDepartment'];
					$newuserBatch = $_POST['newuserBatch'];
					$newuserDesignation = $_POST['newuserDesignation'];
					echo $newuserFullName;
					$sql = "UPDATE ". TABLE_USERS ." SET department='".$newuserDepartment."', batch='".$newuserBatch."', designation = '".$newuserDesignation."' WHERE username = '".$nnuser."'";
					mysql_query($sql, $db_link);
					echo "New Account Added";
					echo $newuserEmail;
					echo $newuserType;
					
					break;
	default:
	break;
}
	
?>





<HTML>
<HEAD>
	<TITLE><?php echo $lang['TITLE_TAB']." - ".$lang['LBL_USR_ACCT_SET']  ?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['CHARSET']?>">	
	</HEAD>
<BODY>
<?php 
if ($nnature=="teacher")
{
?>
<CENTER>

<TABLE>
<FORM NAME="additionalinfo" ACTION="additionalinfo.php?action=addition" METHOD="post">
<TR VALIGN="top" >
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Department</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              			<INPUT TYPE="hidden" name ="nnnuser" value="<?php echo $nuser; ?>" >
	              				<SELECT NAME="newuserDepartment" CLASS="formSelect">
	              				<OPTION VALUE="it" SELECTED>Information Technology</OPTION>
	              				<OPTION VALUE="cse">Computer Science</OPTION>
	              				<OPTION VALUE="ece">Electronics And Commumication</OPTION>
	              				<OPTION VALUE="ee">Electrical</OPTION>
	              				<OPTION VALUE="pe">Production</OPTION>
	              				<OPTION VALUE="me">Mechanical</OPTION>
	              				<OPTION VALUE="ce">Civil</OPTION>
	              				<OPTION VALUE="mca">MCA</OPTION>
	              				<OPTION VALUE="mba">MBA</OPTION>
							</SELECT></TD></TR>
							<TR VALIGN="top" >
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Designation</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              				<SELECT NAME="newuserDesignation" CLASS="formSelect">
	              				<OPTION VALUE="hod" SELECTED>Head Of Department</OPTION>
	              				<OPTION VALUE="assistantprofessor">Assistant Professor</OPTION>
	              			   <OPTION VALUE="lecturer">Lecturer</OPTION>
	              			   	              				
							</SELECT></TD></TR>	
							<?php
						}
							elseif($nnature=="student")
							{
							?>
							<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Department</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              			<INPUT TYPE="hidden" name ="nnnuser" value="<?php echo $nuser; ?>" >
	              				<SELECT NAME="newuserDepartment" CLASS="formSelect">
	              				<OPTION VALUE="it" SELECTED>Information Technology</OPTION>
	              				<OPTION VALUE="cse">Computer Science</OPTION>
	              				<OPTION VALUE="ece">Electronics And Commumication</OPTION>
	              				<OPTION VALUE="ee">Electrical</OPTION>
	              				<OPTION VALUE="pe">Production</OPTION>
	              				<OPTION VALUE="me">Mechanical</OPTION>
	              				<OPTION VALUE="ce">Civil</OPTION>
	              				<OPTION VALUE="mca">MCA</OPTION>
	              				<OPTION VALUE="mba">MBA</OPTION>
							</SELECT></TD></TR>
							
							<TR VALIGN="top">
						<TD WIDTH=100 CLASS="data" STYLE="text-align:right"><B>Batch</B></TD>
	              			<TD WIDTH=150 CLASS="data">
	              				<SELECT NAME="newuserBatch" CLASS="formSelect">
	              				<OPTION VALUE="2007-2011" SELECTED>2007-2011</OPTION>
	              				<OPTION VALUE="2008-2012">2008-2012</OPTION>
	              				<OPTION VALUE="2009-2013">2009-2013</OPTION>
	              				<OPTION VALUE="2010-2014">2010-2014</OPTION>
	              				<OPTION VALUE="2011-2015">2011-2015</OPTION>
	              				<OPTION VALUE="2012-2016">2012-2016</OPTION>
	              				<OPTION VALUE="2013-2017">2013-2017</OPTION>
	              				<OPTION VALUE="2014-2018">2014-2018</OPTION>
	              				<OPTION VALUE="2015-2019">2015-2019</OPTION>
							</SELECT></TD></TR>
	              			<?php
	              			}
							
	              			?>
	              		<INPUT TYPE="submit" CLASS="formButton" NAME="addUser" VALUE="<?php echo $lang['BTN_ADD']?>">
	              			</FORM>
	              			
							
							</TABLE></CENTER>
</BODY>
</HTML>
