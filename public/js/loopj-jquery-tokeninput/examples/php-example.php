<?

#
# Example PHP server-side script for generating
# responses suitable for use with jquery-tokeninput
#

# Connect to the database
mysql_pconnect("127.0.0.1", "h4p0", "hapo84") or die("Could not connect");
mysql_select_db("rateit") or die("Could not select database");

# Perform the query
$query = sprintf("SELECT id, username from user WHERE username LIKE '%%%s%%' LIMIT 10", mysql_real_escape_string($_GET['q']));
$arr = array();
$rs = mysql_query($query);

# Collect the results
while($obj = mysql_fetch_object($rs)) {
    $arr[] = $obj;
}

# JSON-encode the response
$json_response = json_encode($arr);

# Optionally: Wrap the response in a callback function for JSONP cross-domain support
if($_GET["callback"]) {
    $json_response = $_GET["callback"] . "(" . $json_response . ")";
}

# Return the response
echo $json_response;

?>
