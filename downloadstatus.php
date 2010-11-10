<?php
	include 'functions.php';
	$showid = addslashes($_GET['showid']);
	$season = intval(addslashes($_GET['season']));
	$episode = intval(addslashes($_GET['id']));
	$getHash = ($_GET['getHash'] == '1');
	$hash = addslashes($_GET['hash']);
	
	if ($getHash) {	
		$show = new Show($showid, NO_EPISODE_CHECK);
		if (count($show->getEpisode($season.'x'.$episode)->getTorrents())>0) {
			//echo json_encode($show->getEpisode($season.'x'.$episode)->getDownloadStatus());
			$torrents = $show->getEpisode($season.'x'.$episode)->getTorrents();
			echo json_encode($torrents[0]);
			die();
		}
	} else {
		$transmission = new Transmission();
		$actualstatus = $transmission->getStatus($hash);
		echo json_encode($actualstatus);
	}
?>