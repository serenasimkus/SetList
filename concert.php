<?php
	session_start();
	ini_set('display_errors', 'On');
	require_once "connection.php";

	$concert_info = array();
	$concerts = array();

	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		$concert_info = searchByID($conn, $id);
	} else {
		$id = false;
		$concerts = otherwise($conn);
	}

	if (isset($_GET['concert_name'])) {
		$concert_name = $_GET['concert_name'];
		$concert_info = searchByConcertName($conn, $concert_name);
	} else {
		$concert_name = false;
		$concerts = otherwise($conn);
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
		$concerts = otherwise($conn);
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
		$sql = "select * from concerts where concert_id='$id'";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_info[] = $res;
		}

		return $concert_info;
	}

	function searchByConcertName($conn, $concert_name) {
		$sql = "select * from concerts where name='$concert_name'";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_assoc($stmt))                                                        
		{
			$concert_info[] = $res;
		}

		return $concert_info;
	}

	function searchByDate($conn, $concert_date) {
		$sql = "select * from concerts where concert_date='$concert_date'";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$concerts[] = "<li><a href='concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
		}

		return $concerts;
	}

	function otherwise($conn) {
		$sql = "select * from concerts";

		$stmt = performQuery($conn, $sql);

		while ($res = oci_fetch_row($stmt))                                                        
		{
			$concerts[] = "<li><a href='concert.php/?id=".$res[0]."'>".$res[1]."</a></li>";
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
						<input type="text" placeholder="Concert name" class="form-control" name="concert_name"/>
					</form>
				</div>
				<div class="col-md-6">
					<form method="GET" action="">
						<input type="text" placeholder="Concert date" class="form-control" name="concert_date"/>
					</form>
				</div>
			</div>

			<?php
				if (count($concert_info) > 0) {
					echo ("<h3>Concert name: ".$concert_info[0]['NAME']."</h3>");
					echo ("<h4>Date: ".$concert_info[0]['CONCERT_DATE']."</h4>");
					echo ("<h5>Start time: ".$concert_info[0]['START_TIME']."</h5>");
					//echo ("<h4>Venue: ".$concert_info[0]['GENRE']."</h4>");
					// add more here 
					//echo ("<p>Reviews: ".$concert_info[0]['GENRE']."<p>");
				} else {
					foreach($concerts as $a) {
						echo $a;
					}
				}
			?>
		</div>
	</body>
</html>