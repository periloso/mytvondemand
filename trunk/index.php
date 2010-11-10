<?php
	include 'header.php';

	$randomshows = new RandomShows(3);
	if ($randomshows->showsRemaining()<=0) {
		print "Please, if you didn't already, go to the configuration!";
		die(include 'footer.php');
	}
?>
				<div class="seriesdetails">
					<?php if ($randomshows->showsRemaining()>0) { ?>
					<div class="imagecontainer">
						<a href="show.php?showid=<?=$randomshows->getID()?>"><img class="thumbnail" alt="<?=$randomshows->getTitle()?>" src="cache/<?=$randomshows->getID()?>-poster.jpg" /></a>
					</div>
					<?=addSubscribeText($randomshows->isSubscribed(), $randomshows->getID(), 1)?>
					<h2><a href="show.php?showid=<?=$randomshows->getID()?>"><?=$randomshows->getTitle()?></a></h2>
					<div class="plot"><?=$randomshows->getPlot()?></div>
					<?php } ?>
				</div>
				<div class="contentleft">
					<?php if ($randomshows->showsRemaining()>0) { ?><?php $randomshows->nextShow(); ?>
					<div class="contentbox photobox">
						<div class="imagecontainer">
							<a href="show.php?showid=<?=$randomshows->getID()?>"><img class="thumbnail" alt="<?=$randomshows->getTitle()?>" src="cache/<?=$randomshows->getID()?>-fanart.jpg" /></a>
						</div>
						<div class="details">
							<?=addSubscribeText($randomshows->isSubscribed(), $randomshows->getID(), 1)?>
							<h3><a href="show.php?showid=<?=$randomshows->getID()?>"><?=htmlentities($randomshows->getTitle())?></a></h3>
							<span><?=cutText($randomshows->getPlot(), 220)?></span>
						</div>
					</div><?php } ?>
				</div>
				<div class="contentright">
					<?php if ($randomshows->showsRemaining()>0) { ?><?php $randomshows->nextShow(); ?>
					<div class="contentbox photobox">
						<div class="imagecontainer">
							<a href="show.php?showid=<?=$randomshows->getID()?>"><img class="thumbnail" alt="<?=$randomshows->getTitle()?>" src="cache/<?=$randomshows->getID()?>-fanart.jpg" /></a>
						</div>
						<div class="details">
							<?=addSubscribeText($randomshows->isSubscribed(), $randomshows->getID(), 1)?>
							<h3><a href="show.php?showid=<?=$randomshows->getID()?>"><?=$randomshows->getTitle()?></a></h3>
							<span><?=cutText($randomshows->getPlot(), 220)?></span>
						</div>
					</div><?php } ?>
				</div>
<?php
	include 'footer.php';
?>