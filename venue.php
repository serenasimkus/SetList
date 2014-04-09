<?php
	session_start();
	ini_set('display_errors', 'On');
	require_once "connection.php";

	$venue_info = array();
	$venues = otherwise($conn);
	$options = array("AL", "AK", "AZ", "AR", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "HI", "ID", "IL", "IN", "IA", "KS", "KY", "LA", "ME", "MD", "MA", "MI", "MN", "MS", "MO", "MT", "NE", "NV", "NH", "NJ", "NM", "NY", "NC", "ND", "OH", "OK", "OR", "PA", "RI", "SC", "SD", "TN", "TX", "UT", "VT", "VA", "WA", "WV", "WI", "WY");

	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		$venue_info = searchByID($conn, $id);
	} else {
		$id = false;
	}

	if (isset($_GET['venue_name'])) {
		$venue_name = $_GET['venue_name'];
		$venue_info = searchByVenueName($conn, $venue_name);
	} else {
		$venue_name = false;
	}

	if (isset($_GET['city'])) {
		$city = $_GET['city'];
		if (strlen($city) > 0) {
			$venues = searchByCity($conn, $city);
		} else {
			$venues = otherwise($conn);
		}
	} else {
		$city = false;
	}

	if (isset($_GET['state'])) {
		$state = $_GET['state'];
		if (strlen($state) > 0) {
			$venues = searchByState($conn, $state);
		} else {
			$venues = otherwise($conn);
		}
	} else {
		$state = false;
	}

	if (isset($_GET['zip'])) {
		$zip = $_GET['zip'];
		if (strlen($zip) > 0) {
			$venues = searchByZip($conn, $zip);
		} else {
			$venues = otherwise($conn);
		}
	} else {
		$zip = false;
	}

	if (isset($_POST['add_review']) && isset($_POST['venue_review'])) {
		$add_review = $_POST['add_review'];
		$venue_review = $_POST['venue_review'];
		addReview($conn, $add_review, $venue_review);
		$venue_info = searchByID($conn, $add_review);
	} else {
		$add_review = false;
		$venue_review = false;
	}

	if (isset($_POST['no_review'])) {
		$no_review = $_POST['no_review'];
		deleteReview($conn, $no_review);
		$venue_info = searchByID($conn, $no_review);
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
		$sql = "select * from venues where venue_id='$id'";

		$stmt = performQuery($conn, $sql);

		$venue_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_info[] = $res;
		}

		if ($venue_info) {
			$venue_info['CONCERTS'][] = getVenueConcerts($conn, $venue_info[0]['VENUE_ID']);
			$venue_info['REVIEWED'][] = checkReview($conn, $venue_info[0]['VENUE_ID']);
			$venue_info['REVIEW'][] = getVenueReviews($conn, $venue_info[0]['VENUE_ID']);
		} else {
			$_SESSION['Error'] = "Venue does not exist";
		}

		return $venue_info;
	}

	function searchByVenueName($conn, $venue_name) {
		$sql = "select * from venues where name='$venue_name'";

		$stmt = performQuery($conn, $sql);

		$venue_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_info[] = $res;
		}
		
		if ($venue_info) {
			$venue_info['CONCERTS'][] = getVenueConcerts($conn, $venue_info[0]['VENUE_ID']);
			$venue_info['REVIEWED'][] = checkReview($conn, $venue_info[0]['VENUE_ID']);
			$venue_info['REVIEW'][] = getVenueReviews($conn, $venue_info[0]['VENUE_ID']);
		} else {
			$_SESSION['Error'] = "Venue does not exist";
		}

		return $venue_info;
	}

	function checkReview($conn, $add_review) {
		if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
			$sql = "select * from reviews_v where venue_id='$add_review' and username='".$_SESSION['User']['USERNAME']."'";

			$stmt = performQuery($conn, $sql);

			$venue_info = array();

			while ($res = oci_fetch_assoc($stmt))
			{
				$venue_info[] = $res;
			}

			return $venue_info;
		}
	}

	function addReview($conn, $add_review, $venue_review) {
		if (!checkReview($conn, $add_review)) {
			$sql = "insert into reviews_v values ('".$_SESSION['User']['USERNAME']."',".$add_review.",'".$venue_review."')";

			$stmt = performQuery($conn, $sql);

			oci_commit($conn);
		}
	}

	function deleteReview($conn, $no_review) {
		if (checkReview($conn, $no_review)) {
			$sql = "delete from reviews_v where username='".$_SESSION['User']['USERNAME']."' and venue_id=".$no_review."";

			$stmt = performQuery($conn, $sql);

			oci_commit($conn);
		}
	}

	function searchByCity($conn, $city) {
		$sql = "select * from venues where city='$city'";

		$stmt = performQuery($conn, $sql);

		$venues = "";
		
		while ($res = oci_fetch_row($stmt))
		{
			$venues[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		if (!$venues) {
			$_SESSION['Error'] = "No venues in this city, try another city.";
		}

		return $venues;
	}

	function searchByState($conn, $state) {
		$sql = "select * from venues where state='$state'";

		$stmt = performQuery($conn, $sql);

		$venues = "";
		
		while ($res = oci_fetch_row($stmt))
		{
			$venues[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		if (!$venues) {
			$_SESSION['Error'] = "No venues in this state, try another state.";
		}

		return $venues;
	}

	function searchByZip($conn, $zip) {
		$sql = "select * from venues where zip='$zip'";

		$stmt = performQuery($conn, $sql);

		$venues = "";
		
		while ($res = oci_fetch_row($stmt))
		{
			$venues[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		if (!$venues) {
			$_SESSION['Error'] = "No venues in this zip code, try another zip code.";
		}

		return $venues;
	}

	function getVenueReviews($conn, $id) {
		$sql = "select username, review from reviews_v where venue_id='$id'";

		$stmt = performQuery($conn, $sql);

		$venue_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_info[] = $res;
		}

		return $venue_info;
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

	function otherwise($conn) {
		$sql = "select * from venues";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$venues[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res[0]."'>".$res[1]."</a></li>";
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
			<?php
				if (isset($_SESSION['Error']) && !empty($_SESSION['Error'])) {
					echo '<div class="alert alert-success">'.$_SESSION['Error'].'</div>';
					$_SESSION['Error'] = "";
				}
			?>
			<h3>Welcome to SetList!</h3>
			<div class="row">
				<div class="col-md-4">
					<form method="GET" action="">
						<input type="text" placeholder="Venue name" class="form-control" name="venue_name"/>
					</form>
				</div>
				<div class="col-md-4">
					<form method="GET" action="">
						<input type="text" placeholder="City" class="form-control" name="city"/>
					</form>
				</div>
				<div class="col-md-2">
					<form method="GET" action="">
						<select class="form-control" name="state" onchange="this.form.submit();">
							<option value="" default>Select State</option>
							<?php
								foreach($options as $a) {
									echo "<option value=".$a.">".$a."</option>";
								}
							?>
						</select>
					</form>
				</div>
				<div class="col-md-2">
					<form method="GET" action="">
						<input type="text" placeholder="Zip code" class="form-control" name="zip"/>
					</form>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
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
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								if (!empty($venue_info['REVIEWED'][0])) {
									echo ("<form style='display: inline-block;' method='POST' action=''><button class='btn btn-danger' type='submit'>Delete Review?</button><input type='text' name='no_review' hidden value='".$venue_info[0]['VENUE_ID']."'/></form>");
								} else {
									echo ("<form class='form-horizontal' role='form' method='POST' action=''><div>");
									echo ("<button class='btn btn-info' style='margin-bottom: 5;' type='submit'>Save Review</button><input type='text' \
										name='add_review' hidden value='".$venue_info[0]['VENUE_ID']."'/>");
									echo ("<input type='text' placeholder='Venue review' class='form-control' name='venue_review'/>");
									echo ("</div></form>");
								}
							} else {
								echo ("<a href='/~sks2187/w4111/login.php' style='margin-bottom: 10;' class='btn btn-info'>Sign in to Leave a Review</a>");
							}
							echo ("<ul>");
							if (isset($venues) && !empty($venues)) {
								foreach ($venue_info['REVIEW'][0] as $x) {
									echo ("<p>Username: ".$x['USERNAME']."<p><p>".$x['REVIEW']."<p><hr size=4>");
								}
							}
							echo ("</ul>");
						} else {
							if (isset($venues) && !empty($venues)) {
								foreach($venues as $a) {
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