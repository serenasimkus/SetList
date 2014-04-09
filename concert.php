<?php
	session_start();
	ini_set('display_errors', 'On');
	require_once "connection.php";

	$concert_info = array();
	$concerts = otherwise($conn);

	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		$concert_info = searchByID($conn, $id);
	} else {
		$id = false;
	}

	if (isset($_GET['concert_name'])) {
		$concert_name = $_GET['concert_name'];
		$concert_info = searchByConcertName($conn, $concert_name);
	} else {
		$concert_name = false;
	}

	if (isset($_GET['concert_date'])) {
		$concert_date = urldecode($_GET['concert_date']);
		if (strlen($concert_date) > 0) {
			$concerts = searchByDate($conn, $concert_date);
		} else {
			$concerts = otherwise($conn);
		}
	} else {
		$concert_date = false;
	}

	if (isset($_POST['plans_to_attend'])) {
		$plans_to_attend = $_POST['plans_to_attend'];
		attendByConcertID($conn, $plans_to_attend);
		$concert_info = searchByID($conn, $plans_to_attend);
	} else {
		$plans_to_attend = false;
	}

	if (isset($_POST['not_attending'])) {
		$not_attending = $_POST['not_attending'];
		notAttendByConcertID($conn, $not_attending);
		$concert_info = searchByID($conn, $not_attending);
	} else {
		$not_attending = false;
	}

	if (isset($_POST['add_review']) && isset($_POST['concert_review'])) {
		$add_review = $_POST['add_review'];
		$concert_review = $_POST['concert_review'];
		addReview($conn, $add_review, $concert_review);
		$concert_info = searchByID($conn, $add_review);
	} else {
		$add_review = false;
		$concert_review = false;
	}

	if (isset($_POST['no_review'])) {
		$no_review = $_POST['no_review'];
		deleteReview($conn, $no_review);
		$concert_info = searchByID($conn, $no_review);
	} else {
		$no_review = false;
	}

	if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
		$username = $_SESSION['User']['USERNAME'];
		$attending = getAttending($conn, $username);
		$concert_reviews = getSidebarConcertReviews($conn, $username);
		$venue_reviews = getSidebarVenueReviews($conn, $username);
	} else {
		$attending = false;
		$concert_reviews = false;
		$venue_reviews = false;
	}

	function searchByID($conn, $id) {
		$sql = "select * from concerts where concert_id='$id'";

		$stmt = performQuery($conn, $sql);

		$concert_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_info[] = $res;
		}

		if ($concert_info) {
			$concert_info['VENUE'][] = getConcertVenue($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['ARTISTS'][] = getConcertArtists($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['ATTENDING'][] = checkAttending($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['REVIEWED'][] = checkReview($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['REVIEW'][] = getConcertReviews($conn, $concert_info[0]['CONCERT_ID']);
		} else {
			$_SESSION['Error'] = "Concert does not exist";
		}

		return $concert_info;
	}

	function searchByConcertName($conn, $concert_name) {
		$sql = "select * from concerts where name='$concert_name'";

		$stmt = performQuery($conn, $sql);

		$concert_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_info[] = $res;
		}
		
		if ($concert_info) {
			$concert_info['VENUE'][] = getConcertVenue($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['ARTISTS'][] = getConcertArtists($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['ATTENDING'][] = checkAttending($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['REVIEWED'][] = checkReview($conn, $concert_info[0]['CONCERT_ID']);
			$concert_info['REVIEW'][] = getConcertReviews($conn, $concert_info[0]['CONCERT_ID']);
		} else {
			$_SESSION['Error'] = "Concert does not exist";
		}

		return $concert_info;
	}

	function checkAttending($conn, $plans_to_attend) {
		if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
			$sql = "select * from plans_to_attend where concert_id='$plans_to_attend' and username='".$_SESSION['User']['USERNAME']."'";

			$stmt = performQuery($conn, $sql);

			$concert_info = array();

			while ($res = oci_fetch_assoc($stmt))
			{
				$concert_info[] = $res;
			}

			return $concert_info;
		}
	}

	function attendByConcertID($conn, $plans_to_attend) {
		if (!checkAttending($conn, $plans_to_attend)) {
			$sql = "insert into plans_to_attend values ('".$_SESSION['User']['USERNAME']."',".$plans_to_attend.")";

			$stmt = performQuery($conn, $sql);

			oci_commit($conn);
		}
	}

	function notAttendByConcertID($conn, $not_attending) {
		if (checkAttending($conn, $not_attending)) {
			$sql = "delete from plans_to_attend where username='".$_SESSION['User']['USERNAME']."' and concert_id=".$not_attending."";

			$stmt = performQuery($conn, $sql);

			oci_commit($conn);
		}
	}

	function checkReview($conn, $add_review) {
		if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
			$sql = "select * from reviews_c where concert_id='$add_review' and username='".$_SESSION['User']['USERNAME']."'";

			$stmt = performQuery($conn, $sql);

			$concert_info = array();

			while ($res = oci_fetch_assoc($stmt))
			{
				$concert_info[] = $res;
			}

			return $concert_info;
		}
	}

	function addReview($conn, $add_review, $concert_review) {
		if (!checkReview($conn, $add_review)) {
			$sql = "insert into reviews_c values ('".$_SESSION['User']['USERNAME']."',".$add_review.",'".$concert_review."')";

			$stmt = performQuery($conn, $sql);

			oci_commit($conn);
		}
	}

	function deleteReview($conn, $no_review) {
		if (checkReview($conn, $no_review)) {
			$sql = "delete from reviews_c where username='".$_SESSION['User']['USERNAME']."' and concert_id=".$no_review."";

			$stmt = performQuery($conn, $sql);

			oci_commit($conn);
		}
	}

	function searchByDate($conn, $concert_date) {
		$sql = "select * from concerts where concert_date='$concert_date'";

		$stmt = performQuery($conn, $sql);

		$concerts = array();
		
		while ($res = oci_fetch_row($stmt))
		{
			$concerts[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		if (!$concerts) {
			$_SESSION['Error'] = "No concerts on this date, try another date.";
		}

		return $concerts;
	}

	function getConcertReviews($conn, $id) {
		$sql = "select username, review from reviews_c where concert_id='$id'";

		$stmt = performQuery($conn, $sql);

		$concert_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_info[] = $res;
		}

		return $concert_info;
	}

	function getConcertVenue($conn, $id) {
		$sql = "select v.venue_id, v.name from venues v, concerts c where c.venue_id=v.venue_id and c.concert_id='$id'";

		$stmt = performQuery($conn, $sql);

		$concert_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_info[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res['VENUE_ID']."'>".$res['NAME']."</a></li>";
		}

		return $concert_info;
	}

	function getConcertArtists($conn, $id) {
		$sql = "select a.artist_id, a.artist_name from performs p, artists a where p.artist_id=a.artist_id and p.concert_id='$id'";

		$stmt = performQuery($conn, $sql);

		$concert_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_info[] = "<li><a href='/~sks2187/w4111/artist.php/?id=".$res['ARTIST_ID']."'>".$res['ARTIST_NAME']."</a></li>";
		}

		return $concert_info;
	}

	function otherwise($conn) {
		$sql = "select * from concerts";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$concerts[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		return $concerts;
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
						<li><a href="/~sks2187/w4111/index.php">Home</a></li>
						<li><a href="/~sks2187/w4111/artist.php">Artists</a></li>
						<li class="active"><a href="/~sks2187/w4111/concert.php">Concerts</a></li>
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

		<?php
			if (isset($_SESSION['Error']) && !empty($_SESSION['Error'])) {
				echo '<div class="alert alert-success">'.$_SESSION['Error'].'</div>';
				$_SESSION['Error'] = "";
			}
		?>

		<div class="col-md-3" style="background-color: #222; height: 100%; overflow: scroll; padding-bottom: 20px;">
			<ul class="nav nav-pills nav-stacked">
				<?php
					if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
						echo ("<h4 style='color: #FFF;'>Concerts Attending:</h4>");
						if (!empty($attending)) {
							foreach($attending as $a) {
								echo $a;
							}
						} else {
							echo ("<a href='/~sks2187/w4111/concert.php' class='btn btn-info'>Find some concerts to attend!</a>");
						}
						echo ("<h4 style='color: #FFF;'>Reviews For:</h4>");
						echo ("<h5 style='color: #FFF;'>Concerts:</h5>");
						if (!empty($concert_reviews)) {
							foreach($concert_reviews as $c) {
								echo $c;
							}
						} else {
							echo ("<a href='/~sks2187/w4111/concert.php' class='btn btn-info'>Review a concert?</a>");
						}
						echo ("<h5 style='color: #FFF;'>Venues:</h5>");
						if (!empty($venue_reviews)) {
							foreach($venue_reviews as $v) {
								echo $v;
							}
						} else {
							echo ("<a href='/~sks2187/w4111/venue.php' class='btn btn-info'>Review a venue?</a>");
						}
					} else {
						echo ("<a href='/~sks2187/w4111/login.php' style='display: block; margin-top: 10px;' class='btn btn-info'>Login to See Your Information</a>");
					}
				?>
			</ul>
		</div>
		
		<div class="col-md-9" style="overflow: scroll; height: 100%;">
			<h3>Welcome to SetList!</h3>
			<div class="row">
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" placeholder="Concert name" class="form-control" name="concert_name"/>
					</form>
				</div>
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" id="datepicker" placeholder="Concert date" class="form-control" name="concert_date" onchange="this.form.submit();"/>
					</form>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<?php
						if (count($concert_info) > 0) {
							echo ("<h3 style='display: inline-block; vertical-align: sub;'>Concert name: ".$concert_info[0]['NAME']."</h3>");
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								if (!empty($concert_info['ATTENDING'][0])) {
									echo ("&nbsp;<form style='display: inline-block;' method='POST' action=''><button class='btn btn-danger' \
										type='submit'>Not Attending</button><input type='text' name='not_attending' hidden value='".$concert_info[0]['CONCERT_ID']."'/></form>");
								} else {
									echo ("&nbsp;<form style='display: inline-block;' method='POST' action=''><button class='btn btn-info' \
										type='submit'>Attending?</button><input type='text' name='plans_to_attend' hidden value='".$concert_info[0]['CONCERT_ID']."'/></form>");
								}
							} else {
								echo("&nbsp;<a href='/~sks2187/w4111/login.php' class='btn btn-info'>Sign in to Attend</a>");
							}
							echo ("<h4>Date: ".$concert_info[0]['CONCERT_DATE']."</h4>");
							echo ("<h5>Start time: ".$concert_info[0]['START_TIME']."</h5>");
							echo ("<h5>Venue: </h5>");
							echo ("<ul>");
							foreach ($concert_info['VENUE'][0] as $x) {
								echo $x;
							}
							echo ("</ul>");
							echo ("<h5>Artists: </h5>");
							echo ("<ul>");
							if (isset($concerts) && !empty($concerts)) {
								foreach ($concert_info['ARTISTS'][0] as $x) {
									echo $x;
								}
							}
							echo ("</ul>");
							echo ("<h5>Reviews: </h5>");
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								if (!empty($concert_info['REVIEWED'][0])) {
									echo ("<form style='display: inline-block;' method='POST' action=''><button class='btn btn-danger' \
										type='submit'>Delete Review?</button><input type='text' name='no_review' hidden value='".$concert_info[0]['CONCERT_ID']."'/></form>");
								} else {
									echo ("<form class='form-horizontal' role='form' method='POST' action=''><div>");
									echo ("<button class='btn btn-info' style='margin-bottom: 5;' type='submit'>Save Review</button><input type='text' \
										name='add_review' hidden value='".$concert_info[0]['CONCERT_ID']."'/>");
									echo ("<input type='text' placeholder='Concert review' class='form-control' name='concert_review'/>");
									echo ("</div></form>");
								}
							} else {
								echo ("<a href='/~sks2187/w4111/login.php' style='margin-bottom: 10;' class='btn btn-info'>Sign in to Leave a Review</a>");
							}
							echo ("<ul>");
							if (isset($concerts) && !empty($concerts)) {
								foreach ($concert_info['REVIEW'][0] as $x) {
									echo ("<p>Username: ".$x['USERNAME']."<p><p>".$x['REVIEW']."<p><hr size=4>");
								}
							}
							echo ("</ul>");
						} else {
							if (isset($concerts) && !empty($concerts)) {
								foreach($concerts as $a) {
									echo $a;
								}
							}
						}
					?>
				</div>
			</div>
		</div>
	</body>
</html>