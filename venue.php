<?php
	session_start();
	ini_set('display_errors', 'On');
	require_once "connection.php";

	$venue_info = array();
	$venues = array();

	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		$venue_info = searchByID($conn, $id);
		$venue_review = searchByVenueReview($conn, $id);
		$venue_info[] = $venue_review;
	} else {
		$id = false;
		$venues = otherwise($conn);
	}

	if (isset($_GET['venue_name'])) {
		$venue_name = $_GET['venue_name'];
		$venue_info = searchByVenueName($conn, $venue_name);
		$venue_review = searchByVenueReview($conn, $venue_info[0]['VENUE_ID']);
		$venue_info[] = $venue_review;
	} else {
		$venue_name = false;
		$venues = otherwise($conn);
	}

	if (isset($_GET['concert_date'])) {
		$concert_date = urldecode($_GET['concert_date']);
		if (strlen($concert_date) > 0) {
			$venues = searchByDate($conn, $concert_date);
		} else {
			$venues = otherwise($conn);
		}
	} else {
		$concert_date = false;
		$venues = otherwise($conn);
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

	function searchByID($conn, $id) {
		$sql = "select * from venues where venue_id='$id'";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_info[] = $res;
		}

		$venue_info['CONCERTS'][] = getVenueConcerts($conn, $venue_info[0]['VENUE_ID']);
		//$venue_info['ARTISTS'][] = getConcertArtists($conn, $venue_info[0]['VENUE_ID']);

		return $venue_info;
	}

	function searchByVenueName($conn, $venue_name) {
		$sql = "select * from venues where name='$venue_name'";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_info[] = $res;
		}
		
		$venue_info['CONCERTS'][] = getVenueConcerts($conn, $venue_info[0]['VENUE_ID']);
		//$venue_info['ARTISTS'][] = getConcertArtists($conn, $venue_info[0]['VENUE_ID']);

		return $venue_info;
	}

	// function searchByDate($conn, $concert_date) {
	// 	$sql = "select * from venues where concert_date='$concert_date'";

	// 	$stmt = performQuery($conn, $sql);

	// 	$venues = "";
		
	// 	while ($res = oci_fetch_row($stmt))
	// 	{
	// 		$venues[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
	// 	}

	// 	return $venues;
	// }

	function searchByVenueReview($conn, $id) {
		$sql = "select username, review from reviews_v where venue_id='$id'";

		$stmt = performQuery($conn, $sql);

		$concert_review = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_review[] = $res;
		}

		return $concert_review;
	}

	function getVenueConcerts($conn, $id) {
		$sql = "select concert_id, name from concerts where venue_id='$id'";

		$stmt = performQuery($conn, $sql);

		$venue_info = "";

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_info[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res['CONCERT_ID']."'>".$res['NAME']."</a></li>";
		}

		return $venue_info;
	}

	// function getConcertArtists($conn, $id) {
	// 	$sql = "select a.artist_id, a.artist_name from performs p, artists a where p.artist_id=a.artist_id and p.concert_id='$id'";

	// 	$stmt = performQuery($conn, $sql);

	// 	$venue_info = "";

	// 	while ($res = oci_fetch_assoc($stmt))
	// 	{
	// 		$venue_info[] = "<li><a href='/~sks2187/w4111/artist.php/?id=".$res['ARTIST_ID']."'>".$res['ARTIST_NAME']."</a></li>";
	// 	}

	// 	return $venue_info;
	// }

	function otherwise($conn) {
		$sql = "select * from venues";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$venues[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		return $venues;
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
						<li class="active"><a href="/~sks2187/w4111/venue.php">Venues</a></li>
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
			<div class="row">
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" placeholder="Venue name" class="form-control" name="venue_name"/>
					</form>
				</div>
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" id="datepicker" placeholder="Concert date" class="form-control" name="concert_date" onchange="this.form.submit();"/>
					</form>
				</div>
			</div>

			<?php
				if (count($venue_info) > 0) {
					echo ("<h3>Venue name: ".$venue_info[0]['NAME']."</h3>");
					echo ("<h5>Address: ".$venue_info[0]['STREET_ADDR']."</h5>");
					echo ("<h5>".$venue_info[0]['CITY'].", ".$venue_info[0]['STATE']." ".$venue_info[0]['ZIP']."</h5>");
					echo ("<h5>Capacity: ".$venue_info[0]['CAPACITY']."</h5>");
					echo ("<h5>Concerts: </h5>");
					echo ("<ul>");
					foreach ($venue_info['CONCERTS'][0] as $x) {
						echo $x;
					}
					echo ("</ul>");
					// echo ("<h5>Artists: </h5>");
					// echo ("<ul>");
					// foreach ($venue_info['ARTISTS'][0] as $x) {
					// 	echo $x;
					// }
					// echo ("</ul>");
					if (count($venue_info[1]) > 0) {
						echo ("<h5>Reviews: </h5><p>Username: ".$venue_info[1][0]['USERNAME']."<p><p>".$venue_info[1][0]['REVIEW']."<p>");
					}
				} else {
					if (isset($venues) && !empty($venues)) {
						foreach($venues as $a) {
							echo $a;
						}
					}
				}
			?>
		</div>
	</body>
</html>