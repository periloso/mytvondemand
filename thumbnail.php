<?php
	include 'functions.php';
	$showid = intval(addslashes($_GET['showid']));
	$season = intval(addslashes($_GET['season']));
	$episode = intval(addslashes($_GET['id']));

	$thumbPath = sprintf("cache/%02d-%02d%02d-thumb.jpg", $showid, $season, $episode);
	if (!file_exists($thumbPath)) {
		$show = new Show($showid, NO_EPISODE_CHECK);
		$show->getEpisode($season.'x'.$episode)->getThumbnail();
	} else {
		header('Location: /'.$thumbPath);
		die();
	}
?>