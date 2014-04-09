<?php
	session_start();
	ini_set('display_errors', 'On'); 
	require_once "connection.php";

	if (isset($_POST['username']) && isset($_POST['password'])) {

		$username = $_POST['username'];
		$password = $_POST['password'];

		$sql = "select * from users where username='$username' and password='$password'";

		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		while ($res = oci_fetch_assoc($stmt))
		{
			$_SESSION['User'] = $res;
			header('Location: index.php');
		}
		$_SESSION['Error'] = "Login failed";
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
	
	<body style="margin-top:50px;">
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
						<li class="active"><a href="/~sks2187/w4111/index.php">Home</a></li>
						<li><a href="/~sks2187/w4111/artist.php">Artists</a></li>
						<li><a href="/~sks2187/w4111/concert.php">Concerts</a></li>
						<li><a href="/~sks2187/w4111/venue.php">Venues</a></li>
					</ul>
					<ul class="nav navbar-nav pull-right">
						<li><a href="/~sks2187/w4111/login.php">Login</a></li>
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
				<div class="col-md-6">
					<div class="panel panel-default">
						<div class="panel-heading">
							Login
						</div>
						<div class="panel-body">
							<form class="form-horizontal" role="form" method="POST" action="">
							  <div class="form-group">
							    <label for="inputUsername" class="col-sm-2 control-label">Username</label>
							    <div class="col-sm-10">
							      <input type="text" class="form-control" id="inputUsername" placeholder="Username" name="username">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputPassword" class="col-sm-2 control-label">Password</label>
							    <div class="col-sm-10">
							      <input type="password" class="form-control" id="inputPassword" placeholder="Password" name="password">
							    </div>
							  </div>
							  <div class="form-group">
							    <div class="col-sm-offset-2 col-sm-10">
							      <button type="submit" class="btn btn-default">Sign in</button>
							    </div>
							  </div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="panel panel-default">
						<div class="panel-heading">
							Register
						</div>
						<div class="panel-body">
							<form class="form-horizontal" role="form" method="POST" action="register.php">
							  <div class="form-group">
							    <label for="inputUsername2" class="col-sm-2 control-label">Username</label>
							    <div class="col-sm-10">
							      <input type="text" class="form-control" id="inputUsername2" placeholder="Username" name="username">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputPassword2" class="col-sm-2 control-label">Password</label>
							    <div class="col-sm-10">
							      <input type="password" class="form-control" id="inputPassword2" placeholder="Password" name="password">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputBirthdate" class="col-sm-2 control-label">Birthdate</label>
							    <div class="col-sm-10">
							      <input type="text" id="datepicker" class="form-control" id="inputBirthdate" placeholder="DD-MON-YYYY" name="birthdate">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="inputGender" class="col-sm-2 control-label">Gender</label>
							    <div class="col-sm-10">
							      <input type="text" class="form-control" id="inputGender" placeholder="Gender" name="gender">
							    </div>
							  </div>
							  <div class="form-group">
							    <div class="col-sm-offset-2 col-sm-10">
							      <button type="submit" class="btn btn-default">Sign in</button>
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