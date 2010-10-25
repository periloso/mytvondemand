<?php
	include 'functions.php';
	$showid = addslashes($_GET['showid']);
	$season = intval(addslashes($_GET['season']));
	$episode = intval(addslashes($_GET['id']));
	$show = new Show($showid, NO_EPISODE_CHECK);
	if (count($show->getEpisode($season.'x'.$episode)->getTorrents())>0) {
		echo json_encode($show->getEpisode($season.'x'.$episode)->getDownloadStatus());
	}	
?>