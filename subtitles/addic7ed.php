<?php
	function fetchSubtitles($showtitle, $season, $episode, $language) {
		$englang = array(	it => 'Italian',
							en => 'English',
							es => 'Spanish',
							ro => 'Romanian',
							pt => 'Portuguese',
							fr => 'French',
							hu => 'Hungarian',
							ru => 'Russian',
							de => 'German',
							sw => 'Swedish',
							dk => 'Dutch');
		
		/* Gathering TV Series Names */
		$data = file_get_contents("http://www.addic7ed.com/shows.php");
		preg_match_all("/\"Letter1\">(.*)/", $data, $matches);
		$series = $matches[1][0];
		preg_match_all('/href="([0-z\/]*)">(.*?)<\/a>/', $series, $matches);
		$links = $matches[1];
		$names = $matches[2];
		unset($matches); unset($data); unset($series);
		for($i=0; $i<=count($links); $i++)
			$series[strtolower($names[$i])] = $links[$i];
		unset($names); unset($links); 

		/* Gathering Episodes */
		if ($series[strtolower($showtitle)] == Null)
			return Null;
		preg_match_all("/\/show\/(.*)/", $series[strtolower($showtitle)], $matches);
		$link = $matches[1][0];
		$data = str_replace("\n","",file_get_contents("http://www.addic7ed.com/ajax_loadShow.php?show=$link&season=$season"));
		preg_match_all("/ - [0-9]*x[0]*$episode.*?".$englang[$language].".*?>([0-9\.\% ]*)?Completed.*?(\/updated.*?)\".*?<\/table>/", $data, $matches);
		$sublink = $matches[2][0];
		$completed = $matches[1][0];
		unset($link); unset($matches); unset($data);
		
		/* Subtitle download */
		if (($sublink == Null) || ($completed != ''))
			return Null;
		$subtitle = file_get_contents("http://www.addic7ed.com".$sublink);
		if (strrpos($subtitle, "Daily Download count exceeded") === false) {
			if ($subtitle != Null)
				return $subtitle;
			else
				return Null;
		} else
			return null;
	}
?>