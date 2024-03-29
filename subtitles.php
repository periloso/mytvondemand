#!/usr/bin/php
<?php
	$thisdirectory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	include "$thisdirectory/functions.php";

	$subtitleDownloader = getString('subtitlescript');
	$language = getString('subtitlelanguage');
	$serieslocation = getString('serieslocation');
	include "$thisdirectory/subtitles/$subtitleDownloader.php";
	
	$q = mysql_query("SELECT shows.title as showTitle, episodes.showid as showid, episodes.title as episodeTitle, episodes.season, episodes.id, subscriptions.subtitles as language FROM downloaded JOIN episodes ON downloaded.episodeid = episodes.id AND downloaded.season = episodes.season AND downloaded.showid = episodes.showid JOIN shows ON shows.id = downloaded.showid LEFT JOIN subscriptions ON downloaded.showid = subscriptions.showid WHERE subbed = 0");
	
	while($row = mysql_fetch_array($q, MYSQL_ASSOC)) {
		//function fetchSubtitles($showtitle, $season, $episode, $language) {
		if (($row['language'] != 'none') && ($row['language'] != null))
			$sublanguage = $row['language'];
		else
			$sublanguage = $language;
		$showTitle = $row['showTitle'];
		$showid = $row['showid'];
		$episodeTitle = $row['episodeTitle'];
		$season = $row['season'];
		$episodeid = $row['id'];
		if ($sublanguage != 'none') {
			$subtitle = fetchSubtitles($showTitle, $season, $episodeid, $sublanguage);
			echo "$showTitle - $season"."x"."$episodeid - $episodeTitle\n";
			if (($subtitle != Null) && (strlen($subtitle) >= 100)) {
				$filePath = sprintf('%s/%s/%s - %02dx%02d - %s.srt', $serieslocation, sanitizeStrings($showTitle), sanitizeStrings($showTitle), $season, $episodeid, sanitizeStrings($episodeTitle));
				file_put_contents($filePath, $subtitle);			
				mysql_query("UPDATE downloaded SET subbed = 1 WHERE showid = '$showid' AND season = '$season' AND episodeid = '$episodeid'");
			}
		}
	}
?>
