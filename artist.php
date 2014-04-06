<?php
	session_start();
	ini_set('display_errors', 'On');
	require_once "connection.php";
	
	$artist_info = array();
	$artists = otherwise($conn);

	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		$artist_info = searchByID($conn, $id);
	} else {
		$id = false;
	}

	if (isset($_GET['artist_name'])) {
		$artist_name = $_GET['artist_name'];
		$artist_info = searchByArtistName($conn, $artist_name);
	} else {
		$artist_name = false;
	}

	if (isset($_GET['genre'])) {
		$genre = urldecode($_GET['genre']);
		if (strlen($genre) > 0) {
			$artists = searchByGenre($conn, $genre);
		} else {
			$artists = otherwise($conn);
		}
	} else {
		$genre = false;
	}

	$options = getGenres($conn, $genre);

	function performQuery($conn, $sql) {
		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		return $stmt;
	}

	function getGenres($conn, $genre) {
		$sql = "select genre from artists";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			if ($genre == $res[0]) {
				$options[] = "<option selected value=".urlencode($res[0]).">".$res[0]."</option>";
			} else {
				$options[] = "<option value=".urlencode($res[0]).">".$res[0]."</option>";
			}
		}

		$options = array_unique($options);

		return $options;
	}

	function searchByID($conn, $id) {
		$sql = "select * from artists where artist_id='$id'";

		$stmt = performQuery($conn, $sql);

		$artist_info = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$artist_info[] = $res;
		}

		if ($artist_info) {
			$artist_info['SONGS'][] = getArtistSongs($conn, $artist_info[0]['ARTIST_ID']);
			$artist_info['CONCERTS'][] = getArtistConcerts($conn, $artist_info[0]['ARTIST_ID']);
		} else {
			$_SESSION['Error'] = "Artist does not exist";
		}

		return $artist_info;
	}

	function searchByArtistName($conn, $artist_name) {
		$sql = "select * from artists where artist_name='$artist_name'";

		$stmt = performQuery($conn, $sql);

		$artist_info = array();

		while ($res = oci_fetch_assoc($stmt))                                                        
		{
			$artist_info[] = $res;
		}

		if ($artist_info) {
			$artist_info['SONGS'][] = getArtistSongs($conn, $artist_info[0]['ARTIST_ID']);
			$artist_info['CONCERTS'][] = getArtistConcerts($conn, $artist_info[0]['ARTIST_ID']);
		} else {
			$_SESSION['Error'] = "Artist does not exist";
		}

		return $artist_info;
	}

	function searchByGenre($conn, $genre) {
		$sql = "select * from artists where genre='$genre'";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$artists[] = "<li><a href='/~sks2187/w4111/artist.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		return $artists;
	}

	function getArtistConcerts($conn, $id) {
		$sql = "select * from concerts c, performs p where c.concert_id=p.concert_id and p.artist_id='$id'";

		$stmt = performQuery($conn, $sql);

		$artist_info = array();

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$artist_info[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		return $artist_info;
	}

	function getArtistSongs($conn, $id) {
		$sql = "select * from songs s, created_by c where s.song_id=c.song_id and c.artist_id='$id'";

		$stmt = performQuery($conn, $sql);

		$artist_info = array();

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$artist_info[] = "<li>".$res[1]."</li>";
		}

		return $artist_info;
	}

	function otherwise($conn) {
		$sql = "select * from artists";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$artists[] = "<li><a href='/~sks2187/w4111/artist.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		return $artists;
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
						<li><a href="/~sks2187/w4111/index.php">Home</a></li>
						<li class="active"><a href="/~sks2187/w4111/artist.php">Artists</a></li>
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

		<?php
			if (isset($_SESSION['Error']) && !empty($_SESSION['Error'])) {
				echo '<div class="alert alert-success">'.$_SESSION['Error'].'</div>';
				$_SESSION['Error'] = "";
			}
		?>
		
		<div class="container">
			<h3>Welcome to SetList!</h3>
			<div class="row">
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" placeholder="Artist name" class="form-control" name="artist_name"/>
					</form>
				</div>
				<div class="col-md-6">
					<form method="GET" action="">
						<select class="form-control" name="genre" onchange="this.form.submit();">
							<option value="" default>Select Genre</option>
							<?php
								foreach($options as $a) {
									echo $a;
								}
							?>
						</select>
					</form>
				</div>
			</div>

			<?php
				if (count($artist_info) > 0) {
					echo ("<h3>Artist name: ".$artist_info[0]['ARTIST_NAME']."</h3>");
					echo ("<h4>Genre: ".$artist_info[0]['GENRE']."</h4>");
					echo ("<p>Bio: ".$artist_info[0]['BIO']."<p>");
					echo ("<h4>Concerts: </h4>");
					echo ("<ul>");
					foreach ($artist_info['CONCERTS'][0] as $x) {
						echo $x;
					}
					echo ("</ul>");
					echo ("<h4>Songs: </h4>");
					echo ("<ul>");
					foreach ($artist_info['SONGS'][0] as $x) {
						echo $x;
					}
					echo ("</ul>");
				} else {
					foreach ($artists as $a) {
						echo $a;
					}
				}
			?>
		</div>
	</body>
</html>