<?
	$thisdirectory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
	include "$thisdirectory/config.php";
	define('EP_NOT_PRESENT', 0);
	define('EP_DOWNLOADING', 1);
	define('EP_PENDING', 2);
	define('EP_ON_HARDDISK', 3);
	define('EP_DOWNLOAD_STOP', 4);
	define('EP_FUTURE_EPISODE', 5);
	define('EP_NEED_MANUAL_TORRENT', 6);
	define('EP_UNKNOWN_AIRING', 7);
	define('NO_EPISODE_CHECK', 0);
	define('NO_FORCE_UPDATE', 0);
	define('FORCE_UPDATE', 1);
	define('DOWNLOAD_NEW_EPISODES', 1);
	
	$ctx = stream_context_create(array('http' => array('timeout' => $sockettimeout))); 
	
	if (!mysql_connect($dbhost, $dbuser, $dbpass))
		die('Could not connect: ' . mysql_error());
	if (!mysql_select_db($dbname))
		die('Could not select database: ' . mysql_error());
	updateShowsEZTV();
	$serieslocation = getString('serieslocation');
	unset($null);
	
	function showsEZTV() {
		global $ctx;
		$showarray = array();
		$content = file_get_contents('http://eztv.it/showlist/', 0, $ctx);
		preg_match_all('/forum_thread_post.*?href="(.*?)".*?>(.*?)<.*?<\/tr>/ism', $content, $matches, PREG_SET_ORDER);
		foreach ($matches as $show) {
			preg_match_all('/(.*?), (.*)/', $show[2], $comamatch, PREG_SET_ORDER);
			if ($comamatch[0][1])
				$showarray[] = array($comamatch[0][2] . " " . $comamatch[0][1], "http://eztv.it$show[1]");
			else
				$showarray[] = array($show[2], "http://eztv.it$show[1]");
		}
		return($showarray);
	}

	function updateShowsEZTV() {
		if (mysql_num_rows(mysql_query("SELECT * FROM configuration WHERE configuration.key = 'LatestEZTVUpdate' AND DATE(value3) > DATE_SUB(NOW(), INTERVAL 2 DAY)")) == 0) {
			$showarray = showsEZTV();
			foreach($showarray as $singleshow) {
				$singleshow[0] = addslashes($singleshow[0]);
				$singleshow[1] = addslashes($singleshow[1]);
				if (mysql_num_rows(mysql_query("SELECT * FROM shows WHERE title = '$singleshow[0]'")))
					mysql_query("UPDATE shows SET torrentlink = '$singleshow[1]' WHERE title = '$singleshow[0]'");
				else
					mysql_query("INSERT INTO shows (title, torrentlink) VALUES ('" . utf8_encode($singleshow[0]) . "', '$singleshow[1]')");
			}
			if (mysql_num_rows(mysql_query("SELECT value3 FROM configuration WHERE configuration.key = 'LatestEZTVUpdate'")) == 0) {
				mysql_query("INSERT INTO configuration (configuration.key, value3) VALUES ('LatestEZTVUpdate', NOW())");
			} else {
				mysql_query("UPDATE configuration SET value3 = NOW() WHERE configuration.key = 'LatestEZTVUpdate'");
			}
		}
	}

	function addSubscribeText($isSubscribed, $showid, $dialog = 0) {
		if ($isSubscribed)
			echo "<a href='subscribe.php?showid=$showid&amp;cancel=1' class='subscribe'><span>Unsubscribe</span></a>";
		else {
			if ($dialog) {
				echo "<a href='subscribe.php?showid=$showid' class='subscribedialog'><span>Subscribe</span></a>";
			} else {
				echo "<a href='subscribe.php?showid=$showid' class='subscribe'><span>Subscribe</span></a>";
			}
		}
	}
	
	function retrieveURL($url, $parameters = '', $post='') {
		preg_match("/http:\/\/(.*?):([0-9]*)(\/.*)/", $url, $match);
		$host = $match[1];
		$port = (int)$match[2];
		$path = $match[3];
		if ($post != '') {
			$lenpost = "Content-Length: " . strlen($post) . "\r\n";
			$method = 'POST';
		} else
			$method = 'GET';
		$out =	"$method $path HTTP/1.1\r\n" .
				"Host: $host\r\n" .
				$parameters .
				$lenpost .
				"Connection: Close\r\n\r\n$post";
		$fp = @fsockopen($host, $port, $errno, $errstr, 30);
		if (!$fp)
			die("Unable to connect to $url");
		fwrite($fp, $out);
		$content = '';
		while (!feof($fp))
			$content .= fgets($fp, 512);
		fclose($fp);
		return substr($content, strpos($content, "\r\n\r\n")+4);
	}

	function cutText($text, $limit) {
		if (strlen($text) <= $limit)
			return $text;
		else
			return substr($text, 0, strpos($text, ' ', $limit)) . '...';
	}

	function dateDifference($first, $second = null) {
		if ($second == Null)
			$second = date('Y-m-d'); # Default value
		$first = strtotime($first,0);
		$second = strtotime($second,0);
		return floor(($first-$second)/(60*60*24));
	}
	
	function sanitizeStrings($string) {
		$stripper = "\?/|<>\"";
		for ($i=0; $i<=strlen($stripper); $i++){
			$string = str_replace(substr($stripper,$i,1), '', $string);
		}
		return str_replace(':', ' -', $string);
	}

	function getString($dbkey) {
		$q = mysql_query("SELECT value1 FROM configuration WHERE configuration.key = '$dbkey'");
		if (mysql_num_rows($q) > 0)
			return mysql_result($q, 0);
		elseif ($dbkey == 'thetvdblanguage')
			return 'en';
		elseif ($dbkey == 'serieslocation')
			return '/home/transmission/';
		elseif ($dbkey == 'subtitlelanguage')
			return 'none';
		else
			return '';
	}
	
	function saveString($dbkey, $dbvalue) {
		if ($dbkey == 'transmissionurl') {
			preg_match('/(http:\/\/.*?:[0-9]*)/', $dbvalue, $match);
			$dbvalue = $match[0] . '/transmission/rpc/';
		}
		$q = mysql_query("SELECT value1 FROM configuration WHERE configuration.key = '$dbkey'");
		if (mysql_num_rows($q) > 0)
			mysql_query("UPDATE configuration SET value1 = '$dbvalue' WHERE configuration.key = '$dbkey'");
		else
			mysql_query("INSERT INTO configuration (configuration.key, value1) VALUES ('$dbkey', '$dbvalue')");
	}

	function getInteger($dbkey) {
		$q = mysql_query("SELECT value2 FROM configuration WHERE configuration.key = '$dbkey'");
		if (mysql_num_rows($q) > 0)
			return mysql_result($q, 0);
		else
			return 0;
	}
	
	function saveInteger($dbkey, $dbvalue) {
		$q = mysql_query("SELECT value2 FROM configuration WHERE configuration.key = '$dbkey'");
		if (mysql_num_rows($q) > 0)
			mysql_query("UPDATE configuration SET value2 = '$dbvalue' WHERE configuration.key = '$dbkey'");
		else
			mysql_query("INSERT INTO configuration (configuration.key, value2) VALUES ('$dbkey', '$dbvalue')");
	}
	
	function sendThumbnail($imagePath) {
		header('Location: /'.$imagePath);
	}
	
	function setStatus($episode) {
		switch ($episode->getStatus()) {
			case EP_NOT_PRESENT:
				echo '<span class="downloadtorrent"><img alt="Download" title="Download" src="images/download.png" /></span>';
				break;
			case EP_DOWNLOADING:
				echo '<span class="downloadingtorrent"><img alt="Downloading..." title="Downloading..." src="images/downloading.png" /></span>';
				break;
			case EP_PENDING:
				echo '<img alt="Already downloaded" title="Already downloaded" src="images/tick.png" />';
				break;
			case EP_ON_HARDDISK:
				echo '<img alt="Already downloaded" title="Already downloaded" src="images/tick.png" />';
				break;
			case EP_DOWNLOAD_STOP:
				echo '<span class="pausedtorrent"><img alt="Paused" title="Paused" src="images/paused.png" /></span>';
				break;
			case EP_FUTURE_EPISODE:
				$airing = ($episode->getUntilAired() == 0) ? 'Today' : (($episode->getUntilAired() == 1) ? 'Tomorrow' : $episode->getUntilAired() . ' days');
				echo '<span class="manualaddtorrent"><img alt="Future episode" title="Future episode - Airing: '.$airing.'" src="images/future_episode.png" /></span>';
				break;
			case EP_NEED_MANUAL_TORRENT:
				echo '<span class="manualaddtorrent"><img alt="Manual Torrent" title="Manual Torrent" src="images/manual_torrent.png" /></span>';
				break;
			case EP_UNKNOWN_AIRING:
				echo '<span class="manualaddtorrent"><img alt="Airing Unknown" title="Airing Unknown" src="images/unknown_airing.png" /></span>';
				break;
		}
	}

	class SimpleImage {
	   
		var $image;
		var $image_type;
		var $filename;
		var $modified = false;

		function open($filename) {
			$image_info = getimagesize($filename);
			$this->image_type = $image_info[2];
			if( $this->image_type == IMAGETYPE_JPEG )
				$this->image = imagecreatefromjpeg($filename);
			elseif( $this->image_type == IMAGETYPE_GIF )
				$this->image = imagecreatefromgif($filename);
			elseif( $this->image_type == IMAGETYPE_PNG )
				$this->image = imagecreatefrompng($filename);
			$this->filename = $filename;
		}
		function save($filename = null, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
			if ($this->modified) {
				if ($filename == null)
					$filename = $this->filename;
				if( $image_type == IMAGETYPE_JPEG )
					imagejpeg($this->image,$filename,$compression);
				elseif( $image_type == IMAGETYPE_GIF )
					imagegif($this->image,$filename);         
				elseif( $image_type == IMAGETYPE_PNG )
					imagepng($this->image,$filename);
				if( $permissions != null)
					chmod($filename,$permissions);
			}
		}
		function output($image_type=IMAGETYPE_JPEG) {
			if( $image_type == IMAGETYPE_JPEG )
				imagejpeg($this->image);
			elseif( $image_type == IMAGETYPE_GIF )
				imagegif($this->image);         
			elseif( $image_type == IMAGETYPE_PNG )
				imagepng($this->image);
		}
		function getWidth() {
			return imagesx($this->image);
		}
		function getHeight() {
			return imagesy($this->image);
		}
		function resizeIfBigger($width = 800, $height = 700) {
			if (($width > $this->getWidth()) || ($height > $this->getHeight())) {
				if ($this->getWidth() > $this->getHeight())
					$this->resizeToWidth($width);
				else
					$this->resizeToHeight($height);
				$this->modified = true;
			}
		}
		function resizeToHeight($height) {
			$ratio = $height / $this->getHeight();
			$width = $this->getWidth() * $ratio;
			$this->resize($width,$height);
		}
		function resizeToWidth($width) {
			$ratio = $width / $this->getWidth();
			$height = $this->getheight() * $ratio;
			$this->resize($width,$height);
		}
		function scale($scale) {
			$width = $this->getWidth() * $scale/100;
			$height = $this->getheight() * $scale/100; 
			$this->resize($width,$height);
		}
		function resize($width,$height) {
			$new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
			$this->image = $new_image;   
		}      
	}

	class RandomShows {
		const NO_EPISODE_CHECK = 0;
		private $shows = array();
		private $pointer;
		
		function __construct($numShows = 1) {
		$q = mysql_query("SELECT id FROM shows WHERE NOT(plot = '') ORDER BY RAND() LIMIT $numShows");
			while ($row = mysql_fetch_array($q, MYSQL_ASSOC)) {
				$this->shows[] = new Show($row['id'], NO_EPISODE_CHECK);
			}
			$this->pointer = 0;
		}
	
		public function showsRemaining() {
			return (count($this->shows) - ($this->pointer+1));
		}
		
		public function nextShow() {
			$this->pointer++;
		}
		
		public function getID() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getID();
		}
		
		public function isSubscribed() {
			return $this->shows[$this->pointer]->isSubscribed();
		}
		
		public function getPlot() {
			if (isset($this->shows[$this->pointer]))
				return str_replace('&','&amp;', $this->shows[$this->pointer]->getPlot());
		}
		
		public function getThetvdbid() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getThetvdbid();
		}
		
		public function getTitle() {
			if (isset($this->shows[$this->pointer]))
				return str_replace('&','&amp;', $this->shows[$this->pointer]->getTitle());
		}
		
		public function getLastthetvdbupdate() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getLastthetvdbupdate();
		}
		
		public function getLasteztvupdate() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getLasteztvupdate();
		}
		
		public function getTorrentlink() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getTorrentlink();
		}
		
		public function getQuality() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getQuality();
		}
		
		public function getSubscribed() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getSubscribed();
		}
		
		public function getSubbed() {
			if (isset($this->shows[$this->pointer]))
				return $this->shows[$this->pointer]->getSubbed();
		}
		
		public function getShows() {
			return $this->shows;
		}
	}
	
	class Episode {
		const EP_NOT_PRESENT = 0;
		const EP_DOWNLOADING = 1;
		const EP_PENDING = 2;
		const EP_ON_HARDDISK = 3;
		const EP_DOWNLOAD_STOP = 4;
		const EP_FUTURE_EPISODE = 5;
		const EP_NEED_MANUAL_TORRENT = 6;
		const EP_UNKNOWN_AIRING = 7;
		private $title;
		private $plot;
		private $aired;
		private $thumbnail;
		private $torrents = array();
		private $status = EP_NOT_PRESENT;
		private $statusdetails;
		private $TR_TORRENT_DIR;
		private $TR_TORRENT_NAME;
		private $TR_TORRENT_HASH;
		private $subbed;
		private $season;
		private $showid;
		private $episodeid;
		private $showname;
		
		public function getUntilAired() {
			return dateDifference($this->aired);
		}
		
		public function getShowID() {
			return $this->showid;
		}
		
		public function getSeason() {
			return $this->season;
		}
		
		public function getEpisodeID() {
			return $this->episodeid;
		}
		
		public function getStatus() {
			return $this->status;
		}
		
		public function getTitle() {
			return utf8_decode($this->title);
		}
		
		public function getPlot() {
			return utf8_decode($this->plot);
		}
		
		public function getAired() {
			return $this->aired;
		}
		
		public function getDownloadStatus() {
			return $this->statusdetails;
		}
		
		function getThumbnail() {
			global $ctx;
			$thumbPath = sprintf("cache/%02d-%02d%02d-thumb.jpg", $this->showid, $this->season, $this->episodeid);
			if (!file_exists($thumbPath)) {
				if ($this->thumbnail != '') {
					$url = "http://www.thetvdb.com/banners/" . $this->thumbnail;
					$thumb = @file_get_contents($url, 0, $ctx);
					if (strlen($thumb)<10)
						die(sendThumbnail('images/no_image.gif'));
					file_put_contents($thumbPath, $thumb);
					die(sendThumbnail($thumbPath));
				} else
					die(sendThumbnail('images/no_image.gif'));
			} else
				die(sendThumbnail($thumbPath));
		}

		public function getSubbed() {
			return $this->subbed;
		}
		
		public function addTorrent($row, $transmission = Null) {
			if (($row['quality'] != Null) && ($row['link'] != Null)) {
				$this->torrents[$row['quality']] = array(link => ($row['link']), hash => ($row['hash']));
			}
			if (($transmission != Null) && ($row['hash'])) {
				$actualstatus = $transmission->getStatus($row['hash']);
				if ($actualstatus != Null) {
					if ($actualstatus['status'] == 8) {
						$this->status = EP_PENDING;
						$this->statusdetails = $actualstatus;
					} elseif ($actualstatus['status'] == 16) {
						$this->status = EP_DOWNLOAD_STOP;
						$this->statusdetails = $actualstatus;
					} else {
						$this->status = EP_DOWNLOADING;
						$this->statusdetails = $actualstatus;
					}
				} else {
					if ($this->getUntilAired() >= 0)
						$this->status = EP_FUTURE_EPISODE;
					elseif ($this->getAired() == '0000-00-00')
						$this->status = EP_UNKNOWN_AIRING;
					else {
						if (count($this->torrents)<=0)
							$this->status = EP_NEED_MANUAL_TORRENT;
					}
				}
			} else {
				if ($this->getUntilAired() >= 0)
					$this->status = EP_FUTURE_EPISODE;
				elseif ($this->getAired() == '0000-00-00')
					$this->status = EP_UNKNOWN_AIRING;
				else {
					if (count($this->torrents)<=0)
						$this->status = EP_NEED_MANUAL_TORRENT;
				}
			}
		}
		
		public function updateTorrents($quality, $link, $hash = '') {
			if (isset($this->torrents[$quality])) {
				if ($this->torrents[$quality]['link'] != $link) {
					if (stripos($this->torrents[$quality]['link'], 'proper')===FALSE) {
						$this->torrents[$quality] = array(link => $link, hash => $hash);
						mysql_query("UPDATE torrents SET link = '$link' WHERE showid = '".$this->showid."' AND season = '".$this->season."' AND episode = '".$this->episodeid."' AND quality = '$quality'");
						if ($hash != '')
							$this->status = EP_DOWNLOADING;
					}
				}
			} else {
				$this->torrents[$quality] = array(link => $link, hash => $hash);
				mysql_query("INSERT INTO torrents (showid, season, episode, link, hash, quality) VALUES ('".$this->showid."', '".$this->season."', '".$this->episodeid."', '$link', '$hash', '$quality')");
				if ($this->status == EP_NEED_MANUAL_TORRENT)
					$this->status = EP_NOT_PRESENT;
				else
					$this->checkEpisode();
			}
		}
		
		public function getTorrents($quality = Null) {
			if ($quality == Null)
				return $this->torrents;
			else {
				if (isset($this->torrents[$quality]))
					return $this->torrents[$quality];
				else {
					if (count($this->torrents)>0)
						return($this->torrents[0]);
					else
						return Null;
				}
			}
		}
		
		function checkEpisode() {
			global $serieslocation;
			if (is_dir("$serieslocation/" . sanitizeStrings($this->showname))) {
				$dirarray = scandir("$serieslocation/" . sanitizeStrings($this->showname));
				for ($i=0; $i<count($dirarray); $i++) {
					$string = '%02dx%02d';
					if (strpos($dirarray[$i], sprintf($string, $this->season, $this->episodeid))>0) {
						$fileextension = strrchr($dirarray[$i], '.');
						if (($fileextension == '.avi') || ($fileextension == '.mkv')) {
							$this->status = EP_ON_HARDDISK;
							break;
						}
					}
				}
			}
		}
		
		function updateEpisode($episode) {
			$this->aired = trim($episode->FirstAired);
			$this->plot = addslashes(utf8_encode($episode->Overview));
			$this->title = addslashes(utf8_encode($episode->EpisodeName));
			$this->thumbnail = trim($episode->filename);
			if ($this->aired != '') {
				mysql_query("UPDATE episodes SET aired = '".$this->aired."', plot = '".$this->plot."', title = '".$this->title."', thumbnail = '".$this->thumbnail."' WHERE id = '".$this->episodeid."' AND showid = '".$this->showid."' AND season = '".$this->season."'");
			}
		}

		function __construct($row, $showname = '', $transmission = Null, $checkEpisode = 1) {
			$this->title = trim($row['title']);
			$this->plot = trim($row['plot']);
			$this->aired = $row['aired'];
			$this->thumbnail = $row['thumbnail'];
			$this->TR_TORRENT_DIR = $row['TR_TORRENT_DIR'];
			$this->TR_TORRENT_NAME = $row['TR_TORRENT_NAME'];
			$this->TR_TORRENT_HASH = $row['TR_TORRENT_HASH'];
			$this->showid = $row['showid'];
			$this->season = $row['season'];
			$this->episodeid = $row['id'];
			$this->showname = $showname;
			
			$this->addTorrent($row, $transmission);
			if ($checkEpisode == 1)
				$this->checkEpisode();
		}
	}
	
	class Show {
		private $id;
		private $myEpisodes = array();
		private $plot;
		private $thetvdbid;
		private $title;
		private $lastthetvdbupdate;
		private $lasteztvupdate;
		private $torrentlink;
		private $quality;
		private $subscribed;
		private $subbed;
		private $present;
		private $transmission;
		
		function searchTheTVDBID() {
			global $ctx;
			$language = 'en';
			$searchTitle = urlencode(preg_replace("/[^a-zA-Z0-9 ]/", "", str_replace('/',' ', $this->title)));
			$xml = simplexml_load_string(file_get_contents("http://www.thetvdb.com/api/GetSeries.php?seriesname=$searchTitle&amp;language=$language", 0, $ctx));
			if (isset($xml->Series[0]->SeriesName))
				$this->thetvdbid = $xml->Series[0]->seriesid;
			else
				throw new Exception("TV Series not found!");
			$this->plot = utf8_encode($xml->Series[0]->Overview);
		}
		
		function getSeriesTorrents() {
			global $ctx;
			$episodesarray = array();
			$content = file_get_contents($this->torrentlink, 0, $ctx);
			$content = str_replace("\n",'',$content); // Easier to apply the regex
			#preg_match_all('/epinfo">(.*?) [sS]?([0-9]*)[eExX]([0-9]*) (.*?)<\/.*?href="(.*?)"/ism', $content, $matches, PREG_SET_ORDER);
			# using magnet links...!
			preg_match_all('/epinfo">(.*?) [sS]?([0-9]*)[eExX]([0-9]*) (.*?)<.*?(magnet\:.*?)" /ism', $content, $matches, PREG_SET_ORDER);
			foreach ($matches as $torrent) {
				$showname = $torrent[1];
				$season = intval($torrent[2]);
				$episode = intval($torrent[3]);
				$quality = $torrent[4];
				$torrentlink = $torrent[5];
				if (stristr($quality, 'HDTV') || stristr($quality, 'DSR'))
					$quality = 0;
				elseif (stristr($quality, 'DVDSCR'))
					$quality = 1;
				elseif (stristr($quality, '720p'))
					$quality = 2;
				else
					$quality = 1;
				$episodearray[$season."x".$episode] = array(torrentlink => $torrentlink, quality => $quality);
			}
			return ($episodearray);
		}
		
		function extractShow() {
			global $ctx, $thetvdbapikey, $thisdirectory;
			$language = getString('thetvdblanguage');
			$file = file_get_contents ("http://www.thetvdb.com/api/$thetvdbapikey/series/".$this->thetvdbid."/all/$language.zip", 0, $ctx); 
			if (strlen($file)>10) {
				file_put_contents("$thisdirectory/temp/".$this->id.".zip", $file);

				$z = new ZipArchive();
				if (@$z->open("$thisdirectory/temp/".$this->id.".zip")) {
					$fp = $z->getStream("$language.xml");
					if(!$fp) return(0);
					while (!feof($fp))
						$contents .= fread($fp, 1024);
					fclose($fp);
					$xml = simplexml_load_string($contents);
					$this->plot = utf8_encode($xml->Series->Overview);
					
					foreach($xml->Episode as $episode) {
						$episodeid = intval(trim($episode->EpisodeNumber));
						$season = intval(trim($episode->SeasonNumber));
						$aired = trim($episode->FirstAired);
						$plot = addslashes(trim(utf8_encode($episode->Overview)));
						$title = addslashes(trim(utf8_encode($episode->EpisodeName)));
						$thumbnail = trim($episode->filename);
						if (isset($this->myEpisodes[$season.'x'.$episodeid])) {
							$this->myEpisodes[$season.'x'.$episodeid]->updateEpisode($episode);
						} else {
							mysql_query("INSERT INTO episodes (id, showid, season, aired, plot, title, thumbnail) VALUES ('$episodeid', '".$this->id."', '$season', '$aired', '$plot', '$title', '$thumbnail')");
							$sql = "SELECT episodes.*, torrents.link, torrents.hash, torrents.quality FROM torrents LEFT OUTER JOIN (SELECT episodes.*, pending.TR_TORRENT_DIR, pending.TR_TORRENT_NAME, pending.TR_TORRENT_HASH, NOT (downloaded.id IS NULL) as downloaded , downloaded.subbed FROM episodes LEFT JOIN pending ON episodes.id = pending.episode AND episodes.season = pending.season AND episodes.showid = pending.showid LEFT JOIN downloaded ON episodes.id = downloaded.episodeid AND episodes.season = downloaded.season AND episodes.showid = downloaded.showid) AS episodes ON torrents.showid = episodes.showid AND torrents.season = episodes.season AND torrents.episode = episodes.id WHERE episodes.id = '$episodeid' AND episodes.season = '$season' AND episodes.showid = '".$this->id."' " .
									"UNION " .
								"SELECT episodes.*, torrents.link, torrents.hash, torrents.quality FROM torrents RIGHT OUTER JOIN (SELECT episodes.*, pending.TR_TORRENT_DIR, pending.TR_TORRENT_NAME, pending.TR_TORRENT_HASH, NOT (downloaded.id IS NULL) as downloaded , downloaded.subbed FROM episodes LEFT JOIN pending ON episodes.id = pending.episode AND episodes.season = pending.season AND episodes.showid = pending.showid LEFT JOIN downloaded ON episodes.id = downloaded.episodeid AND episodes.season = downloaded.season AND episodes.showid = downloaded.showid) AS episodes ON torrents.showid = episodes.showid AND torrents.season = episodes.season AND torrents.episode = episodes.id WHERE episodes.id = '$episodeid' AND episodes.season = '$season' AND episodes.showid = '".$this->id."'";
							$array = mysql_fetch_array(mysql_query($sql), MYSQL_ASSOC);
							$this->myEpisodes[$season.'x'.$episodeid] = new Episode($array, $this->title, $this->transmission, 1);
						}
					}
								
					$contents = '';
					$fp = $z->getStream('banners.xml');
					if(!$fp) return(0);
					while (!feof($fp))
						$contents .= fread($fp, 1024);
					fclose($fp);
					$xml = simplexml_load_string($contents);
					foreach ($xml->Banner as $image) {
						if (($gotfanart == False) && ($image->BannerType == "fanart")) {
							$fanart = "http://www.thetvdb.com/banners/" . $image->VignettePath;
							$gotfanart = True;
						} elseif (($gotposter == False) && ($image->BannerType == "poster")) {
							$poster = "http://www.thetvdb.com/banners/" . $image->BannerPath;
							$gotposter = True;
						} elseif (($gotposter == True) && ($gotfanart == True))
							break;
					}
					if ($gotposter) {
						file_put_contents("$thisdirectory/cache/".$this->id."-poster.jpg", file_get_contents($poster, 0, $ctx));
						$simpleimage = new SimpleImage();
						$simpleimage->open("$thisdirectory/cache/".$this->id."-poster.jpg");
						$simpleimage->resizeIfBigger();
						$simpleimage->save();
					}
					if ($gotfanart) {
						file_put_contents("$thisdirectory/cache/".$this->id."-fanart.jpg", file_get_contents($fanart, 0, $ctx));
						$simpleimage = new SimpleImage();
						$simpleimage->open("$thisdirectory/cache/".$this->id."-fanart.jpg");
						$simpleimage->resizeIfBigger();
						$simpleimage->save();
					}
					$z->close();
					unlink("$thisdirectory/temp/".$this->id.".zip");
				} else {
					die("Failed to open $thisdirectory/temp/".$this->id.".zip");
				}
			} else print "Unable to contact TheTVDB.com";
		}
		
		public function updateShow($force = 0) {
			if ($this->thetvdbid == 0) {
				$showarray = $this->searchTheTVDBID();
				$this->extractShow();
			} elseif (($force == 1) || (dateDifference($this->lastthetvdbupdate)>=7)) {
				$this->extractShow();
			}

			if (($force == 1) || (dateDifference($this->lasteztvupdate)<=-1)) {
				$listTorrents = $this->getSeriesTorrents();
				if (count($listTorrents)>0) {
					foreach (array_keys($listTorrents) as $arraykey) {
						$torrentLink = $listTorrents[$arraykey]['torrentlink'];
						$torrentQuality = $listTorrents[$arraykey]['quality'];
						$hash = '';
						if ($this->myEpisodes[$arraykey]) {
							if (($this->quality == $torrentQuality) && (count($this->myEpisodes[$arraykey]->getTorrents($this->quality))==0) && ($this->subscribed == 1))
								$hash = $this->transmission->addTorrent($torrentLink);
							if (isset($this->myEpisodes[$arraykey]))
								$this->myEpisodes[$arraykey]->updateTorrents($torrentQuality, $torrentLink, $hash);
						}
					}
					$this->lasteztvupdate = date('Y-M-d');
					$this->lastthetvdbupdate = date('Y-M-d');
					mysql_query("UPDATE shows SET thetvdbid = '".$this->thetvdbid."', title = '".addslashes($this->title)."', lasteztvupdate = NOW(), plot = '".addslashes($this->plot)."', lastthetvdbupdate = NOW() WHERE id = '".$this->id."'");
				} else throw new Exception("REGEX for retrieving torrents HAS FAILED.");
			}
		}
		
		public function downloadTorrent($episode) {
			if ($episode->getTorrents($this->quality) != Null) {
				$torrent = $episode->getTorrents($this->quality);
				$hash = $this->transmission->addTorrent($torrent);
				if ($hash != Null) {
					mysql_query("UPDATE torrents SET hash = '$hash' WHERE link = '".$torrent['link']."'");
					return 'ok';
				} else
					return 'ko';
			} else
				return 'ko';
		}
		
		public function stopTorrent($episode) {
			$torrent = $episode->getTorrents($this->quality);
			$this->transmission->stopTorrent($torrent);
		}
		
		public function removeTorrent($episode) {
			$torrent = $episode->getTorrents($this->quality);
			$this->transmission->removeTorrent($torrent);
		}
		
		public function startTorrent($episode) {
			$torrent = $episode->getTorrents($this->quality);
			$this->transmission->startTorrent($torrent);
		}
		
		public function getNextAir() {
			$airsarray = array();
			foreach ($this->myEpisodes as $episode) {
				if (dateDifference($episode->getAired())>0)
					$airsarray[$episode->getSeason() . "x" . $episode->getEpisodeID()] = $episode->getAired();
			}
			list($episode) = array_keys($airsarray);
			return($this->myEpisodes[$episode]);
		}
		
		public function getSeasonsNumber() {
			$seasons = array();
			$seasonkeys = array_keys($this->myEpisodes);
			foreach ($seasonkeys as $key) {
				list($season, $episode) = explode('x', $key);
				if (!in_array($season, $seasons))
					$seasons[] = $season;
			}
			return count($seasons);
		}
		
		public function getEpisodesNumber() {
			return count($this->myEpisodes);
		}
		
		public function getID() {
			return $this->id;
		}
		
		public function getPlot() {
			return utf8_decode($this->plot);
		}
		
		public function getThetvdbid() {
			return $this->thetvdbid;
		}
		
		public function getTitle() {
			return utf8_decode(trim($this->title));
		}
		
		public function getLastthetvdbupdate() {
			return $this->lastthetvdbupdate;
		}
		
		public function getLasteztvupdate() {
			return $this->lasteztvupdate;
		}
		
		public function getTorrentlink() {
			return $this->torrentlink;
		}
		
		public function getQuality() {
			return $this->quality;
		}
		
		public function isSubscribed() {
			return $this->subscribed;
		}
		
		public function isSubbed() {
			return $this->subbed;
		}
		
		public function getEpisode($episode) {
			list($season, $episodeid) = explode('x', $episode);
			if (isset($this->myEpisodes[intval($season) . "x" . intval($episodeid)]))
				return $this->myEpisodes[intval($season) . "x" . intval($episodeid)];
			else
				throw new Exception('Episode not found.');
		}
		
		public function getEpisodes() {
			return $this->myEpisodes;
		}
		
		public function getSeasons() {
			$returnSeasons = array();
			foreach ($this->myEpisodes as $episode) {
				if (!is_array($returnSeasons[$episode->getSeason()]))
					$returnSeasons[$episode->getSeason()] = array();
				$returnSeasons[$episode->getSeason()][] = $episode;
			}
			return $returnSeasons;
		}
		
		public function splitSeasons() {
			$seasonsNumber = $this->getSeasonsNumber();
			$returnSplit = array(0 => array(), 1 => array());
			$seasons = $this->getSeasons();
			list($firstkey) = array_keys($seasons);
			for($i=$firstkey,$j=1;$i<=count($seasons);$i++,$j++) {
				if ($j<=round($seasonsNumber/2))
					$returnSplit[0][] = $seasons[$i];
				else
					$returnSplit[1][] = $seasons[$i];
			}
			return $returnSplit;
		}
		
		function __construct($showid, $checkEpisodes = 1) {
			$this->transmission = new Transmission();
			$q = mysql_query("SELECT shows.*, subscriptions.quality, subscriptions.subtitles, subscriptions.showid as subscribed FROM shows LEFT JOIN subscriptions ON subscriptions.showid = shows.id WHERE shows.id = '$showid'");
			if (mysql_num_rows($q)>0)
				$details = mysql_fetch_array($q, MYSQL_ASSOC);
			else
				throw new Exception('Show not found.');

			$this->id = $showid;
			$this->plot = trim($details['plot']);
			$this->thetvdbid = $details['thetvdbid'];
			$this->title = trim($details['title']);
			$this->lastthetvdbupdate = $details['lastthetvdbupdate'];
			$this->lasteztvupdate = $details['lasteztvupdate'];
			$this->torrentlink = $details['torrentlink'];
			if ($details['subscribed'] != Null) {
				$this->subscribed = 1;
				$this->quality = $details['quality'];
				$this->subbed = $details['subbed'];
			} else {
				$this->subscribed = $this->subbed = 0;
				$this->quality = getInteger('torrentquality');
			}
			$this->present = is_dir(getString('serieslocation') . '/' . sanitizeStrings($this->title));
			
			$sql = "SELECT * FROM (SELECT episodes.*, torrents.link, torrents.hash, torrents.quality FROM torrents LEFT OUTER JOIN (SELECT episodes.*, pending.TR_TORRENT_DIR, pending.TR_TORRENT_NAME, pending.TR_TORRENT_HASH, NOT (downloaded.id IS NULL) as downloaded , downloaded.subbed FROM episodes LEFT JOIN pending ON episodes.id = pending.episode AND episodes.season = pending.season AND episodes.showid = pending.showid LEFT JOIN downloaded ON episodes.id = downloaded.episodeid AND episodes.season = downloaded.season AND episodes.showid = downloaded.showid) AS episodes ON torrents.showid = episodes.showid AND torrents.season = episodes.season AND torrents.episode = episodes.id WHERE episodes.showid = '$showid' " .
					"UNION " .
				"SELECT episodes.*, torrents.link, torrents.hash, torrents.quality FROM torrents RIGHT OUTER JOIN (SELECT episodes.*, pending.TR_TORRENT_DIR, pending.TR_TORRENT_NAME, pending.TR_TORRENT_HASH, NOT (downloaded.id IS NULL) as downloaded , downloaded.subbed FROM episodes LEFT JOIN pending ON episodes.id = pending.episode AND episodes.season = pending.season AND episodes.showid = pending.showid LEFT JOIN downloaded ON episodes.id = downloaded.episodeid AND episodes.season = downloaded.season AND episodes.showid = downloaded.showid) AS episodes ON torrents.showid = episodes.showid AND torrents.season = episodes.season AND torrents.episode = episodes.id WHERE episodes.showid = '$showid') as episodes ORDER BY episodes.showid, episodes.season, episodes.id";
			$episodes = mysql_query($sql);
			if (mysql_num_rows($episodes)==0) {
				$this->updateShow();
			} else {
				if ($episodes != Null) {
					while ($array = mysql_fetch_array($episodes, MYSQL_ASSOC)) {
						$arraySeasonEpisode = intval($array['season'])."x".intval($array['id']);
						if (!isset($this->myEpisodes[intval($array['season']) . 'x' . intval($array['id'])])) {
							$this->myEpisodes[$arraySeasonEpisode] = new Episode($array, $this->title, $this->transmission, $checkEpisodes);
						} else {
							$this->myEpisodes[$arraySeasonEpisode]->addTorrent($array, $this->transmission);
						}
					}
				}
				if (($checkEpisodes == 1) || ($this->subscribed == 1)) // If we are looking for the episode list or it is subscribed, try to update it
					$this->updateShow();
			}
		}
	}
	
	class Transmission {
		private $sessionID;
		private $torrents = array();
		private $trurl;
		private $trusr;
		private $trpwd;
		
		public function retrieveStatus() {
			$parameters =	"Authorization: Basic " . base64_encode("$this->trusr:$this->trpwd") . "\r\n" .
							"X-Transmission-Session-Id: $this->sessionID\r\n";
			$post = '{ "method" : "torrent-get", "arguments" : { "fields" : ["status", "hashString", "sizeWhenDone", "leftUntilDone", "eta", "rateDownload"] } }';

			$result = retrieveURL($this->trurl, $parameters, $post);
			$resultarray = json_decode($result);
			$this->torrents = array(); $presentTorrents = array(); $i = 0;
			if ($resultarray->arguments) {
				foreach($resultarray->arguments->torrents as $torrent) {
					$size = $torrent->sizeWhenDone;
					$completed = ($torrent->sizeWhenDone - $torrent->leftUntilDone) + 1;
					if ($size == 0) {
						$percentage = 0;
						if ($torrent->status == 16) $status = $torrent->status;
						else $status = 32;
					} else {
						$status = $torrent->status;
						if (($completed > 0) && ($size > 0))
							$percentage = ($completed/$size)*100;
						else
							$percentage = 0;
					}
					$this->torrents[$torrent->hashString] = array('status' => ($status), 'size' => ($size), 'percentage' => $percentage, 'eta' => ($torrent->eta), 'rateDownload' => ($torrent->rateDownload));
					$presentTorrents[$i] = $torrent->hashString;
					$i++;
				}
				mysql_query("UPDATE torrents SET hash = '' WHERE hash NOT IN ('" . implode("', '", $presentTorrents) . "')");
			}
		}
		
		
		public function getStatus($hash = '') {
			if (($hash != '') && isset($this->torrents[$hash])) {
				return $this->torrents[$hash];
			} elseif ($hash == '') {
				return $this->torrents;
			} elseif (!isset($this->torrents[$hash])) {
				return Null;
			}
		}
		
		public function startTorrent($hashes) {
			if (!$hashes)
				throw new Exception('Torrent not specified.');
			$id = '';
			if (!isset($hashes['hash'])) {
				$ids = array();
				foreach($hashes as $hash)
					$ids[] = $hash['hash'];
				$id = implode('", "', $ids);
			} else
				$id = $hashes['hash'];
			$parameters =	"Authorization: Basic " . base64_encode($this->trusr.":".$this->trpwd) . "\r\n" .
							"X-Transmission-Session-Id: ".$this->sessionID."\r\n";
			$post = '{ "method" : "torrent-start", "arguments" : { "ids" : ["' . $id . '"] } }';
			return retrieveURL($this->trurl, $parameters, $post);
		}
		
		public function removeTorrent($hashes) {
			if (!$hashes)
				throw new Exception('Torrent not specified.');
			$id = '';
			$ids = array();
			if (!isset($hashes['hash'])) {
				foreach($hashes as $hash)
					$ids[] = $hash['hash'];
				$id = implode('", "', $ids);
			} else
				$id = $hashes['hash'];
			$parameters =	"Authorization: Basic " . base64_encode($this->trusr.":".$this->trpwd) . "\r\n" .
							"X-Transmission-Session-Id: ".$this->sessionID."\r\n";
			$post = '{ "method" : "torrent-remove", "arguments" : { "ids" : ["' . $id . '"] } }';
			if (!isset($hashes['hash']))
				$id = implode("' OR hash = '", $ids);
			mysql_query("UPDATE torrents SET hash = '' WHERE hash = '".$id."'");
			return retrieveURL($this->trurl, $parameters, $post);
		}
		
		public function stopTorrent($hashes) {
			if (!$hashes)
				throw new Exception('Torrent not specified.');
			$id = '';
			if (!isset($hashes['hash'])) {
				$ids = array();
				foreach($hashes as $hash)
					$ids[] = $hash['hash'];
				$id = implode('", "', $ids);
			} else
				$id = $hashes['hash'];
			$parameters =	"Authorization: Basic " . base64_encode($this->trusr.":".$this->trpwd) . "\r\n" .
							"X-Transmission-Session-Id: ".$this->sessionID."\r\n";
			$post = '{ "method" : "torrent-stop", "arguments" : { "ids" : ["' . $id . '"] } }';
			return retrieveURL($this->trurl, $parameters, $post);
		}
		
		public function addTorrent($torrent) {
			global $serieslocation;
			if (is_array($torrent))
				$torrentlink = $torrent['link'];
			else
				$torrentlink = $torrent;
			$parameters =	"Authorization: Basic ".base64_encode($this->trusr.":".$this->trpwd)."\r\n" .
							"X-Transmission-Session-Id: ".$this->sessionID."\r\n";
			$post = '{ "method" : "torrent-add", "arguments" : { "paused" : 0, "download-dir" : "' . $serieslocation . '", "filename" : "' . $torrentlink . '" } }';
			$result = retrieveURL($this->trurl, $parameters, $post);
			$resultarray = json_decode($result);
			if ($resultarray->result == 'success')
				return $resultarray->{'arguments'}->{'torrent-added'}->{'hashString'};
			else
				return Null;
		}
		function __construct() {
			$this->trurl = getString('transmissionurl');
			$this->trusr = getString('transmissionusername');
			$this->trpwd = getString('transmissionpassword');
			$parameters =	"Authorization: Basic ".base64_encode($this->trusr.":".$this->trpwd)."\r\n";
			$content = retrieveURL($this->trurl, $parameters);
			preg_match("/<code>X-Transmission-Session-Id: ([A-Za-z0-9=]*)<\/code>/", $content, $match);
			$this->sessionID = $match[1];
			$this->retrieveStatus();
		}
	}
?>
