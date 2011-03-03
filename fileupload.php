<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04e
 *  
 *
 **************************************************************
 *  fileupload.php
 *  Upload pictures for entries.
 *
 *************************************************************/

// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS); 

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin("admin","user");
// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();

// ** DENY ACCESS IF UPLOAD IS NOT ALLOWED
	if (($options->picAllowUpload != 1) && ($_SESSION['usertype'] != "admin")) {
		reportScriptError("File uploading has been turned off in this installation.");
		exit();
	}

// ** BEGIN
	require(FILE_LIB_UPLOAD);

#--------------------------------#
# Variables
#--------------------------------#

// The name of the file field in your form.
	$upload_file_name = "userfile";
	$path = "mugshots/";

// ACCEPT mode - if you only want to accept
// a certain type of file.
// possible file types that PHP recognizes includes:
//
// OPTIONS INCLUDE:
//  text/plain
//  image/gif
//  image/jpeg
//  image/png
	
	// Accept ONLY gifs's
	#$acceptable_file_types = "image/gifs";
	
	// Accept GIF and JPEG files
	$acceptable_file_types = "image/gif|image/jpeg|image/pjpeg";
	
	// Accept ALL files
	#$acceptable_file_types = "";

// If no extension is supplied, and the browser or PHP
// can not figure out what type of file it is, you can
// add a default extension - like ".jpg" or ".txt"

	$default_extension = "";

// MODE: if your are attempting to upload
// a file with the same name as another file in the
// $path directory
//
// OPTIONS:
//   1 = overwrite mode
//   2 = create new with incremental extention
//   3 = do nothing if exists, highest protection

	$mode = $options->picDupeMode;

#--------------------------------#
# BEGIN HTML HEADER
#--------------------------------#

?>
<HTML>
<HEAD>
	<TITLE><?php echo $lang['TAB'].' <-> '.$lang['LBL_UPLOAD_PICTURE'] ?></TITLE>
	<LINK REL="stylesheet" HREF="styles.css" TYPE="text/css">
	<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="-1">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo $lang['CHARSET']?>">
	<SCRIPT LANGUAGE="JavaScript">
	
	<!--
		function updateOpener() {
    		window.opener.document.forms[0].pictureURL.value = document.forms[0].pictureURL.value;
    		window.close();
		}
	//-->
	</SCRIPT>
</HEAD>
<BODY>
<?php
#--------------------------------#
# PHP
#--------------------------------#
	if (isset($_REQUEST['submitted'])) {
		$my_uploader = new uploader($lang['ThisLanguage']); 
		// OPTIONAL: set the max filesize of uploadable files in bytes
		$my_uploader->max_filesize(30000);
		
		// OPTIONAL: if you're uploading images, you can set the max pixel dimensions 
		$my_uploader->max_image_size($options->picWidth, $options->picHeight); // max_image_size($width, $height)
		
		// UPLOAD the file
		if ($my_uploader->upload($upload_file_name, $acceptable_file_types, $default_extension)) {
			$my_uploader->save_file($path, $mode);
		}
		
		// RETURN RESULTS
		if ($my_uploader->error) {
			echo $my_uploader->error . "<P>\n";
		
		} else {
			// Successful upload!
			echo("<FORM WIDTH = \"450\" NAME=\"form\"><INPUT TYPE=\"hidden\" NAME=\"pictureURL\" VALUE=\"" . $my_uploader->file['name'] . "\"></FORM>\n");
			echo("<B>".$lang['UP_OK']."</B>");
			echo("<BR>URL: " . $my_uploader->file['name']);

			echo("<P><A HREF=\"#\" onClick=\"updateOpener();\">".$lang['UP_USE_MUG']."\n");

			// End the page.
			echo("<P><A HREF=\"" . FILE_UPLOAD . "\">".$lang['UP_MORE']."</A>\n");
			echo("</BODY></HTML>");
			exit();
			
			/* STUFF THAT WE'RE NOT GOING TO USE
			// Print all the array details...
			//print_r($my_uploader->file);
			
			// ...or print the file
			if(stristr($my_uploader->file['type'], "image")) {
				//echo "<img src=\"" . $path . $my_uploader->file['name'] . "\" border=\"0\" alt=\"\">";
			} else {
				$fp = fopen($path . $my_uploader->file['name'], "r");
				while(!feof($fp)) {
					$line = fgets($fp, 255);
					echo $line;
				}
				if ($fp) { fclose($fp); }
			}
			*/
 		}
 	}


#--------------------------------#
# HTML FORM
#--------------------------------#
//$theaction = $_SERVER['PHP_SELF'].'?id='.$id;
?>
	<FORM ENCTYPE="multipart/form-data" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>" METHOD="POST">
	<INPUT TYPE="hidden" NAME="submitted" VALUE="true">
		
		<B><?php echo $lang['LBL_UPLOAD_PICTURE'] ?>:</B>
		<BR>( <?php echo($lang['UP_FORMAT'].", ".$options->picWidth); ?> x <?php echo($options->picHeight)." ". $lang['UP_MAX']?>)
		<BR><INPUT NAME="<?php echo $upload_file_name; ?>" TYPE="file">
		<BR><?php echo $lang['BTN_CHOOSE_FILE'];?>
		<P>
<?php
/* ERROR MESSAGE OUTPUT FROM ORIGINAL FILE UPLOAD SCRIPT
		Error Messages:<br>
		<select name="language">
			<option value="en">English</option>
			<option value="fr">French</option>
			<option value="de">German</option>
			<option value="nl">Dutch</option>
			<option value="it">Italian</option>
			<option value="fi">Finnish</option>
			<option value="es">Spanish</option>
			<option value="no">Norwegian</option>
			<option value="da">Danish</option>
		</select>
		<br><br>
*/
?>
		<INPUT TYPE="hidden" VALUE="en">
<?php
// ** PRINT DUPLICATE FILE NAMES WARNING
	switch($options->picDupeMode) {
		case 1:
			echo("<B>".$lang['UP_WARN']."!</B><BR>".$lang['UP_DUPE_OVERWRITE']."!<BR>\n");
			break;
		case 2:
			echo("<B>".$lang['UP_WARN']."!</B><BR>".$lang['UP_DUPE_RENAME']."!<BR>\n");
			break;
		case 3:
			echo("<B>".$lang['UP_WARN']."!</B><BR>".$lang['UP_DUPE_NOT_UP']."!<BR>\n");
			break;
	}
?>
		<BR><INPUT TYPE="submit" VALUE="<?php echo $lang['BTN_UP_FILE']?>" CLASS="formButton">
	</FORM>


<?php
/*
	if (isset($acceptable_file_types) && trim($acceptable_file_types)) {
		print("This form only accepts <b>" . str_replace("|", " or ", $acceptable_file_types) . "</b> files\n");
	}
*/
?>

</BODY>
</HTML>
