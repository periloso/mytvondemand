<?php
	include 'header.php';

	$show = mysql_query(	"SELECT *, subscriptions.quality FROM (SELECT shows.*, episodes.aired FROM shows as shows " .
								"LEFT JOIN (SELECT showid, aired FROM episodes WHERE aired >= CURDATE()) as episodes " .
								"ON shows.id = episodes.showid " .
								"WHERE id IN (SELECT showid FROM subscriptions) " .
								"ORDER BY ISNULL(aired) ASC, aired ASC, title ASC) as final " .
								"JOIN subscriptions ON subscriptions.showid = final.id " .
								"GROUP BY id ORDER BY ISNULL(aired), aired, title");

	while ($singleshow = mysql_fetch_array($show, MYSQL_ASSOC)) {
		if ($singleshow['aired']>0) {
			$difference = dateDifference($singleshow['aired']); # Difference to now (default value)
			$airingdays = floor($difference % 31);
			$airingmonths = floor($difference / 31);
			if ($airingmonths > 0)
				$airingtext = "$airingmonths months, $airingdays days";
			else
				$airingtext = ($airingdays == 0) ? 'Today' : (($airingdays == 1) ? '1 day' : $airingdays . ' days');
		} else
			$airingtext = "No airs";
		$q = $singleshow['quality'];
		if ($q == 0)
			$quality = "HDTV";
		elseif ($q == 1)
			$quality = "DVDRIP";
		elseif ($q == 2)
			$quality = "720p";

?>
				<div class="seriesdetails subscriptions">
					<div class="imagecontainer">
						<a href="/show.php?showid=<?=$singleshow['id']?>"><img class="thumbnail" alt="<?=utf8_decode($singleshow['title'])?>" src="/cache/<?=$singleshow['id']?>-poster.jpg" /></a>
						<div class="quality">Quality: <strong><?=$quality?></strong></div>
						<div class="nextair">Next airing: <strong><?=$airingtext?></strong></div>
					</div>
					<?=addSubscribeText(1, $singleshow['id'])?>
					<h2><a href="/show.php?showid=<?=$singleshow['id']?>"><?=utf8_decode($singleshow['title'])?></a></h2>
					<div class="plot"><?=utf8_decode($singleshow['plot'])?></div>
					
				</div>
<?php
	}
	include 'footer.php';
?>