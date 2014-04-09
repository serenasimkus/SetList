<?php
	ini_set('display_errors', 'On'); 
	$db = "w4111f.cs.columbia.edu:1521/adb"; 
	$conn = oci_connect("sks2187", "cookies1", $db); 

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

	function getSidebarConcertReviews($conn, $username) {
		$sql = "select * from reviews_c r, concerts c where r.concert_id=c.concert_id and r.username='$username'";

		$stmt = performQuery($conn, $sql);

		$concert_reviews = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$concert_reviews[] = "<li><a href='/~sks2187/w4111/concert.php/?id=".$res['CONCERT_ID']."'>".$res['NAME']."</a></li>";
		}

		return $concert_reviews;
	}

	function getSidebarVenueReviews($conn, $username) {
		$sql = "select * from reviews_v r, venues v where r.venue_id=v.venue_id and r.username='$username'";

		$stmt = performQuery($conn, $sql);

		$venue_reviews = array();

		while ($res = oci_fetch_assoc($stmt))
		{
			$venue_reviews[] = "<li><a href='/~sks2187/w4111/venue.php/?id=".$res['VENUE_ID']."'>".$res['NAME']."</a></li>";
		}

		return $venue_reviews;
	}
?>