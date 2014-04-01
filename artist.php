<?php
	session_start();
	ini_set('display_errors', 'On');
	require_once "connection.php";
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
	} else {
		$id = false;
	}

	if (isset($_GET['artist_name'])) {
		$artist_name = $_GET['artist_name'];
	} else {
		$artist_name = false;
	}

	if (isset($_GET['genre'])) {
		$genre = urldecode($_GET['genre']);
		// search_by_genre();
	} else {
		$genre = false;
	}

	$sql = "select genre from artists";

	$stmt = oci_parse($conn, $sql);

	oci_execute($stmt);

	$options = array();

	while ($res = oci_fetch_row($stmt))                                                        
	{
		if ($genre == $res[0]) {
			$options[] = "<option selected value=".urlencode($res[0]).">".$res[0]."</option>";
		} else {
			$options[] = "<option value=".urlencode($res[0]).">".$res[0]."</option>";
		}
	}

	$options = array_unique($options);

	if ($id) {
		$sql = "select * from artists where artist_id='$id'";

		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		$artist_info = array();

		while ($res = oci_fetch_assoc($stmt))                                                        
		{
			$artist_info[] = $res;
		}  
	} elseif ($artist_name) {
		$sql = "select * from artists where artist_name='$artist_name'";

		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		$artist_info = array();

		while ($res = oci_fetch_assoc($stmt))                                                        
		{
			$artist_info[] = $res;
		}  
	} elseif ($genre) {
		$sql = "select * from artists where genre='$genre'";

		$stmt = oci_parse($conn, $sql);
		oci_execute($stmt);

		$err = oci_error($stmt);
		if ($err) {
			echo $err;
		}

		$artists = array();

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$artists[] = "<li><a href='artist.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}  
	} else {
		$sql = "select * from artists";

		$stmt = oci_parse($conn, $sql);

		oci_execute($stmt);

		$artists = array();

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$artists[] = "<li><a href='artist.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}
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
			<div class="row">
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" placeholder="Artist name" class="form-control" name="artist_name"/>
					</form>
				</div>
				<div class="col-md-6">
					<form method="GET" action="">
						<select name="genre" onchange="this.form.submit();">

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
				if (isset($artist_info)) {
					echo ("<h3>Artist name: ".$artist_info[0]['ARTIST_NAME']."</h3>");
					echo ("<h4>Genre: ".$artist_info[0]['GENRE']."</h4>");
					echo ("<p>Bio: ".$artist_info[0]['BIO']."<p>");
				} else {
					foreach($artists as $a) {
						echo $a;
					}
				}
			?>
		</div>
	</body>
</html>