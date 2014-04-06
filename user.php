<?php
	session_start();
	ini_set('display_errors', 'On'); 
	require_once "connection.php";

	if (isset($_POST['password'])) {
		$password = $_POST['password'];
		if (isset($_POST['birthdate'])) {
			$birthdate = $_POST['birthdate'];
		}
		if (isset($_POST['gender'])) {
			$gender = $_POST['gender'];
		}

		$sql = "update users set password='$password', birth_date='$birthdate', gender='$gender' where username='".$_SESSION['User']['USERNAME']."'";

		$stmt = performQuery($conn, $sql);

		$_SESSION['User']['PASSWORD'] = $password;
		$_SESSION['User']['BIRTH_DATE'] = $birthdate;
		$_SESSION['User']['GENDER'] = $gender;
	}

	function performQuery($conn, $sql) {
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		return $stmt;
	}

	oci_close($conn);
?>

<html>
	<head>
		<title>SetList</title>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="jqueryui/css/ui-lightness/jqueryui.css"/>
		<script src="jqueryui/js/jquery.js"></script>
		<script src="jqueryui/js/jqueryui.js"></script>
		<script>
			$(function() {
				$( "#datepicker" ).datepicker();
				$( "#datepicker" ).datepicker( "option", "dateFormat", "dd-M-yy" );
			});
		</script>
	</head>
	
	<body style="margin-top:60px;">
		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				<a class="navbar-brand" href="/~sks2187/w4111/index.php">SetList</a>
			</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="/~sks2187/w4111/index.php">Home</a></li>
						<li><a href="/~sks2187/w4111/artist.php">Artists</a></li>
						<li><a href="/~sks2187/w4111/concert.php">Concerts</a></li>
						<li><a href="/~sks2187/w4111/venue.php">Venues</a></li>
					</ul>
					<ul class="nav navbar-nav pull-right">
						<li><?php
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								echo('<li class="active"><a href="/~sks2187/w4111/user.php">Welcome, '.$_SESSION['User']['USERNAME'].'</a></li>');
								echo('<li><a href="/~sks2187/w4111/logout.php">Log Out</a></li>');
							} else {
								echo('<li><a href="/~sks2187/w4111/login.php">Log In</a></li>');
							}
						?></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
		
		<?php
			if (isset($_SESSION['Error']) && !empty($_SESSION['Error'])) {
				echo '<div class="alert alert-success">'.$_SESSION['Error'].'</div>';
				$_SESSION['Error'] = "";
			}
		?>

		<div class="container">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<div class="panel panel-default">
						<div class="panel-heading">
							Settings
						</div>
						<div class="panel-body">
							<form class="form-horizontal" role="form" method="POST" action="">
							  <div class="form-group">
							    <label for="inputUsername2" class="col-sm-2 control-label">Username</label>
							    <div class="col-sm-10">
							      <input disabled type="text" class="form-control" id="inputUsername2" value="<?php echo $_SESSION['User']['USERNAME']; ?>" placeholder="Username" name="username">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputPassword2" class="col-sm-2 control-label">Password</label>
							    <div class="col-sm-10">
							      <input type="password" class="form-control" id="inputPassword2" value="<?php echo $_SESSION['User']['PASSWORD']; ?>"placeholder="Password" name="password">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputBirthdate" class="col-sm-2 control-label">Birthdate</label>
							    <div class="col-sm-10">
							      <input type="text" id="datepicker" class="form-control" id="inputBirthdate" value="<?php echo $_SESSION['User']['BIRTH_DATE'];?>" placeholder="<?php echo $_SESSION['User']['BIRTH_DATE'];?>" name="birthdate">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputGender" class="col-sm-2 control-label">Gender</label>
							    <div class="col-sm-10">
							      <input type="text" class="form-control" id="inputGender" value="<?php echo $_SESSION['User']['GENDER']; ?>" placeholder="Gender" name="gender">
							    </div>
							  </div>
							  <div class="form-group">
							    <div class="col-sm-offset-2 col-sm-10">
							      <button type="submit" class="btn btn-default">Save</button>
							    </div>
							  </div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>