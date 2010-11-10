jQuery.fn.exists = function(){return jQuery(this).length>0;}
$(document).ready(function () {
	$('.manualaddtorrent').live('click', function () {
		var url = $(this).parent().parent().children('a').attr('href').replace('episode.php?','addmanualtorrent.php?');
		var myParent = $(this).parent().parent();
		myParent.children('span').children('.manualaddtorrent').animate({ width: '0', opacity: 'toggle' });
		myParent.append('<div class="manuallink"><form name="manuallinkform" method="post" action="addtorrent.php?' + url + '"><label>Manual torrent link: </label><input type="text" name="torrenturl" /><input type="submit"></form></div>');
		myParent.children('.manuallink').hide().slideDown();
		myParent.children('.manuallink').children('form').submit(function () {
			$(this).slideUp();
			var thisdownload = myParent.children('span').children('span.manualaddtorrent');
			thisdownload.html('<img style="width: 18px;" src="images/loading.gif" title="Loading..." />');
			thisdownload.animate( { width: '18px', opacity: 'toggle' });
			thisdownload.attr('class', 'loading');
			var data = $(this).serialize();
			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				success: function(data) {
					thisdownload.animate({ width: '0', opacity: 'toggle' }, function () {
						if (data == 'ok') {
							thisdownload.html('<img style="width: 18px;" src="images/download.png" title="Download" />');
							thisdownload.attr('class', 'downloadtorrent');
						} else {
							thisdownload.html('<img style="width: 18px;" src="images/manual_torrent.png" title="Manual Torrent" alt="Manual Torrent" />');
							thisdownload.attr('class', 'manualaddtorrent');
						}
					}).animate({ width: '18px', opacity: 'toggle'});
				}
			});
			return false;
		});
	});
	$('.downloadtorrent').live('click', function () {
		var url = $(this).parent().parent().children('a').attr('href').replace('episode.php?', 'downloadtorrent.php?');
		var thisdownload = $(this);
		$(this).animate({ width: '0', opacity: 'toggle' }, function () {
			$(this).html('<img style="width: 18px;" src="images/loading.gif" title="Loading..." />');
		}).animate({ width: '18px', opacity: 'toggle'});
		$(this).attr('class', 'loading');
		$.get(url, function(data) {
			if (data == 'ok') {
				var icon = 'downloading';
				thisdownload.attr('class', 'downloadingtorrent');
				var icontext = 'Downloading...';
			} else {
				var icon = 'cross';
				thisdownload.attr('class', 'downloadtorrent');
				var icontext = 'Error. Try again.';
			}
			thisdownload.animate({ width: '0', opacity: 'toggle' }, function () {
				thisdownload.html('<img src="images/' + icon + '.png" title="' + icontext + '" />');
			}).animate({ width: '18px', opacity: 'toggle'});
		});
	});
	$('.downloadingtorrent').live('click', function () {
		var url = $(this).parent().parent().children('a').attr('href').replace('episode.php?', 'downloadtorrent.php?') + '&pause=1';
		$.get(url);
		$(this).animate({ width: '0', opacity: 'toggle' }, function () {
			$(this).attr('class', 'pausedtorrent');
			$(this).html('<img src="images/paused.png" title="Paused" />');
		}).animate({ width: '18px', opacity: 'toggle'});
	});
	$('.pausedtorrent').live('click', function() {
		var url = $(this).parent().parent().children('a').attr('href').replace('episode.php?', 'downloadtorrent.php?') + '&resume=1';
		$.get(url);
		$(this).animate({ width: '0', opacity: 'toggle' }, function () {
			$(this).attr('class', 'downloadingtorrent');
			$(this).html('<img src="images/downloading.png" title="Downloading..." />');
		}).animate({ width: '18px', opacity: 'toggle'});
	})
	$('.dldmissingeps').live('click', function () {
		$(this).parent().parent().children('ul').children('li').each(function () {
			var thisepisode = $(this).children('span').children('.downloadtorrent');
			if (thisepisode.exists()) {

				var url = thisepisode.parent().parent().children('a').attr('href').replace('episode.php?', 'downloadtorrent.php?');
				thisepisode.animate({ width: '0', opacity: 'toggle' }, function () {
					$(this).html('<img style="width: 18px;" src="images/loading.gif" title="Loading..." />');
				}).animate({ width: '18px', opacity: 'toggle'});
				thisepisode.attr('class', 'loading');
				$.get(url, function(data) {
					if (data == 'ok') {
						var icon = 'downloading';
						thisepisode.attr('class', 'downloadingtorrent');
						var icontext = 'Downloading...';
					} else {
						var icon = 'cross';
						thisepisode.attr('class', 'downloadtorrent');
						var icontext = 'Error. Try again.';
					}
					thisepisode.animate({ width: '0', opacity: 'toggle' }, function () {
						thisepisode.html('<img src="images/' + icon + '.png" title="' + icontext + '" />');
					}).animate({ width: '18px', opacity: 'toggle'});
				});
			}
		});
		return false;
	});
	$('.subscribe').live('click', function () {
		var url = $(this).attr('href');
		$(this).children('span').fadeOut('fast', function() {
			$.get(url);
			if ($(this).text() == 'Subscribe') {
				$(this).text('Unsubscribe');
				$(this).parent().attr('href', url + '&cancel=1');
			} else if ($(this).text() == 'Unsubscribe') {
				$(this).text('Subscribe');
				$(this).parent().attr('href', url.replace('&cancel=1',''));
			}
			$(this).fadeIn('fast');
		});
		return false;
	});
	$('.episodedetails #showtitle').live('click', function () {
		var url = $(this).attr('href');
			if ($('.seriesdetails').exists()) {
				$('html, body').animate({scrollTop:0}, 'fast');
				$('.seriesdetails').slideUp('fast', function () {
					$('.seriesdetails').remove();
					$.get(url + '&ajax=1', function(data) {
						$('.content').prepend(data).children('.seriesdetails').hide();
						$('.seriesdetails').slideDown();
					});
				});
			} else {
				$('html, body').animate({scrollTop:0}, 'fast');
				$('.episodedetails').slideUp('fast', function () {
					$('.episodedetails').remove();
					$.get(url + '&ajax=1', function(data) {
						$('.content').prepend(data).children('.seriesdetails').hide();
						$('.seriesdetails').slideDown();
					});
				});
			}
		return false;
	});
	$('.seasons ul li a').live('click', function () {
		var url = $(this).attr('href');
			if ($('.seriesdetails').exists()) {
				$('html, body').animate({scrollTop:0}, 'fast');
				$('.seriesdetails').slideUp('fast', function () {
					$('.seriesdetails').remove();
					$.get(url + '&ajax=1', function(data) {
						$('.content').prepend(data).children('.episodedetails').hide();
						$('.episodedetails').slideDown();
					});
				});
			} else {
				$('html, body').animate({scrollTop:0}, 'fast');
				$('.episodedetails').slideUp('fast', function () {
					$('.episodedetails').remove();
					$.get(url + '&ajax=1', function(data) {
						$('.content').prepend(data).children('.episodedetails').hide();
						$('.episodedetails').slideDown();
					});
				});
			}
		return false;
	});
	$('#configuration').live('click', function () {
		if ($('.configuration').exists()) {
			$('.configuration').slideUp('fast', function () {
				$('.configuration').remove();
			});
		} else {
			$.get('configuration.php?ajax=1', function(data) {
				$('.content').prepend(data);
				$('.configuration').toggle().slideDown();
				$('.configuration').children('form').submit(function () {
					$('.configuration').slideUp('fast', function () {
						var url = $('.configuration form').attr('action');
						var data = $('.configuration form').serialize();
						var a = $.ajax({
							type: 'POST',
							url: url,
							data: data
						});
						$('.configuration').remove();
					});
					return false;
				});
			});
		}
		return false;
	});
	/*$('#login').live('click', function () {
		$(this).parent().parent().animate({paddingLeft:0, paddingRight: 0, marginLeft: 0, width: 0}, 'slow');
		$(this).fadeOut(function() {
			$(this).remove();
		});
		return false;
	});*/
	$('#dialog').dialog({
		autoOpen: false,
		height: 270,
		width: 450,
		resizable: false,
		draggable: false,
		modal: true,
		hide: 'fold',
		show: 'fold',
		buttons: {
			'Subscribe': function() {
				var text = $(this).children('select').children('option:selected').attr('id');
				actionTorrent(text.replace('episode.php?', 'subscribe.php?from=1&'));
				$(this).children('select').html('');
				$(this).dialog('close');
			},
			'Cancel': function () { $(this).dialog('close'); }
		}
	});
	$('.subscribedialog').live('click', function () {
		$('#dialog').dialog('option','title','Subscribe');
		$('#dialog').dialog('option','buttons', {
			'Subscribe': function() {
				var url = $(this).children('select#episode').children('option:selected').attr('id');
				var quality = $(this).children('select#quality').children('option:selected').attr('id');
				$.get(url.replace('episode.php?', 'subscribe.php?from=1&') + '&quality=' + quality, function (data) {
					window.location = url;
				});
			},
			'Next one': function () { 
				var url = $(this).children('select#episode').children('option:selected').attr('id');
				var quality = $(this).children('select#quality').children('option:selected').attr('id');
				var language = $(this).children('select#language').children('option:selected').attr('id');
				$.get(url.replace('episode.php?', 'subscribe.php?') + '&quality=' + quality + '&language=' + language, function (data) {
					$('.subscribedialog').html('<span>Unsubscribe</span>');
					$('.subscribedialog').attr('href', url.replace('episode.php?', 'subscribe.php?') + '&cancel=1');
					$('.subscribedialog').attr('class','subscribe');
					$(this).dialog('close');
				});
			},
			'Cancel': function () { $(this).dialog('close'); }
		});
		$('#dialog').html('<p style="text-align: justify;">Which episode to start subscription?</p><select id="episode" style="width: 370px"></select><p style="text-align: justify;">Select quality:</p><select id="quality"><option id="0">HDTV</option><option id="1">DVDRIP</option><option id="2">720p</option></select><p style="text-align: justify;">Select subtitle language:</p><select id="language"><option id="none" selected="selected">None</option><option id="cz">Cesky</option><option id="dk">Dansk</option><option id="de">Deutsch</option><option id="en">English</option><option id="es">Espa&ntilde;ol</option><option id="fr">Fran&ccedil;ais</option><option id="hr">Hrvatski</option><option id="it">Italiano</option><option id="hu">Nagyar</option><option id="nl">Nederlands</option><option id="no">Norsk</option><option id="pl">Polski</option><option id="pt">Portugu&ecirc;s</option><option id="sk">Slovenski</option><option id="fi">Suomeksi</option><option id="sv">Svenska</option><option id="tr">T&uuml;rk&ccedil;e</option></select>');
		$('.fullcontent').children().each(function() {
			$(this).children().each(function () {
				$(this).children('ul').each(function () {
					$(this).children('li').each(function () {
						episodeIcon = $(this).children('span').children('span').children('img').attr('src');
						//if ((episodeIcon != 'images/future_episode.png') && (episodeIcon != 'images/unknown_airing.png')) {
						if (episodeIcon == 'images/download.png') {
							var seasoncontainer = $(this).children('a').attr('href');
							var re = new RegExp('season=([0-9]*)\&');
							season = re.exec(seasoncontainer)[1];
							$('#dialog select#episode').prepend('<option id="' + $(this).children('a').attr('href') + '">' + season + 'x' + $(this).children('a').text() + '</option>');
						}
					});
				});
			});
		});
		$('#dialog').dialog('open');
		return false;
	});
	$('.progressbar').progressbar();
	
	$('.thumb').mouseenter(function () {
		$(this).prepend('<div class="hiddenmenu"><img src="images/resume.png" /><img src="images/pause.png" /><img src="images/remove.png" /></div>');
		$(this).children('.hiddenmenu').animate({bottom: '0'}, {queue: false});
	});
	$('.thumb').mouseleave(function () {
		$(this).children('.hiddenmenu').animate({bottom: '-20px'}, {queue: false, complete: function () {
			$(this).remove();
		}});
	});
	$('.hiddenmenu img').live('click', function () {
		var url = $(this).parent().parent().parent().parent().children('h2').children('a:nth-child(2)').attr('href').replace('episode.php?', 'downloadtorrent.php?');
		var imgsrc = $(this).attr('src');
		if (imgsrc.indexOf('resume')>0)
			var command = '&resume=1';
		else if (imgsrc.indexOf('pause')>0)
			var command = '&pause=1';
		else if (imgsrc.indexOf('remove')>0)
			var command = '&remove=1';
		$.get(url+command);
	});
	
	if ($('.beingdownloaded .downloaddetails .imagecontainer .progressbar').exists()) {
		window.setInterval(function () {
			$('.beingdownloaded .downloaddetails').each(function () {
				var hash = $(this).children('h2').attr('id');
				var h2 = $(this).children('h2');
				if (!hash) {
					var hashlink = h2.children('a:nth-child(2)').attr('href').replace('episode.php?','downloadstatus.php?') + '&getHash=1';
					$.getJSON(hashlink, function(data) {
						h2.attr('id', data['hash']);
					});
					return;
				}
				var link = $(this).children('h2').children('a:nth-child(2)').attr('href').replace('episode.php?','downloadstatus.php?') + '&hash=' + hash;
				var progressepisode = $(this).children('.imagecontainer').children('.progressbar');
				var episodedetails = $(this).children('.imagecontainer').children('.details');
				var thisdownload = $(this);
				$.getJSON(link, function(data) {
					if (data != null) {
						if (data['status'] != 8) {
							progressepisode.progressbar('option','value', data['percentage']);
							if (data['status'] == 32)
								episodedetails.html('ETA: ' + secondsToTime(data['eta']) + '<br />Status: Retrieving Metadata - Rate: ' + Math.round(data['rateDownload']/1024) + 'KB/s');
							else
								episodedetails.html('ETA: ' + secondsToTime(data['eta']) + '<br />Status: ' + decodeStatus(data['status']) + ' - Rate: ' + Math.round(data['rateDownload']/1024) + 'KB/s');
						} else {
							thisdownload.slideUp(function () {
								thisdownload.children('.imagecontainer').children('.progressbar').remove();
								thisdownload.children('.imagecontainer').children('.details').remove();
								var thiscontent = '<div class="downloaddetails">' + thisdownload.html() + '</div>';
								var thish3 = thisdownload.parent().parent().children('.downloadedtorrents').children('h3').html();
								thisdownload.parent().parent().children('.downloadedtorrents').children('h3').remove();
								thisdownload.parent().parent().children('.downloadedtorrents').prepend(thiscontent).children('.downloaddetails:nth-child(1)').hide().slideDown();
								thisdownload.parent().parent().children('.downloadedtorrents').prepend('<h3>' + thish3 + '</h3>');
								thisdownload.remove();
								if (!$('.beingdownloaded').children('.downloaddetails').exists()) {
									$('.beingdownloaded').slideUp(function () {
										$(this).remove();
									});
								}
							});
						}
					} else {
						thisdownload.slideUp(function () {
							$(this).remove();
							if (!$('.beingdownloaded').children('.downloaddetails').exists()) {
								$('.beingdownloaded').slideUp(function () {
									$(this).remove();
								});
							}
						});
					}
				});
			});
		}, 1000);
	}
	$('.imagecontainer a').live('click', function () {
		var imagelink = $(this).children('img.thumbnail').attr('src');
		var height = $('body').height();
		$('body').append('<div id="lightbox"></div><div id="lightbox-panel"><img src="images/ajaxLoader.gif" /></div>');
		$('#lightbox').css({opacity: '0', background: '#000', top: '0', left: '0', position: 'absolute', width: '100%', height: height+91, 'z-index': '1000'});
		$('#lightbox-panel').css({'max-width': '800px', 'max-height': '700px', opacity: '0', position: 'fixed', top: '30px', background: '#FFF', left: '50%', 'margin-left': '0', width: 'auto', padding: '10px', border: '2px solid #CCCCCC', 'z-index': '1001'});
		$('#lightbox-panel img').css({'max-width': '800px', 'max-height': '700px'});
		$('#lightbox').animate({opacity: '0.9'},500);
		$('#lightbox-panel').animate({opacity: '1'}, 500);
		$('#lightbox-panel img').load(function () {
			var objImagePreloader = new Image();
			objImagePreloader.onload = function() {
				$('#lightbox-panel img').attr('src', imagelink);
				var imagewidth = 0;
				if (objImagePreloader.height > objImagePreloader.width) {
					if (objImagePreloader.height>700) {
						imagewidth = (objImagePreloader.width/objImagePreloader.height)*700;
						imageheight = 700;
					} else {
						imageheight = objImagePreloader.height;
						imagewidth = objImagePreloader.width;
					}
					$('#lightbox-panel img').animate({height: '700px'}, {queue: false});
				} else {
					if (objImagePreloader.width>800)
						imagewidth = 800;
					else
						imagewidth = objImagePreloader.width;
					$('#lightbox-panel img').animate({width: imagewidth}, {queue: false});
				}
				$('#lightbox-panel').animate({marginLeft: -(imagewidth/2), marginTop: '30px'}, {queue: false});
				objImagePreloader.onload=function(){};
			};
			objImagePreloader.src = imagelink;
		});
		return false;
	});
	$('#lightbox').live('click', function() {
		$(this).animate({opacity: '0'}, 500, function() {
			$(this).remove();
		});
		$('#lightbox-panel').animate({opacity: '0'}, 500, function() {
			$(this).children('img').remove();
			$(this).remove();
		});
	});
	$('#lightbox-panel').live('click', function() {
		$(this).animate({opacity: '0'}, 500, function() {
			$(this).remove();
		});
		$('#lightbox').animate({opacity: '0'}, 500, function() {
			$(this).remove();
		});
	});
});

function decodeStatus(s) {
	if (s == 1) return 'Check wait';
	else if (s == 2) return 'Checking';
	else if (s == 4) return 'Downloading';
	else if (s == 8) return 'Seeding';
	else if (s == 16) return 'Stopped';
}

function secondsToTime(t) {
	if (t<0) return 'Unknown';
	var h = Math.floor(t / 3600);
	t %= 3600;
	var m = Math.floor(t / 60);
	var s = Math.floor(t % 60);
	return((h > 0 ? h + ' hr' + ((h > 1) ? 's ' : ' ') : '') +
				 (m > 0 ? m + ' min' + ((m > 1) ? 's ' : ' ') : '') +
				 s + ' sec' + ((s > 1) ? 's' : ''));			
}
