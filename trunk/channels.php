<?php
	include 'header.php';

	$randomshows = new RandomShows(2);
	
	$q = mysql_query("SELECT * FROM shows ORDER BY title");
	$numRows = mysql_num_rows($q);
?>
				<div class="contentleft">
					<?php if ($randomshows->showsRemaining()>0) { ?>
					<div class="contentbox photobox">
						<div class="imagecontainer">
							<a href="show.php?showid=<?=$randomshows->getID()?>"><img class="thumbnail" alt="<?=$randomshows->getTitle()?>" src="cache/<?=$randomshows->getID()?>-fanart.jpg" /></a>
						</div>
						<div class="details">
							<?=addSubscribeText($randomshows->isSubscribed(), $randomshows->getID())?>
							<h3><a href="show.php?showid=<?=$randomshows->getID()?>"><?=$randomshows->getTitle()?></a></h3>
							<span><?=cutText($randomshows->getPlot(), 220)?></span>
						</div>
					</div><?php } ?>
					<div class="contentbox normalbox">
						<h3>Show list</h3>
						<ul>
							<?php for($i=0; $i<($numRows/2); $i++) {
								$row = mysql_fetch_array($q, MYSQL_ASSOC);
								?><li><a href="show.php?showid=<?=$row['id']?>"><?=$row['title']?></a><?php if ($row['thetvdbid']) echo '<span>Info cached</span>'?><!--<span>5 seasons</span>--></li><?php } ?>
						</ul>
					</div>
				</div>
				<div class="contentright">
					<?php if ($randomshows->showsRemaining()>0) { ?>
					<div class="contentbox photobox"><?php $randomshows->nextShow(); ?>
						<div class="imagecontainer">
							<a href="show.php?showid=<?=$randomshows->getID()?>"><img class="thumbnail" alt="<?=$randomshows->getTitle()?>" src="cache/<?=$randomshows->getID()?>-fanart.jpg" /></a>
						</div>
						<div class="details">
							<?=addSubscribeText($randomshows->isSubscribed(), $randomshows->getID())?>
							<h3><a href="show.php?showid=<?=$randomshows->getID()?>"><?=$randomshows->getTitle()?></a></h3>
							<span><?=cutText($randomshows->getPlot(), 220)?></span>
						</div>
					</div><?php } ?>
					<div class="contentbox normalbox">
						<h3>Show list</h3>
						<ul>
							<?php for($i=($numRows/2); $i<$numRows; $i++) {
								$row = mysql_fetch_array($q, MYSQL_ASSOC);
								?><li><a href="show.php?showid=<?=$row['id']?>"><?=htmlentities($row['title'])?></a><?php if ($row['thetvdbid']) echo '<span>Info cached</span>'?><!--<span>5 seasons</span>--></li><?php } ?>
						</ul>
					</div>
				</div>
<?php
	include 'footer.php';
?>