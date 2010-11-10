<?php
	$showid = intval(addslashes($_GET['showid']));
	$season = intval(addslashes($_GET['season']));
	$episode = intval(addslashes($_GET['id']));
	if (isset($_GET['ajax'])) {
		include 'functions.php';
		$show = new Show($showid, NO_EPISODE_CHECK);
	} else {
		include 'header.php';
		$show = new Show($showid);
	}
?>
				<div class="episodedetails">
					<div class="imagecontainer">
						<a href="show.php?showid=<?=$show->getID()?>"><img class="thumbnail" alt="<?=$show->getTitle()?> - <?php printf("%02d", $show->getEpisode($season.'x'.$episode)->getSeason())?>x<?=printf("%02d", $show->getEpisode($season.'x'.$episode)->getEpisodeID())?> - <?=$show->getEpisode($season.'x'.$episode)->getTitle()?>" src="thumbnail.php?showid=<?=$showid?>&amp;season=<?=$season?>&amp;id=<?=$episode?>" /></a>
					</div>
					<?=addSubscribeText($show->isSubscribed(), $show->getID(), 1)?> <a href="show.php?showid=<?=$show->getID()?>&amp;update=1" class="updatethis">Force Update</a>
					<h2><a id="showtitle" href="show.php?showid=<?=$show->getID()?>"><?=$show->getTitle()?></a> &raquo; <a href="episode.php?showid=<?=$show->getID()?>&amp;season=<?=$show->getEpisode($season.'x'.$episode)->getSeason()?>&amp;id=<?=$show->getEpisode($season.'x'.$episode)->getEpisodeID()?>"><?php printf("%02d", $show->getEpisode($season.'x'.$episode)->getSeason())?>x<?php printf("%02d", $show->getEpisode($season.'x'.$episode)->getEpisodeID()); ?> - <?=$show->getEpisode($season.'x'.$episode)->getTitle()?></a></h2>
					<div class="plot"><?=$show->getEpisode($season.'x'.$episode)->getPlot()?></div>
				</div>
				<?php
				if (!isset($_GET['ajax'])) { ?>
					<div class="fullcontent">
						<div class="contentleft">
							<?php 
								$seasonSplit = $show->splitSeasons();
								foreach ($seasonSplit[0] as $season) { if (!isset($season[0])) break; // PHP BUG?!?!
							?>
							<div class="contentbox normalbox seasons">
								<h3>Season <?php printf("%02d", $season[0]->getSeason())?> <a href="show.php?showid=<?=$season[0]->getShowID()?>" class="subscribe dldmissingeps"><span>Download missing episodes</span></a></h3>
								<ul>
									<?php
									foreach($season as $episode) { ?><li><a href="episode.php?showid=<?=$episode->getShowID()?>&amp;season=<?=$episode->getSeason()?>&amp;id=<?=$episode->getEpisodeID()?>"><span><?php printf("%02d", $episode->getEpisodeID())?> - <?=$episode->getTitle()?></span></a><span><?php setStatus($episode) ?></span></li>
									<?php } ?>
								</ul>
							</div>
							<? } ?>
						</div>
						<div class="contentright">
							<?php 
								foreach ($seasonSplit[1] as $season) { if (!isset($season[0])) break; // PHP BUG?!?!
							?>
							<div class="contentbox normalbox seasons">
								<h3>Season <?php printf("%02d", $season[0]->getSeason())?> <a href="show.php?showid=<?=$season[0]->getShowID()?>" class="subscribe dldmissingeps"><span>Download missing episodes</span></a></h3>
								<ul>
									<?php
									foreach($season as $episode) { ?><li><a href="episode.php?showid=<?=$episode->getShowID()?>&amp;season=<?=$episode->getSeason()?>&amp;id=<?=$episode->getEpisodeID()?>"><span><?php printf("%02d", $episode->getEpisodeID())?> - <?=$episode->getTitle()?></span></a><span><?php setStatus($episode) ?></span></li>
									<?php } ?>
								</ul>
							</div>
							<? } ?>
						</div>
					</div>
<?php
				}
	include 'footer.php';
?>