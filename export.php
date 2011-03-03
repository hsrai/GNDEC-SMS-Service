<?php
/*************************************************************
 *  THE ADDRESS BOOK  :  version 1.04d
 *   
 *
 ****************************************************************
 *  export.php
 *  Exports entries to a variety of other formats.
 *
 *************************************************************/


// ** GET CONFIGURATION DATA **
    require_once('constants.inc');
    require_once(FILE_FUNCTIONS);
    require_once(FILE_CLASS_OPTIONS);

// ** START SESSION **
	session_start();
	
// ** OPEN CONNECTION TO THE DATABASE **
    $db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);
    
    $options = new Options();

// ** CHECK FOR LOGIN **
	checkForLogin();

// ** EXPORT FORMATS **
	switch($_GET['format']) {


/********************************************************************************
 ** MYSQL DUMP FORMAT
 **
 ********************************************************************************/
		case "mysql":

			// FUNCTION DECLARATION
			function createInsertQuery($table) {
				global $db_link;
				// Obtain the information from the table
				$result = mysql_query("SELECT * FROM " . $table, $db_link);
						// Note on this query -- previously, it had the additional statement ORDER BY id at the end of it, which created a very clean export
						// But the Options and Scratchpad tables don't have an id field, so you can't tell it to ORDER BY id -- you'd get no results from the query if you did
						// So it has been removed and that way it works for any table that don't have an id field... unfortunately the output is now not as clean. But 
						// it's not as important to have it that way.
			    // Create the Insert Query
				while ($resultrow = mysql_fetch_row($result)) {
					echo "INSERT INTO " . $table . " VALUES(";
					for ($i=0; $i < count($resultrow); $i++) {
						if ($i != 0) {
							echo ",";  // As long as it's not the first element, print a comma separation
						}
						echo (is_numeric($resultrow[$i]) ? "$resultrow[$i]" : "\"" . addslashes($resultrow[$i]) . "\""); // outputs numbers without quotes, strings with addslashes/double-quotes
					}
					echo ");\n";
				}
				// Clear the result from memory -- we don't need it anymore
				mysql_free_result($result);
			// end function
			}

			// OUTPUT
		    header("Content-type: text/plain");
			header("Content-disposition: attachment; filename=tab_mysql.txt");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: 0");
		    echo " * ". $lang['EXP_MYSQL_1']." \n";
		    echo " * ". $lang['EXP_MYSQL_2']." \n";
		    echo " * ". $lang['EXP_MYSQL_3']." \n";
		    echo " *\n";
		    echo " * ". $lang['EXP_MYSQL_4']." \n";
		    echo " *\n";
		    echo " * ". $lang['EXP_MYSQL_5']." \n";
		    echo " *\n";
		    echo " * ". $lang['EXP_MYSQL_6']." \n";
		    echo " * ". $lang['EXP_MYSQL_7']." \n";
		    echo " * ". $lang['EXP_MYSQL_8']." \n";
		    echo " * ". $lang['EXP_MYSQL_9']." ". date("l F j Y, H:i:s\n");
		    echo " * ". $lang['EXP_MYSQL_10']." \n";
		    echo " * ". $lang['EXP_MYSQL_11']." \n";
		    echo " * ". $lang['TAB']." ".VERSION_NO ." \n";
		    echo " *\n";
    // The following block of code must be automated.
		    echo("\n\n");
			echo "DROP TABLE IF EXISTS " . TABLE_ADDITIONALDATA . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_ADDRESS . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_CONTACT . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_EMAIL . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_GROUPLIST . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_GROUPS . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_MESSAGING . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_OPTIONS . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_OTHERPHONE . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_WEBSITES . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_USERS . ";\n";
			echo "DROP TABLE IF EXISTS " . TABLE_SCRATCHPAD . ";\n";
			echo "CREATE TABLE " . TABLE_ADDITIONALDATA . " (id INT(11) NOT NULL DEFAULT '0', type VARCHAR(20) DEFAULT NULL, value TEXT) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_ADDRESS . " (refid INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id INT(11) NOT NULL DEFAULT '0', type VARCHAR(20) NOT NULL DEFAULT '', line1 VARCHAR(100) DEFAULT NULL, line2 VARCHAR(100) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, state VARCHAR(10) DEFAULT NULL, zip VARCHAR(20) DEFAULT NULL, country VARCHAR(3) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, phone2 VARCHAR(20) DEFAULT NULL ) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_CONTACT . " (id INT(11) NOT NULL AUTO_INCREMENT, firstname VARCHAR(40) NOT NULL DEFAULT '', lastname VARCHAR(80) NOT NULL DEFAULT '', middlename VARCHAR(40) DEFAULT NULL, primaryAddress INT(11) DEFAULT NULL, birthday DATE DEFAULT NULL, nickname VARCHAR(40) DEFAULT NULL, pictureURL VARCHAR(255) DEFAULT NULL, notes TEXT, lastUpdate DATETIME DEFAULT NULL, hidden INT(1) DEFAULT '0' NOT NULL, whoAdded VARCHAR(15), PRIMARY KEY (id)) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_EMAIL . " (id INT(11) NOT NULL DEFAULT '0', email VARCHAR(100) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_GROUPLIST . " (groupid INT(11) NOT NULL DEFAULT '0', groupname VARCHAR(60) DEFAULT NULL, PRIMARY KEY (groupid)) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_GROUPS . " (id INT(11) NOT NULL DEFAULT '0', groupid TINYINT(4) NOT NULL DEFAULT '0') TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_MESSAGING . " (id INT(11) NOT NULL DEFAULT '0', handle VARCHAR(30) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_OPTIONS . " (bdayInterval INT(3) DEFAULT '21' NOT NULL, bdayDisplay INT(1) DEFAULT '1' NOT NULL, displayAsPopup INT(1) DEFAULT '0' NOT NULL, useMailScript INT(1) DEFAULT '1' NOT NULL, picAlwaysDisplay INT(1) DEFAULT '0' NOT NULL, picWidth INT(1) DEFAULT '140' NOT NULL, picHeight INT(1) DEFAULT '140' NOT NULL, picDupeMode INT(1) DEFAULT '1' NOT NULL, picAllowUpload INT(1) DEFAULT '1' NOT NULL, modifyTime VARCHAR(3) DEFAULT '0' NOT NULL, msgLogin TEXT NULL, msgWelcome VARCHAR(255) NULL, countryDefault CHAR(3) DEFAULT '0' NULL, allowUserReg INT(1) DEFAULT '0' NOT NULL, eMailAdmin int(1) NOT NULL default '0', requireLogin INT(1) DEFAULT '1' NOT NULL, language VARCHAR(25) NOT NULL, defaultLetter char(2) default NULL, limitEntries smallint(3) NOT NULL default '0') TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_OTHERPHONE . " (id INT(11) NOT NULL DEFAULT '0', phone VARCHAR(20) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_WEBSITES . " (id INT(11) NOT NULL DEFAULT '0', webpageURL VARCHAR(255) DEFAULT NULL, webpageName VARCHAR(255) DEFAULT NULL) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_USERS . " (id INT(2) NOT NULL AUTO_INCREMENT, username VARCHAR(15) NOT NULL, usertype ENUM('admin','user','guest') NOT NULL DEFAULT 'user', password VARCHAR(32) NOT NULL DEFAULT '', email VARCHAR(50) NOT NULL, confirm_hash VARCHAR(50) NOT NULL, is_confirmed TINYINT(1) DEFAULT '0' NOT NULL, bdayInterval int(3) default NULL, bdayDisplay int(1) default NULL, displayAsPopup int(1) default NULL, useMailScript int(1) default NULL, language varchar(25) default NULL, defaultLetter char(2) default NULL, limitEntries smallint(3) NOT NULL default '0', PRIMARY KEY (id), UNIQUE KEY username (username)) TYPE=MyISAM;\n";
			echo "CREATE TABLE " . TABLE_SCRATCHPAD . " (notes TEXT NOT NULL) TYPE=MyISAM;\n";

			// GET AND OUTPUT ALL THE DATA
			$tables = array(TABLE_ADDITIONALDATA, TABLE_ADDRESS, TABLE_CONTACT, TABLE_EMAIL, TABLE_GROUPLIST, TABLE_GROUPS, TABLE_MESSAGING, TABLE_OPTIONS, TABLE_OTHERPHONE, TABLE_WEBSITES, TABLE_USERS, TABLE_SCRATCHPAD);
			while ($a = each($tables)) {
				createInsertQuery($a[1]);
			}

			// END
			break;

/********************************************************************************
 ** EUDORA NICKNAMES FORMAT
 **
 ********************************************************************************/
		case "eudora":

			// Retrieve data associated with given ID
		    $nnListQuery = "SELECT contact.id, CONCAT(firstname,' ', lastname) AS fullname, email FROM " . TABLE_CONTACT . " AS contact, " . TABLE_EMAIL . " AS email WHERE contact.id=email.id ORDER BY contact.id";

		    $r_contact = mysql_query($nnListQuery, $db_link)
				or die(reportSQLError($nnListQuery));

			// OUTPUT
		    header("Content-type: text/plain");
			header("Content-disposition: attachment; filename=NNdbase.txt");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: 0");

		    while ($tbl_contact = mysql_fetch_array($r_contact)) {
		        echo("\n");
		        echo('alias "' . 
		              $tbl_contact['fullname'] . '" ' .
		              $tbl_contact['fullname'] . ' <' . 
		              $tbl_contact['email'] . '>');
		    }
		
			// END
			break;

/********************************************************************************
 ** COMMA-SEPARATED VALUES (CSV) FORMAT
 **
 ** thanks to sineware
 ********************************************************************************/
		case "csv":

			// QUERY
		    $csvQuery = "SELECT contact.id, firstname, middlename, lastname, birthday, notes, 
		    		           	email.email, address.line1, address.line2, address.city, address.state, address.zip, 
								address.phone1, address.phone2, otherphone.phone, websites.webpageURL
				     	FROM ". TABLE_CONTACT ." AS contact
						LEFT JOIN ". TABLE_EMAIL ." AS email ON contact.id=email.id
			        	LEFT JOIN ". TABLE_ADDRESS ." AS address ON address.id=contact.id
						LEFT JOIN ". TABLE_OTHERPHONE ." AS otherphone ON contact.id=otherphone.id
						LEFT JOIN ". TABLE_WEBSITES ." AS websites ON contact.id=websites.id";
		    $r_contact = mysql_query($csvQuery, $db_link)
				or die(reportSQLError($csvQuery));

			// OUTPUT
			header("Content-Type: text/comma-separated-values");
			header("Content-disposition: attachment; filename=tab.csv");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: 0");

		    echo("firstname,middlename,lastname,birthday,email,address1,address2,city,state,zip,phone1,phone2,phone3,website,notes\n");
		    while ($tbl_contact = mysql_fetch_array($r_contact)) {
				// Most  variables are checked for the comma (,) character, which will be
				// removed if found. This is to prevent these fields from breaking the CSV format.
				echo(str_replace(",","",$tbl_contact['firstname']) . "," .
					str_replace(",","",$tbl_contact['middlename']) . "," .
					str_replace(",","",$tbl_contact['lastname']) . "," .
					$tbl_contact['birthday'] . "," .
					$tbl_contact['email'] . "," . 
					str_replace(",","",$tbl_contact['line1']) . "," . 
					str_replace(",","",$tbl_contact['line2']) . "," . 
					str_replace(",","",$tbl_contact['city']) . "," . 
					str_replace(",","",$tbl_contact['state']) . "," . 
					str_replace(",","",$tbl_contact['zip']) . "," . 
					str_replace(",","",$tbl_contact['phone1']) . "," .
					str_replace(",","",$tbl_contact['phone2']) . "," .
					str_replace(",","",$tbl_contact['phone']) . "," .
					str_replace(",","",$tbl_contact['webpageURL']) . "," .
					str_replace(",","",$tbl_contact['notes']) . "\n");
		    }

			// END
			break;


/********************************************************************************
 ** TEXT FORMAT
 **
 ** (thanks to David Léonard) -- Beta, but working. -- broken pending existence of acessBD.php
 ********************************************************************************/
		case "text":

			// QUERY
				$query ="
					SELECT 
					  `address_contact`.`id`,
					  `address_contact`.`firstname`,
					  `address_contact`.`lastname`,
					  `address_contact`.`middlename`,
					  `address_contact`.`primaryAddress`,
					  `address_contact`.`birthday`,
					  `address_contact`.`nickname`,
					  `address_contact`.`pictureURL`,
					  `address_contact`.`notes`,
					  `address_contact`.`lastUpdate`,
					  `address_contact`.`hidden`,
					  `address_contact`.`whoAdded`,
					  `address_address`.`type`,
					  `address_address`.`line1`,
					  `address_address`.`line2`,
					  `address_address`.`city`,
					  `address_address`.`state`,
					  `address_address`.`zip`,
					  `address_address`.`country`,
					  `address_address`.`phone1`,
					  `address_address`.`phone2`
					FROM
					  `address_contact`
					  INNER JOIN `address_address` ON (`address_contact`.`id` = `address_address`.`id`)
					
				";
		    
		    $data 	= new accesBDlecture ($query,"","");
		    $query	= "SELECT * FROM address_grouplist WHERE 1";
		    $entete = new accesBDlecture($query,"","");
		    
			// OUTPUT
		    header("Content-type: text/plain");
			header("Content-disposition: attachment; filename=tab.txt");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: 0");

		    
		    //affichage des entetes communs
		    echo "NUMERO\tPRENOM\tNOM\tTITRE\tANNIVERSAIRE\tMÀJ LE\tPROPRIETAIRE\tTYPE ADRESSE\tADRESSE1\tADRESSE2\tVILLE\tETAT\tNPA\tPAYS\tTEL1\tTEL2\t";
		    
		    //affichage des entetes correspondant aux noms des groupes
		    foreach ($entete->row as $courant) {
		    	if ($courant == NULL) break;
		    	if ($courant->groupid <3)
		    		{continue;}
		    	else
		    		{echo"$courant->groupname\t";}
		    }
		    echo"\n";
		    
		    //remplissage des données suivant les entetes
		    foreach ($data->row as $donnee) {
		    	if ($donnee == NULL) break;
		    	//sélection du nom du pays
		    	$query 				= "SELECT countryname FROM address_country WHERE id = ".$donnee->country." ";
		    	$pays 				= new accesBDlecture($query,"","");
		    	$paysCourant 	= $pays->row[0]->countryname;
		    	
		    	//affichage des données communes
		    	echo "$donnee->id\t$donnee->firstname\t$donnee->lastname\t$donnee->nickname\t$donnee->birthday\t$donnee->lastUpdate\t$donnee->whoAdded\t$donnee->type\t$donnee->line1\t$donnee->line2\t$donnee->city\t$donnee->state\t$donnee->zip\t$paysCourant\t$donnee->phone1\t$donnee->phone2\t";
		    	
		    	//sélection des des groupes dont fait partie l'adresse courante
		    	$query = "SELECT * FROM address_groups WHERE id =".$donnee->id." ";
		    	$groupe = new accesBDlecture($query,"","");
		    	$query	= "SELECT * FROM address_grouplist WHERE 1 ORDER BY 1";
		    	$entete = new accesBDlecture($query,"","");
		    	foreach ($entete->row as $courant) {
		    			if ($courant == NULL) break;
		    			if ($courant->groupid <3)
		    				{continue;}
		    			else
		    				{
		    					$valide = "NON\t";
		    				foreach ($groupe->row as $groupeCourant) {
		    					if ($groupeCourant == NULL)break;
		    					//comparaison avec les groupes actuels
		    					if ($courant->groupid == $groupeCourant->groupid) $valide = "OUI\t";
		    				}
		    				echo $valide;
		    		}
		    	}
		    	echo "\n";
		    	unset ($query,$pays,$paysCourant);

		    }

			// END
			break;


/********************************************************************************
 ** XML FORMAT
 **
 ** XML quick format
 ** Please use export.xsl for formatting output file - NOT YET!
 ** thanks to "mutato" <radio@frequenze.it>!
 ********************************************************************************/
		case "xml":

			// QUERY



			$xmlQuery = "SELECT * FROM ". TABLE_CONTACT . "";
			$r_contact = mysql_query($xmlQuery, $db_link);

			// OUTPUT
			header("Content-type: text/xml");
			header("Content-disposition: attachment; filename=tab.xml");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: 0");

			echo "<?xml version=\"1.0\" encoding=\"".$lang['CHARSET']."\"?>\n\n";
			echo "<rubrica>\n\n";

			while ($tbl_contact = mysql_fetch_array($r_contact)) {

			# short id
			$XID = $tbl_contact['id'];

			echo "<CONTACT id=\"".$XID."\" update=\"".$tbl_contact['lastUpdate']."\">\n";

			# personal data from TABLE_CONTACT
			echo "<PERSONALDATA>\n";
			echo "<firstname>".$tbl_contact['firstname']."</firstname>\n";
			echo "<middlename>".$tbl_contact['middlename']."</middlename>\n";
			echo "<lastname>".$tbl_contact['lastname']."</lastname>\n";
			echo "<birthday>".$tbl_contact['birthday']."</birthday>\n";
			echo "<nick>".$tbl_contact['nickname']."</nick>\n";
			echo "<notes><![CDATA[\n".$tbl_contact['notes']."\n]]></notes>\n";
			echo "</PERSONALDATA>\n";

			# below this line you can move
			# up or down section data

			# ********************
			# TABLE_EMAIL
			# ********************
			echo "<EMAIL>\n";
			$xmlMail = "SELECT * FROM ". TABLE_EMAIL . " WHERE id=$XID";
			$r_mail = mysql_query($xmlMail, $db_link);

			while ($tbl_mail = mysql_fetch_array($r_mail)) {
				echo "<mail type=\"".$tbl_mail['type']."\">".$tbl_mail['email']."</mail>\n";
			} 

			echo "</EMAIL>\n";
			# ********************
			# /END TABLE_EMAIL 
			# ********************

			# ********************
			# TABLE_ADDRESS 
			# ********************
			echo "<ADDRESS>\n";

			$xmlAddr = "SELECT * FROM ". TABLE_ADDRESS . " WHERE id=$XID";
			$r_addr = mysql_query($xmlAddr, $db_link);

			while ($tbl_addr = mysql_fetch_array($r_addr)) {

			echo "<address type=\"".$tbl_addr['type']."\">\n";
			echo "<line1>".$tbl_addr['line1']."</line1>\n";
			echo "<line2>".$tbl_addr['line2']."</line2>\n";
			echo "<city>".$tbl_addr['city']."</city>\n";
			echo "<state>".$tbl_addr['state']."</state>\n";
			echo "<zip>".$tbl_addr['zip']."</zip>\n";

			# TABLE_COUNTRY
			$xmlCountry = $tbl_addr['country'];

			echo "<country>".$country[$xmlCountry]."</country>\n";
			echo "<phone1>".$tbl_addr['phone1']."</phone1>\n";
			echo "<phone2>".$tbl_addr['phone2']."</phone2>\n";
			echo "</address>\n";

			} 

			echo "</ADDRESS>\n";
			# ********************
			# /END TABLE_ADDRESS 
			# ********************

			# ********************
			# TABLE_OTHERPHONE 
			# ********************
			echo "<OTHER-PHONE>\n";
			$xmlPhone = "SELECT * FROM ". TABLE_OTHERPHONE . " WHERE id=$XID";
			$r_phone = mysql_query($xmlPhone, $db_link);



			while ($tbl_phone = mysql_fetch_array($r_phone)) {

			echo "<phone type=\"".$tbl_phone['type']."\">".$tbl_phone['phone']."</phone>\n";

			} 

			echo "</OTHER-PHONE>\n";
			# ********************
			# /END TABLE_OTHERPHONE
			# ********************

			# ********************
			# TABLE_WEBSITES 
			# ********************
			echo "<WEBSITES>\n";
			$xmlWWW = "SELECT * FROM ". TABLE_WEBSITES . " WHERE id=$XID";
			$r_www = mysql_query($xmlWWW, $db_link);

			while ($tbl_www = mysql_fetch_array($r_www)) {

			echo "<www label=\"".$tbl_www['webpageName']."\">".$tbl_www['webpageURL']."</www>\n";

			} 

			echo "</WEBSITES>\n";
			# ********************
			# /END TABLE_WEBSITES 
			# ********************

			# ********************
			# TABLE_ADDITIONALDATA
			# ********************
			echo "<ADDITIONAL-DATA>\n";
			$xmlData = "SELECT * FROM ". TABLE_ADDITIONALDATA . " WHERE id=$XID";
			$r_data = mysql_query($xmlData, $db_link);

			while ($tbl_data = mysql_fetch_array($r_data)) {

			echo "<data type=\"".$tbl_data['type']."\">".$tbl_data['value']."</data>\n";

			} 

			echo "</ADDITIONAL-DATA>\n";
			# ************************
			# /END TABLE_ADDITIONALDATA 
			# ************************

			# ********************
			# GROUPS SUBSCRIPTIONS
			# ********************
			echo "<GROUPS>\n";
			$xmlGroups = "SELECT * FROM ". TABLE_GROUPS . " WHERE id=$XID";
			$r_groups = mysql_query($xmlGroups, $db_link);

			while ($tbl_groups = mysql_fetch_array($r_groups)) {

			# groups name
			$xmlGN = "SELECT * FROM ". TABLE_GROUPLIST . " WHERE groupid=".$tbl_groups['groupid']."";
			$r_gn = mysql_query($xmlGN, $db_link);
			$tbl_gn = mysql_fetch_array($r_gn);


			echo "<group id=\"".$tbl_gn['groupid']."\" name=\"".$tbl_gn['groupname']."\"/>\n";

			} 

			echo "</GROUPS>\n";
			# ***********************
			# /END GROUPS SUBSCRIPTION
			# ***********************

			#### do not move ########
			echo "</CONTACT>\n\n";
			} 
			### close xmlQuery ######


			echo "</rubrica>";

			// END
			break;


/********************************************************************************
 ** GMAIL-IMPORTABLE CSV FORMAT
 **
 ********************************************************************************/
		case "gmail":

			// QUERY
		    $gmailQuery = "SELECT firstname, lastname, email, type FROM ". TABLE_CONTACT ." AS contact LEFT JOIN ". TABLE_EMAIL ." AS email ON contact.id=email.id WHERE email.email IS NOT NULL";
		    $r_contact = mysql_query($gmailQuery, $db_link)
				or die(reportSQLError($gmailQuery));

			// OUTPUT
			header("Content-Type: text/comma-separated-values");
			header("Content-disposition: attachment; filename=tab_gmail.csv");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Expires: 0");

		    echo("Name,Email Address\n");
		    while ($tbl_contact = mysql_fetch_array($r_contact)) {
				// First Name, Last Name, and Type variables are checked for the comma (,) character, which will be
				// removed if found. This is to prevent these fields from breaking the CSV format.
				echo(str_replace(",", "",$tbl_contact['firstname']) . " " . str_replace(",", "",$tbl_contact['lastname']));
				if(str_replace(",", "",$tbl_contact['type'])) {
					echo(" (" . str_replace(",", "",$tbl_contact['type']) . ")");
				}
				echo("," . $tbl_contact['email'] . "\n");
		    }

			// END
			break;
		case "vcard":  //from wilco on forum http://www.corvalis.net/phpBB2/viewtopic.php?t=294
		
			$vCardQuery = "SELECT id, firstname, middlename, lastname, nickname, birthday, pictureURL, notes
				     	FROM ". TABLE_CONTACT." WHERE whoAdded = '".$_SESSION['username']."'";
				     	
			$r_contact = mysql_query($vCardQuery, $db_link)
				or die(reportSQLError($vCardQuery));
				
			$mobile_prefix = '06'; // prefix for mobile numbers
			$picture_prefix = 'http://202.164.53.116/sms/mugshots/';
			
			
			//include('vcard.php');
			while($r = mysql_fetch_array($r_contact)) {  // $r means result
				$output .= "BEGIN:VCARD\nVERSION:3.0\n";
				$output .= 'FN:' . $r['firstname'] . "\n";
				$output .= 'N:' . $r['lastname'] . ';' . $r['firstname'] . ';' . $r['middlename'] . ";\n";
				if($r['nickname']) $output .= 'NICKNAME:' . $r['nickname'] . "\n";
				if($r['pictureURL']) $output .= 'PHOTO;VALUE=uri:' . $picture_prefix . $r['pictureURL'] . "\n";
				if($r['birthday'] != '0000-00-00') $output .= 'BDAY:' . $r['birthday'] . "\n";
				
				$i='primary';
				$adrq = 'SELECT line1, line2, city, state, phone1, phone2, zip FROM ' . TABLE_ADDRESS . ' WHERE id=' . $r['id'];
				$adrq = mysql_query($adrq);
				while($adr = mysql_fetch_array($adrq)) {
					$output .= 'ADR;TYPE=dom,home,postal';
					if($i == 'primary') {
						$output .= ',pref';
					}
					$output .= ':;;' . $adr['line1'] . ';' . $adr['city'] . ';' . $adr['state'] . ';' . $adr['zip'] . "\n";
					
					
					if($adr['phone1']) {
						$output .= 'TEL;TYPE=';
					
						if(eregi("^$mobile_prefix",$adr['phone1'])) {
							$output .= 'CELL,VOICE,MSG';
							if($i == 'primary') $output .= ',PREF';
						}
						else {
							$output .= 'HOME,VOICE';
							if($i == 'primary') $output .= ',PREF';
						}
					
						$output .= ':' . $adr['phone1'] . "\n";
					}
					
					if($adr['phone2']) {
						$output .= 'TEL;TYPE=';
						if(eregi("^$mobile_prefix",$adr['phone2'])) $output .= 'CELL,VOICE,MSG';
						else $output .= 'HOME,VOICE';
					
						$output .= ':' . $adr['phone2'] . "\n";
					}
					
					$i = 'not_primary';
				}
				
				
				$telq = 'SELECT phone FROM ' . TABLE_OTHERPHONE . ' WHERE id=' . $r['id'];
				$telq = mysql_query($telq);
				while($tel = mysql_fetch_array($telq)) {
					$output .= 'TEL;TYPE=';
					if(eregi("^$mobile_prefix",$tel['phone'])) $output .= 'CELL,VOICE,MSG';
					else $output .= 'HOME,VOICE';
					
					$output .= ':' . $tel['phone'] . "\n";
				}
				
				
				$emailq = 'SELECT email FROM ' . TABLE_EMAIL . ' WHERE id=' . $r['id'];
				$emailq = mysql_query($emailq);
				$i = 'primary';
				while($m = mysql_fetch_array($emailq)) {
					$output .= 'EMAIL;TYPE=internet,home';
					if($i == 'primary') $output .= ',PRIM';
					$output .= ':' . $m['email'] . "\n";
					$i = 'not_primary';
				}
			
				$urlq = 'SELECT webpageURL FROM ' . TABLE_WEBSITES . ' WHERE id=' . $r['id'];
				$urlq = mysql_query($urlq);
				while($url = mysql_fetch_array($urlq)) {
					$output .= 'URL:' . $url['webpageURL'] . "\n";
				}
				
				
				$output .= "END:VCARD\n";
				$output .= "\n";				
				
				
				}
			
			
			// for debugging
			//echo nl2br($output);
			
			Header("Content-Disposition: attachment; filename=export.vcf");
			Header("Content-Length: ".strlen($output));
			Header("Connection: close");
			Header("Content-Type: text/x-vCard; name=export.vcf");
			
			echo $output;

			
		
		
			
		break;

/******************************************************************************
 ** EXPORT MAIN MENU
 ********************************************************************************/

		// ** EXPORT MENU
		default:

			// OUTPUT
		    echo("<HTML>\n<HEAD>\n<TITLE>Address Book</TITLE>\n<LINK REL=\"stylesheet\" HREF=\"styles.css\" TYPE=\"text/css\">\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset= ".$lang['CHARSET']."\"></HEAD></HEAD>\n<BODY>\n");
		    echo($lang['EXP_TO_FILE']);
		    echo("<UL>");
		 //   echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=eudora\">".$lang['EXP_EUDORA']);
		  //  echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=mysql\">".$lang['EXP_MYSQL']);
		 //   echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=csv\">".$lang['EXP_CSV']);
		 //   echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=text\">".$lang['EXP_TXT']);
		   // echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=xml\">".$lang['EXP_XML']);
		  //  echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=gmail\">".$lang['EXP_GMAIL']);
		    echo("  <LI><A HREF=\"" . FILE_EXPORT . "?format=vcard\">".$lang['EXP_VCARD']);
		    echo("</UL>");
		    echo("<P>".$lang['EXP_CONVERT']." <A HREF=\"http://www.interguru.com/mailconv.htm\" TARGET=\"out\"> InterGuru's E-Mail Address Converter</A>");
		    echo("</BODY></HTML>");

			// END
			break;

	// END SWITCH
	}


?>
