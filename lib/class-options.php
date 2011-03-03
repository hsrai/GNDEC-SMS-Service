<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04
 *  
 *  lib/class-options.php
 *  Object: retrieve and set global or user options
 *
 *************************************************************/

 
class Options {
	
	// DECLARE OPTION VARIABLES
	var $bdayInterval;
	var $bdayDisplay;
	var $displayAsPopup;
	var $useMailScript;
	var $picAlwaysDisplay;
	var $picWidth;
	var $picHeight;
	var $picDupeMode;
	var $picAllowUpload;
	var $modifyTime; // not currently in use; reserved for future use
	var $msgLogin;
	var $msgWelcome;
	var $countryDefault;
	var $allowUserReg;
	var $eMailAdmin;
	var $requireLogin;
	var $language;
	var $defaultLetter; // test
	var $limitEntries; // test
	
	// DECLARE OTHER VARIABLES
	var $global_options;
	var $user_options;
	var $message;

	
	// CONSTRUCTOR FUNCTION
	function Options() {
		$this->get();
	}
	
	function get() {
		// This function retrieves global options first. Then, it retrieves user options
		// if a user name is available, which will overwrite certain global options.
		$this->set_global();
		if ((isset($_SESSION['username'])) && ($_SESSION['username'] != '@auth_off')) {
			$this->set_user();
		}
	}
	
	function set_global() {
		// This function restores all options to the administrator-specified global settings.
		// Call this function when you need to ignore the user-specified settings.
		// Note: If you do not call this function, you can still obtain global settings
		// directly using the $this->global_options variable.
		global $db_link;
		
		$this->global_options = mysql_fetch_array(mysql_query("SELECT * FROM " . TABLE_OPTIONS . " LIMIT 1", $db_link))
				or die(reportScriptError("Unable to retrieve global options."));

		$this->bdayInterval     = $this->global_options['bdayInterval'];
		$this->bdayDisplay      = $this->global_options['bdayDisplay'];
		$this->displayAsPopup   = $this->global_options['displayAsPopup'];
		$this->useMailScript    = $this->global_options['useMailScript'];
		$this->picAlwaysDisplay = $this->global_options['picAlwaysDisplay'];
		$this->picWidth         = $this->global_options['picWidth'];
		$this->picHeight        = $this->global_options['picHeight'];
		$this->picDupeMode      = $this->global_options['picDupeMode'];
		$this->picAllowUpload   = $this->global_options['picAllowUpload'];
		$this->modifyTime       = $this->global_options['modifyTime'];
		$this->msgLogin         = stripslashes( $this->global_options['msgLogin'] );
		$this->msgWelcome       = stripslashes( $this->global_options['msgWelcome'] );
		$this->countryDefault   = $this->global_options['countryDefault'];
		$this->allowUserReg     = $this->global_options['allowUserReg'];
		$this->eMailAdmin       = $this->global_options['eMailAdmin'];
		$this->requireLogin     = $this->global_options['requireLogin'];
		$this->language         = $this->load_lang($this->global_options['language']);
		$this->defaultLetter    = $this->global_options['defaultLetter'];
		$this->limitEntries     = $this->global_options['limitEntries'];
	}
	
	function set_user() {
		// This function overrides admin-specified options with user options.
		// Call this function if you need to restore the user settings after resetting
		// to global settings.
		// Note: If you do not call this function, you can still obtain the user settings
		// directly using the $this->user_options variable.
		global $db_link;
		
		$this->user_options = mysql_fetch_array(mysql_query("SELECT * FROM " . TABLE_USERS . " WHERE username='" . $_SESSION['username'] . "' LIMIT 1", $db_link))
				or die(reportScriptError("Unable to retrieve user options."));

		if (!is_null($this->user_options['bdayInterval']))   $this->bdayInterval = $this->user_options['bdayInterval'];
		if (!is_null($this->user_options['bdayDisplay']))    $this->bdayDisplay = $this->user_options['bdayDisplay'];
		if (!is_null($this->user_options['displayAsPopup'])) $this->displayAsPopup = $this->user_options['displayAsPopup'];
		if (!is_null($this->user_options['useMailScript']))  $this->useMailScript = $this->user_options['useMailScript'];
		if (!is_null($this->user_options['language']))       $this->language = $this->load_lang($this->user_options['language']);
		if (!is_null($this->user_options['defaultLetter']))  $this->defaultLetter = $this->user_options['defaultLetter'];
		if (!is_null($this->user_options['limitEntries']))   $this->limitEntries = $this->user_options['limitEntries'];
	}
	
	function save_global() {
		// This function saves global settings to the database, in the options table.
		// It assumes that the options have already been placed in the $_POST superglobal.
		global $db_link;
		global $lang;

		// CHECK NUMERICAL INPUT
		// This is DIFFERENT from the previous implemenation (TAB 1.03 and earlier)
		// where empty or faulty information resulted in resetting the value to a
		// hard-coded default value. Here, it will check if the $_POST value is valid,
		// and if so, it will overwrite the existing setting. Otherwise the original
		// value (whatever it is) is retained.
		if (($_POST['bdayInterval'] > 0) && is_numeric($_POST['bdayInterval']))     $this->bdayInterval = $_POST['bdayInterval'];
		if (($_POST['picWidth'] > 0) && is_numeric($_POST['picWidth']))             $this->picWidth = $_POST['picWidth'];
		if (($_POST['picHeight'] > 0) && is_numeric($_POST['picHeight']))           $this->picHeight = $_POST['picHeight'];
		if (($_POST['picDupeMode'] == 1) || ($_POST['picDupeMode'] == 2) || ($_POST['picDupeMode'] == 3))  $this->picDupeMode = $_POST['picDupeMode'];
		if (($_POST['countryDefault']))                                             $this->countryDefault = $_POST['countryDefault'];
		if (($_POST['limitEntries'] >= 0) && is_numeric($_POST['limitEntries']))    $this->limitEntries = $_POST['limitEntries'];
		
		if ($_POST['language']) $this->language = $_POST['language'];	// not numerical, but the same principle applies
		$this->defaultLetter = (empty($_POST['defaultLetter'])) ? "" : $_POST['defaultLetter']; // if no value is sent, then turn defaultLetter off (note: off must be empty string, NOT 0 value)

		// CLEAN UP STRING INPUT
		// These are allowed to be blank. We will take these "as is" -- no checking is done.
		$this->msgLogin   = addslashes(strip_tags(trim($_POST['msgLogin']),'<a><b><i><u><p><br>'));
		$this->msgWelcome = addslashes(strip_tags(trim($_POST['msgWelcome']),'<a><b><i><u><p><br>'));
		
		// CHECKBOXES
		// If the variable does not exist in $_POST, that means the checkbox is turned off!
		// Give it a value of 0 so we know what to enter into the database.
		// Everything else results in 1 (which should be the contents of the $_POST variable anyway, but let's be sure
		$this->bdayDisplay      = (empty($_POST['bdayDisplay'])) ? 0 : 1;
		$this->displayAsPopup   = (empty($_POST['displayAsPopup'])) ? 0 : 1;
		$this->useMailScript    = (empty($_POST['useMailScript'])) ? 0 : 1;
		$this->picAlwaysDisplay = (empty($_POST['picAlwaysDisplay'])) ? 0 : 1;
		$this->picAllowUpload   = (empty($_POST['picAllowUpload'])) ? 0 : 1;
		$this->allowUserReg     = (empty($_POST['allowUserReg'])) ? 0 : 1;
		$this->eMailAdmin       = (empty($_POST['eMailAdmin'])) ? 0 : 1;
		$this->requireLogin     = (empty($_POST['requireLogin'])) ? 0 : 1;

		// CREATES THE QUERY AND UPDATES THE OPTIONS TABLE
		$sql = "UPDATE " . TABLE_OPTIONS . " SET 
				bdayInterval      = $this->bdayInterval,
				bdayDisplay       = $this->bdayDisplay,
				displayAsPopup    = $this->displayAsPopup,
				useMailScript     = $this->useMailScript,
				picAlwaysDisplay  = $this->picAlwaysDisplay,
				picWidth          = $this->picWidth,
				picHeight         = $this->picHeight,
				picDupeMode       = $this->picDupeMode,
				picAllowUpload    = $this->picAllowUpload,
				modifyTime        = $this->modifyTime,
				msgLogin          = '$this->msgLogin',
				msgWelcome        = '$this->msgWelcome',
				countryDefault    = '$this->countryDefault',
				allowUserReg      = $this->allowUserReg,
				requireLogin      = $this->requireLogin,
				eMailAdmin        = $this->eMailAdmin,
				language          = '$this->language',
				defaultLetter     = '$this->defaultLetter',
				limitEntries      = $this->limitEntries";

		mysql_query($sql, $db_link)
			or die(reportSQLError($lang['ERR_OPTIONS_NO_SAVE']));

		$this->get();
		$this->message = $lang['OPT_SAVED'];

		return true;
	}
	
	
	function save_user() {
		// This function saves user settings to the database, in the users table.
		// This is largely similar in function to save_global() except that there are much fewer
		// options to deal with. It may be better to condense the two functions into 
		// one function so as to avoid repetition of code but we can worry about that later.
		global $db_link;
		global $lang;
		
		// CHECK INPUT
		// Condensed version of events from save_global().
		if (($_POST['bdayInterval'] > 0) && is_numeric($_POST['bdayInterval']))     $this->bdayInterval = $_POST['bdayInterval'];
		if (($_POST['limitEntries'] >= 0) && is_numeric($_POST['limitEntries']))    $this->limitEntries = $_POST['limitEntries'];
		if ($_POST['language']) $this->language = $_POST['language'];
		$this->defaultLetter    = (empty($_POST['defaultLetter'])) ? "" : $_POST['defaultLetter'];
		$this->bdayDisplay      = (empty($_POST['bdayDisplay'])) ? 0 : 1;
		$this->displayAsPopup   = (empty($_POST['displayAsPopup'])) ? 0 : 1;
		$this->useMailScript    = (empty($_POST['useMailScript'])) ? 0 : 1;

		// CREATES THE QUERY AND UPDATES THE OPTIONS TABLE
		$sql = "UPDATE " . TABLE_USERS . " SET 
				bdayInterval      = $this->bdayInterval,
				bdayDisplay       = $this->bdayDisplay,
				displayAsPopup    = $this->displayAsPopup,
				useMailScript     = $this->useMailScript,
				language          = '$this->language',
				defaultLetter     = '$this->defaultLetter',
				limitEntries      = $this->limitEntries
				WHERE username='" . $_SESSION['username'] . "'";
		mysql_query($sql, $db_link)
			or die(reportSQLError($lang['ERR_OPTIONS_NO_SAVE']));
		
		$this->get();
		$this->message = $lang['OPT_SAVED_USER'];

		return true;
	}
	
	function reset_user() {
		// This function is designed to clear the user's settings and have all option variables
		// set to NULL in the database. NULL means neither yes or no, and will force the
		// script to look to the global options table for information.
		global $db_link;
		global $lang;

		// QUERY
		$sql = "UPDATE " . TABLE_USERS . " SET 
				bdayInterval      = NULL,
				bdayDisplay       = NULL,
				displayAsPopup    = NULL,
				useMailScript     = NULL,
				language          = NULL,
				defaultLetter     = NULL,
				limitEntries      = NULL
				WHERE username='" . $_SESSION['username'] . "'";
		mysql_query($sql, $db_link)
			or die(reportSQLError($lang['ERR_OPTIONS_NO_SAVE']));
		
		// RESET MEMBER VARIABLES
		$this->set_global();	
		
		$this->message = $lang['OPT_RESET_USER'];
		return true;
	}
		
	function load_lang($file) {
		global $php_ext;
		// The following variables are loaded from country files. Make these global scope
		global $lang;
		global $country;
		
		$fullpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . PATH_LANGUAGES . $file . '.' . $php_ext;
		// This function takes the value returned by the 'language' column in global or user options table,
		// and checks to make sure that the file exists in the /language directory. If it exists, it loads
		// the language into memory. If it does not exist, it attempts to loads 'english' (the default language).
		if (file_exists($fullpath)) {
			require_once($fullpath);
			return $file;
		} else {
			require_once(dirname($_SERVER['SCRIPT_FILENAME']) . '/' . PATH_LANGUAGES . 'english.' . $php_ext);
			$this->message = $lang['OPT_LANGUAGE_MISSING'];
			return 'english';
		} 
	}
	
// END Options
}


?>
