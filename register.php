<?php
	session_start();
	ini_set('display_errors', 'On'); 
	require_once "connection.php";

	if (isset($_POST['username']) && isset($_POST['password'])) {

		$username = $_POST['username'];
		$password = $_POST['password'];
		if (isset($_POST['birthdate'])) {
			$birthdate = $_POST['birthdate'];
		}
		if (isset($_POST['gender'])) {
			$gender = $_POST['gender'];
		}

		// $_SESSION['name'] = $username;
		// $_SESSION['password'] = $password;
		// $_SESSION['birthdate'] = $birthdate;
		// $_SESSION['gender'] = $gender;

		$sql = "select * from users where username='$username'";

		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		if (!oci_fetch_row($stmt) && $username) {
			$sql2 = "insert into users values ('$username','$password','$birthdate','$gender')";
			$stmt = oci_parse($conn, $sql2);
			oci_execute($stmt);

			oci_commit($conn);

			$sql = "select * from users where username='$username' and password='$password'";

			$stmt = oci_parse($conn, $sql);
			oci_execute($stmt);

			$err = oci_error($stmt);
			if ($err) {
				echo $err;
			}

			while ($res = oci_fetch_row($stmt))
			{
				$_SESSION['User'] = $res[0];
				header('Location: index.php');
			}  

		} else if ($username) {
			echo "Sign up failed because the user login already exists. Please try again.";
			header('Location: index.php');
		}
	}
	//oci_commit($conn);

	oci_close($conn);
?>