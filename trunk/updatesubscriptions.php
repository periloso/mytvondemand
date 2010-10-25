#!/usr/bin/php
<?php
include 'functions.php';

$q = mysql_query("SELECT showid FROM subscriptions");
$numSubscriptions = mysql_num_rows($q);
for ($i=0; $i<$numSubscriptions; $i++) {
	$show = new Show(mysql_result($q, $i), NO_EPISODE_CHECK);
	$show->updateShow(FORCE_UPDATE, DOWNLOAD_NEW_EPISODES);
}
?>