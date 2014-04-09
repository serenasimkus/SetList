<?php
	session_start();
	ini_set('display_errors', 'On'); 
	require_once "connection.php";

	$attending = array();
	$concert_reviews = array();
	$venue_reviews = array();

	if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
		$username = $_SESSION['User']['USERNAME'];
		$attending = getAttending($conn, $username);
		$concert_reviews = getConcertReviews($conn, $username);
		$venue_reviews = getVenueReviews($conn, $username);
	} else {
		$attending = false;
		$concert_reviews = false;
		$venue_reviews = false;
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

	function getAttending($conn, $username) {
		$sql = "select * from plans_to_attend p, concerts c where p.concert_id=c.concert_id and p.username='$username'";

		$stmt = performQuery($conn, $sql);

		$attending = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$attending[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res['CONCERT_ID']."'>".$res['NAME']."</a></li>";
		}

		return $attending;
	}

	function getConcertReviews($conn, $username) {
		$sql = "select * from reviews_c r, concerts c where r.concert_id=c.concert_id and p.username='$username'";

		$stmt = performQuery($conn, $sql);

		$concert_reviews = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_reviews[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res['CONCERT_ID']."'>".$res['NAME']."</a></li>";
		}

		return $concert_reviews;
	}

	function getVenueReviews($conn, $username) {
		$sql = "select * from reviews_v r, venues v where r.venue_id=v.venue_id and r.username='$username'";

		$stmt = performQuery($conn, $sql);

		$venue_reviews = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_reviews[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res['VENUE_ID']."'>".$res['NAME']."</a></li>";
		}

		return $venue_reviews;
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
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								echo('<li><a href="/~sks2187/w4111/user.php">Welcome, '.$_SESSION['User']['USERNAME'].'</a></li>');
								echo('<li><a href="/~sks2187/w4111/logout.php">Log Out</a></li>');
							} else {
								echo('<li><a href="/~sks2187/w4111/login.php">Log In</a></li>');
							}
						?></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>
		
		<div class="container">
			<h3>Welcome to SetList!</h3>
				<div class="col-md-4">
					<ul class="nav nav-pills nav-stacked">
						<?php
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								echo ("<h4>Concerts Attending:</h4>");
								foreach($attending as $a) {
									echo $a;
								}
								echo ("<h4>Reviews For:</h4>");
								echo ("<h5>Concerts:</h5>");
								foreach($concert_reviews as $c) {
									echo $c;
								}
								echo ("<h5>Venues:</h5>");
								foreach($venue_reviews as $v) {
									echo $v;
								}
							} else {
								echo ("<a href='/~sks2187/w4111/login.php' style='margin-bottom: 10;' class='btn btn-info'>Sign in to See Your Information</a>");
							}
						?>


					</ul>
				</div>

				<div class="col-md-8">

				</div> 
		</div>
	</body>
</html>
