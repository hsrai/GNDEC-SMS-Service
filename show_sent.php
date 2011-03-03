<?php   
// ** GET CONFIGURATION DATA **
	require_once('constants.inc');
	require_once(FILE_FUNCTIONS);
	require_once(FILE_CLASS_OPTIONS);
	require_once(FILE_CLASS_CONTACTLIST);
	require_once(FILE_CLASSES);
	session_start();
	$by = $_SESSION['username'];

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

	$conn = mysql_connect($db_hostname,$db_username,$db_password);
			mysql_select_db("adbook", $conn);
	$sql = "SELECT receiver, mobile, msgdata, time FROM report WHERE sender = '".$username."'";
	$result = mysql_query($sql);

echo "<table border = '1px' width='100%'>
					<tr>
			<th>Receiver</th>
			<th>Mobile</th>
			<th>Message</th>
			<th>Date/Time</th>
			</tr>";
	
while($row = mysql_fetch_assoc($result))
  {
  echo "<tr>";
  echo "<td>" . $row['receiver'] . "</td>";
  echo "<td>" . $row['mobile'] . "</td>";
  echo "<td>" . $row['msgdata'] . "</td>";
  echo "<td>" . $row['time'] . "</td>";
  echo "</tr>";
  }
echo "</table>";



?>

<html>
<head>
<title>Sent Messages</title>
	<link rel="stylesheet" href="styles.css" type="text/css">
	<style type="text/css">
	table, td, th
{
border:1px solid black;}




td{height: 50px; vertical-align: bottom; text-align:left;}
th
{
background-color:black;
color:white;
}
caption{caption-side:top;}
	</style>
	</head>
	<body><center><form action='list.php' method='get'>
	<input type='submit' value='Go Back' />
	</form></center></body></html>

