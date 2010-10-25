<?php
	include 'functions.php';
	$showid = addslashes($_GET['showid']);
	$season = addslashes($_GET['season']);
	$id = addslashes($_GET['id']);
	$torrenturl = addslashes(trim($_POST['torrenturl']));
	if (substr($torrenturl,0,7) != 'magnet:') {
		$fp = @fopen(stripslashes($torrenturl), 'r');
		$content = @fread($fp, 512);
		if (($fp) && (substr($content,0,11) == 'd8:announce')) {
			echo "ok";
			mysql_query("INSERT INTO torrents (link, showid, season, episode, quality) VALUES ('$torrenturl', '$showid', '$season', '$id', 0)");
		} else
			echo "ko";
	} else {
		echo "ok";
		mysql_query("INSERT INTO torrents (link, showid, season, episode, quality) VALUES ('$torrenturl', '$showid', '$season', '$id', 0)");
	}
?>