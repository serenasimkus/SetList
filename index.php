<?php
	session_start();
	ini_set('display_errors', 'On'); 
	require_once "connection.php";

	if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
		$username = $_SESSION['User']['USERNAME'];
		$attending = getAttending($conn, $username);
		$concert_reviews = getSidebarConcertReviews($conn, $username);
		$venue_reviews = getSidebarVenueReviews($conn, $username);
		$genreRecommendations = getGenreRecommendations($conn, $username);
		//$venueRecommendations = getVenueRecommendations($conn, $username);
	} else {
		$attending = false;
		$concert_reviews = false;
		$venue_reviews = false;
		$genreRecommendations = false;
		//$venueRecommendations = false;
	}

	function getGenreRecommendations($conn, $username) {
		$sql = "select distinct(x.concert_id), x.name
				from (select a.genre, c.concert_id, c.name 
					  from artists a, performs p, concerts c 
					  where c.concert_id=p.concert_id and p.artist_id=a.artist_id) x
				where x.concert_id not in (select l.concert_id
										   from plans_to_attend l, (select a.artist_id, a.genre, p.concert_id 
										   							from artists a, performs p 
										   							where a.artist_id=p.artist_id) y
										   where l.username='serenasimkus' and l.concert_id=y.concert_id) 
					and x.genre in (select y.genre
									from plans_to_attend l, (select a.artist_id, a.genre, p.concert_id 
															 from artists a, performs p 
															 where a.artist_id=p.artist_id) y
									where l.username='serenasimkus' and l.concert_id=y.concert_id)";
		
		$stmt = performQuery($conn, $sql);

		$genreRecommendations = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$genreRecommendations[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res['CONCERT_ID']."'>".$res['NAME']."</a></li>";
		}

		return $genreRecommendations;
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

		<div class="col-md-9" style="overflow: scroll; height: 100%;" >
			<div class="row">
				<div class="col-md-12">
					<h3>Welcome to SetList!</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<h4>Recommendations Based on Artist Genre</h4>
					<ul>
						<?php
							if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
								if (!empty($genreRecommendations)) {
									foreach($genreRecommendations as $a) {
										echo $a;
									}
								} else {
									echo ("<a href='/~sks2187/w4111/concert.php' class='btn btn-info'>Find some concerts to attend!</a>");
								}
							} else {
								echo ("<a href='/~sks2187/w4111/login.php' style='display: block; margin-top: 10px;' class='btn btn-info'>Login to attend concerts!</a>");
							}
						?>
					</ul>
				</div>
				<div class="col-md-6">
					<h4>Recommendations Based on Venue Locations</h4>
					<ul>
						<?php
							// if (isset($_SESSION['User']) && !empty($_SESSION['User'])) {
							// 	if (!empty($venueRecommendations)) {
							// 		foreach($venueRecommendations as $a) {
							// 			echo $a;
							// 		}
							// 	} else {
							// 		echo ("<a href='/~sks2187/w4111/concert.php' class='btn btn-info'>Find some concerts to attend!</a>");
							// 	}
							// } else {
							// 	echo ("<a href='/~sks2187/w4111/login.php' style='display: block; margin-top: 10px;' class='btn btn-info'>Login to attend concerts!</a>");
							// }
						?>
					</ul>
				</div>
			</div>
		</div> 
	</body>
</html>
