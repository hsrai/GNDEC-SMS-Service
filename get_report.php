<?php   
// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	require_once(FILE_CLASS_CONTACTLIST);
	require_once(FILE_CLASSES);
	session_start();
	$by = $_SESSION['username'];
	echo "<link rel='stylesheet' href='styles.css' type='text/css'>";

// ** OPEN CONNECTION TO THE DATABASE **
	$db_link = openDatabase($db_hostname, $db_username, $db_password, $db_name);

// ** CHECK FOR LOGIN **
	checkForLogin();

// ** RETRIEVE OPTIONS THAT PERTAIN TO THIS PAGE **
	$options = new Options();
	
// ** END INITIALIZATION *******************************************************

	// CREATE THE LIST.	
	$list = new ContactList();
	
	// THIS PAGE TAKES SEVERAL GET VARIABLES
	// ie. list.php?group_id=6&page=2&letter=c&limit=20
	if ($_GET['groupid'])         $list->group_id = $_GET['groupid'];
	if ($_GET['page'])            $list->current_page = $_GET['page'];
	if (isset($_GET['letter']))   $list->current_letter = $_GET['letter'];	
	if (isset($_GET['limit']))    $list->max_entries = $_GET['limit'];	

	// Set group name (group_id defaults to 0 if not provided)
	$list->group_name();

	// ** RETRIEVE CONTACT LIST BY GROUP **
	$r_contact = $list->retrieve($_SESSION['username']);

    $username = $_SESSION['username'];
echo "<center><IMG SRC='images/title.png' WIDTH=570 HEIGHT=90 ALT='' BORDER=0></IMG></center>";
echo "<center><form action='list.php' method='get'>
	<input type='submit' value='Go Back' />
	</form></center>";

echo "<table id='history'>
					<tr>
			<th>Full Name</th>
			<th>User Name</th>
			<th>User Type</th>
			<th>Total SMS Sent</th>
			</tr>";

	$conn = mysql_connect($db_hostname,$db_username,$db_password);
			mysql_select_db("adbook", $conn);
			$sql_users ="SELECT fullname, username, usertype FROM users";
			$result_users = mysql_query($sql_users);
			while($user = mysql_fetch_assoc($result_users)) {
				$sql = "SELECT COUNT(*) FROM report WHERE sender = '".$user['username']."'";
				$result = mysql_query($sql);
				while($row = mysql_fetch_array($result))
  {
  echo "<tr>";
  echo "<td>" . $user['fullname'] . "</td>";
  echo "<td>" . $user['username'] . "</td>";
  echo "<td>" . $user['usertype'] . "</td>";
  echo "<td>" . $row[0] . "</td>";
 
  echo "</tr>";
  }
}
echo "</table>";
?>

<html>
<head>
<title>Sent Messages</title>
	</head>
	<body><center><form action='list.php' method='get'>
	<input type='submit' value='Go Back' />
	</form></center></body></html>
