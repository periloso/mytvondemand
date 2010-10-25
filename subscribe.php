<?php
	include 'functions.php';
	$showid = addslashes($_GET['showid']);
	$sublanguage = addslashes($_GET['language']);
	if (isset($_GET['cancel']))
		$cancel = 1;
	else
		$cancel = 0;
	if (isset($_GET['quality']))
		$quality = addslashes($_GET['quality']);
	else
		$quality = 0;
	if (isset($_GET['from'])) {
		if ($_GET['from'] == 1) {
			$from = 1;
			$season = addslashes($_GET['season']);
			$id = addslashes($_GET['id']);
		}
	} else
		$from = 0;
	if (mysql_num_rows(mysql_query("SELECT * FROM subscriptions WHERE showid = '$showid'"))>0) {
		if ($cancel == 1)
			mysql_query("DELETE FROM subscriptions WHERE showid = '$showid'");
	} else {
		if ($cancel == 0) {
			print ("INSERT INTO subscriptions (showid, quality, subtitles) VALUES ('$showid', '$quality', '$sublanguage')");
			mysql_query("INSERT INTO subscriptions (showid, quality, subtitles) VALUES ('$showid', '$quality', '$sublanguage')");
			if ($from == 1) {
				$q = mysql_query("SELECT * FROM torrents WHERE quality = '$quality' AND showid = '$showid' AND ((season = $season AND episode >= $id) OR (season > $season)) ORDER BY season, episode");
				if (mysql_num_rows($q)) {
					while ($row = mysql_fetch_array($q, MYSQL_ASSOC))
						addTorrent($row['showid'], $row['season'], $row['id'], $row['torrentlink']);
				}
			}
		}
	}
	echo "subscriptions (showid) VALUES ('$showid')";
?>