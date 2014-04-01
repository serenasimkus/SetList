<?php
	session_start();
	ini_set('display_errors', 'On'); 
	require_once "connection.php";

	$stmt = oci_parse($conn, "select * from artists");
	oci_execute($stmt, OCI_DEFAULT);
	$artist = array();
	while ($res = oci_fetch_row($stmt))                                                        
	{
		$artist[] = "<li><a href='artist.php/?id=".$res[0]."'>".$res[1]."</a></li>";
	}
	oci_close($conn);
?>

<html>
	<head>
		<title>SetList</title>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
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
						<li class="active"><a href="/~sks2187/w4111/index.php">Home</a></li>
						<li><a href="/~sks2187/w4111/artist.php">Artists</a></li>
						<li><a href="/~sks2187/w4111/concert.php">Concerts</a></li>
						<li><a href="/~sks2187/w4111/venue.php">Venues</a></li>
					</ul>
					<ul class="nav navbar-nav pull-right">
						<li><?php
							if (count($_SESSION['User']) != 0) {
								echo('<li><a href="#">Welcome, '.$_SESSION['User'].'</a></li>');
								echo('<li><a href="logout.php">Log Out</a></li>');
							} else {
								echo('<li><a href="login.php">Log In</a></li>');
							}
						?></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
		
		<div class="container">
			<h3>Welcome to SetList!</h3>
			<?php
				foreach($artist as $a) {
					echo $a;
				}
			?>
		</div>
	</body>
</html>
