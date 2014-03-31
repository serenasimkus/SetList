<?php
	session_start();
	unset($_SESSION[‘User’];
	header('Location: login.php');
?>