<?php
	if (isset($_GET['ajax']))
		include 'functions.php';
	else
		include 'header.php';
	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		saveString('thetvdblanguage', addslashes($_POST['thetvdblanguage']));
		saveString('subtitlelanguage', addslashes($_POST['subtitlelanguage']));
		saveString('subtitlescript', addslashes($_POST['subtitlescript']));
		saveInteger('torrentquality', addslashes($_POST['torrentquality']));
		saveString('serieslocation', addslashes($_POST['serieslocation']));
		saveString('transmissionurl', addslashes($_POST['transmissionurl']));
		saveString('transmissionusername', addslashes($_POST['transmissionusername']));
		if (($_POST['transmissionusername']!='') && ($_POST['transmissionpassword'] != '')) {
			saveString('transmissionpassword', addslashes($_POST['transmissionpassword']));
		}
	} else {
		$thetvdblanguage = getString('thetvdblanguage');
		$subtitlelanguage = getString('subtitlelanguage');
		$subtitlescript = getString('subtitlescript');
		$torrentquality = getInteger('torrentquality');
		$serieslocation = getString('serieslocation');
		$transmissionurl = str_replace('/transmission/rpc/', '', getString('transmissionurl'));
		$transmissionusername = getString('transmissionusername');
	}
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
?>
						<div class="contentbox normalbox configuration">
							<form action="configuration.php?ajax=1" method="post">
								<h3>Language Configuration</h3>
								<ul class="configuration">
									<li>
										<label>Tv shows Language:</label>
										<select name="thetvdblanguage">
											<option value="cz"<?php if ($thetvdblanguage == 'cz') echo ' selected="selected"'?>>Cesky</option>
											<option value="dk"<?php if ($thetvdblanguage == 'dk') echo ' selected="selected"'?>>Dansk</option>
											<option value="de"<?php if ($thetvdblanguage == 'de') echo ' selected="selected"'?>>Deutsch</option>
											<option value="en"<?php if ($thetvdblanguage == 'en') echo ' selected="selected"'?>>English</option>
											<option value="es"<?php if ($thetvdblanguage == 'es') echo ' selected="selected"'?>>Espa&ntilde;ol</option>
											<option value="fr"<?php if ($thetvdblanguage == 'fr') echo ' selected="selected"'?>>Fran&ccedil;ais</option>
											<option value="hr"<?php if ($thetvdblanguage == 'hr') echo ' selected="selected"'?>>Hrvatski</option>
											<option value="it"<?php if ($thetvdblanguage == 'it') echo ' selected="selected"'?>>Italiano</option>
											<option value="hu"<?php if ($thetvdblanguage == 'hu') echo ' selected="selected"'?>>Nagyar</option>
											<option value="nl"<?php if ($thetvdblanguage == 'nl') echo ' selected="selected"'?>>Nederlands</option>
											<option value="no"<?php if ($thetvdblanguage == 'no') echo ' selected="selected"'?>>Norsk</option>
											<option value="pl"<?php if ($thetvdblanguage == 'pl') echo ' selected="selected"'?>>Polski</option>
											<option value="pt"<?php if ($thetvdblanguage == 'pt') echo ' selected="selected"'?>>Portugu&ecirc;s</option>
											<option value="sk"<?php if ($thetvdblanguage == 'sk') echo ' selected="selected"'?>>Slovenski</option>
											<option value="fi"<?php if ($thetvdblanguage == 'fi') echo ' selected="selected"'?>>Suomeksi</option>
											<option value="sv"<?php if ($thetvdblanguage == 'sv') echo ' selected="selected"'?>>Svenska</option>
											<option value="tr"<?php if ($thetvdblanguage == 'tr') echo ' selected="selected"'?>>T&uuml;rk&ccedil;e</option>
										</select>
									</li>
									<li>
										<label>Default subtitle Lang.:</label>
										<select name="subtitlelanguage">
											<option value="none"<?php if ($subtitlelanguage == 'none') echo ' selected="selected"'?>>None</option>
											<option value="cz"<?php if ($subtitlelanguage == 'cz') echo ' selected="selected"'?>>Cesky</option>
											<option value="dk"<?php if ($subtitlelanguage == 'dk') echo ' selected="selected"'?>>Dansk</option>
											<option value="de"<?php if ($subtitlelanguage == 'de') echo ' selected="selected"'?>>Deutsch</option>
											<option value="en"<?php if ($subtitlelanguage == 'en') echo ' selected="selected"'?>>English</option>
											<option value="es"<?php if ($subtitlelanguage == 'es') echo ' selected="selected"'?>>Espa&ntilde;ol</option>
											<option value="fr"<?php if ($subtitlelanguage == 'fr') echo ' selected="selected"'?>>Fran&ccedil;ais</option>
											<option value="hr"<?php if ($subtitlelanguage == 'hr') echo ' selected="selected"'?>>Hrvatski</option>
											<option value="it"<?php if ($subtitlelanguage == 'it') echo ' selected="selected"'?>>Italiano</option>
											<option value="hu"<?php if ($subtitlelanguage == 'hu') echo ' selected="selected"'?>>Nagyar</option>
											<option value="nl"<?php if ($subtitlelanguage == 'nl') echo ' selected="selected"'?>>Nederlands</option>
											<option value="no"<?php if ($subtitlelanguage == 'no') echo ' selected="selected"'?>>Norsk</option>
											<option value="pl"<?php if ($subtitlelanguage == 'pl') echo ' selected="selected"'?>>Polski</option>
											<option value="pt"<?php if ($subtitlelanguage == 'pt') echo ' selected="selected"'?>>Portugu&ecirc;s</option>
											<option value="sk"<?php if ($subtitlelanguage == 'sk') echo ' selected="selected"'?>>Slovenski</option>
											<option value="fi"<?php if ($subtitlelanguage == 'fi') echo ' selected="selected"'?>>Suomeksi</option>
											<option value="sv"<?php if ($subtitlelanguage == 'sv') echo ' selected="selected"'?>>Svenska</option>
											<option value="tr"<?php if ($subtitlelanguage == 'tr') echo ' selected="selected"'?>>T&uuml;rk&ccedil;e</option>
										</select>
									</li>
									<li>
										<label>Subtitle Script:</label>
										<select name="subtitlescript"><?=listSubtitleScripts()?></select>
									</li>
									<li>
										<label>Show quality:</label>
										<select name="torrentquality">
											<option value="0"<?php if ($torrentquality == 0) echo ' selected="selected"'?>>HDTV</option>
											<option value="1"<?php if ($torrentquality == 1) echo ' selected="selected"'?>>DVDRIP</option>
											<option value="2"<?php if ($torrentquality == 2) echo ' selected="selected"'?>>720p</option>
										</select>
									</li>
									<li>
										<label>Series location:</label>
										<input type="text" name="serieslocation" value="<?=$serieslocation?>" />
									</li>
									<li>
										<label>Transmission URL:</label>
										<input type="text" name="transmissionurl" value="<?=$transmissionurl?>" />
									</li>
									<li>
										<label>Transmission username:</label>
										<input type="text" name="transmissionusername" value="<?=$transmissionusername?>" />
									</li>
									<li>
										<label>Transmission password:</label>
										<input type="password" name="transmissionpassword" />
									</li>
									<li>
										<label>Save Settings:</label>
										<input type="submit" value="Save" />
									</li>
								</ul>
							</form>
						</div>
<?php
	}
	
	if (!isset($_GET['ajax']))
		include 'footer.php';
	
	function listSubtitleScripts() {
		$dirarray = scandir("subtitles/");
		$scripts = array();
		for($i=2;$i<count($dirarray);$i++) {
			if (substr($dirarray[$i], -4) == '.php') {
				$scriptName = str_replace(".php","",$dirarray[$i]);
				if (subtitlescript == $scriptname)
					$selected = ' selected="selected"';
				else
					$selected = '';
				echo "<option value='$scriptName'$selected>$scriptName</option>";
			}
		}
	}
?>