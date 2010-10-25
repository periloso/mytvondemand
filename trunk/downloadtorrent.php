<?php
	include 'functions.php';
	$serieslocation = addslashes(getString('serieslocation')); # replace " into \"
	
	$showid = addslashes($_GET['showid']);
	$season = intval(addslashes($_GET['season']));
	$episode = intval(addslashes($_GET['id']));
	$pause = ($_GET['pause'] == '1');
	$resume = ($_GET['resume'] == '1');
	$remove = ($_GET['remove'] == '1');
	
	$show = new Show($showid);
	if (($pause == 0) && ($resume == 0) && ($remove == 0)) {
		if ($show->downloadTorrent($show->getEpisode($season.'x'.$episode)) == 'ok')
			die('ok');
		else
			die('ko');
	} elseif ($pause)
		$show->stopTorrent($show->getEpisode($season.'x'.$episode));
	elseif ($resume)
		$show->startTorrent($show->getEpisode($season.'x'.$episode));
	elseif ($remove)
		$show->removeTorrent($show->getEpisode($season.'x'.$episode));
?>