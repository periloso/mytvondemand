<?php
	
	include 'header.php';

	$downloading = mysql_query(	"SELECT episodes.*, shows.title as showtitle FROM episodes " .
								"LEFT JOIN torrents ON torrents.episode = episodes.id AND torrents.season = episodes.season AND torrents.showid = episodes.showid JOIN shows ON episodes.showid = shows.id WHERE NOT torrents.hash = '' ORDER BY episodes.showid, episodes.season, episodes.id DESC");
	//$transmission = new Transmission();
	if (mysql_num_rows($downloading)>0) {
?>
			<div class="normalbox downloads beingdownloaded"><h3>Episodes being downloaded</h3><?php while ($singleepisode = mysql_fetch_array($downloading, MYSQL_ASSOC)) { ?>
				<div class="downloaddetails">
					<div class="imagecontainer">
						<div class="thumb">
							<a href="show.php?showid=<?=$singleepisode['showid']?>"><img class="thumbnail" alt="<?=$singleepisode['showtitle']?> - <?php printf("%02d", $singleepisode['season'])?>x<?=printf("%02d", $singleepisode['id'])?> - utf8_decode(<?=$singleepisode['title']?>)" src="thumbnail.php?showid=<?=$singleepisode['showid']?>&amp;season=<?=$singleepisode['season']?>&amp;id=<?=$singleepisode['id']?>" /></a>
						</div>
						<span class="progressbar"></span>
						<span class="details">ETA: Unknown<br />Status: Unknown - Rate: 0KB/s</span>
					</div>
					<h2><a href="show.php?showid=<?=$singleepisode['showid']?>"><?=$singleepisode['showtitle']?></a> &raquo; <a href="episode.php?showid=<?=$singleepisode['showid']?>&amp;season=<?=$singleepisode['season']?>&amp;id=<?=$singleepisode['id']?>"><?php printf("%02d", $singleepisode['season'])?>x<?php printf("%02d", $singleepisode['id']); ?> - <?=utf8_decode($singleepisode['title'])?></a></h2>
					<div class="plot"><?=utf8_decode($singleepisode['plot'])?></div>
				</div><?php } ?>
			</div>
<?php
	}
	
	$download = mysql_query("SELECT downloaded.id as downloadid, episodes.*, shows.title as showtitle FROM episodes " .
							"JOIN downloaded ON downloaded.episodeid = episodes.id AND downloaded.season = episodes.season AND downloaded.showid = episodes.showid " .
							"JOIN shows ON episodes.showid = shows.id ORDER BY downloadid DESC");

?>
			<div class="normalbox downloads downloadedtorrents"><h3>Episodes downloaded</h3><?php while ($singleepisode = mysql_fetch_array($download, MYSQL_ASSOC)) { ?>
				<div class="downloaddetails">
					<div class="imagecontainer">
						<a href="show.php?showid=<?=$singleepisode['showid']?>"><img class="thumbnail" alt="<?=$singleepisode['showtitle']?> - <?php printf("%02d", $singleepisode['season'])?>x<?=printf("%02d", $singleepisode['id'])?> - utf8_decode(<?=$singleepisode['title']?>)" src="thumbnail.php?showid=<?=$singleepisode['showid']?>&amp;season=<?=$singleepisode['season']?>&amp;id=<?=$singleepisode['id']?>" /></a>
					</div>
					<h2><a href="show.php?showid=<?=$singleepisode['showid']?>"><?=$singleepisode['showtitle']?></a> &raquo; <a href="episode.php?showid=<?=$singleepisode['showid']?>&amp;season=<?=$singleepisode['season']?>&amp;id=<?=$singleepisode['id']?>"><?php printf("%02d", $singleepisode['season'])?>x<?php printf("%02d", $singleepisode['id']); ?> - <?=utf8_decode($singleepisode['title'])?></a></h2>
					<div class="plot"><?=utf8_decode($singleepisode['plot'])?></div>
				</div><?php } ?>
			</div>
<?php
	include 'footer.php';
?>