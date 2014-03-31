<?php
session_start();
ini_set('display_errors', 'On'); 
require_once "connection.php";

if (isset($_POST['username']) && isset($_POST['password'])) {

	$username = $_POST['username'];
	$password = $_POST['password'];
	$birthdate = $_POST['birthdate'];
	$gender = $_POST['gender'];

	$_SESSION['name'] = $username;
	$_SESSION['password'] = $password;
	// $_SESSION['birthdate'] = $birthdate;
	// $_SESSION['gender'] = $gender;
}
	$sql = "select * from users where username='$username'";

	$stmt = oci_parse($conn, $sql);
	oci_execute($stmt);

	$err = oci_error($stmt);
	if ($err) {
		echo $err;
	}

	if (!oci_fetch_row($stmt) and $username) {
		sql2 = "insert into users values ('$username','$password','$birthdate','$gender')";
		$stmt = oci_parse($conn, $sql2);
		oci_execute($stmt);
		header('Location: index.php');
	} else if ($username) {
		echo "Sign up failed because the user login already exists. Please try again.";
	}

	oci_commit($conn);

	// while ($res = oci_fetch_row($stmt))
	// {
	// 	$_SESSION['User'] = $res[0];
	// 	header('Location: index.php');
	// }  

	// if (oci_fetch_array($stmt, OCI_NUM)) {
	// 	header('Location: index.php');
	// }
	// else
	// 	echo "Login failed. Please try again.";

	// while (($row = oci_fetch_array($stmt, OCI_NUM))) {
	// 	echo $row[0] . "<br>\n";
	// }


oci_close($conn);
?>