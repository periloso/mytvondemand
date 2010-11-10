#!/usr/bin/env python

import os, re, urllib2, syslog
import MySQLdb
from easytvdb import EasyTVDB
from xbmchelper import XBMCHelper

def parseConfigPHP():
	config = open(myPath + '/config.php', 'r').read().splitlines()
	keys = {}
	for line in config:
		match = re.search('\$(.*?)=(.*);', line)
		try: # Check if it's a string or a number
			if ((match.group(2).strip()[0] != '"') and (match.group(2).strip()[0] != '\'')):
				keys[match.group(1).strip()] = int(match.group(2).strip())
			else:
				keys[match.group(1).strip()] = match.group(2).strip('"\' ')
		except:
			pass
	return keys

def processTorrent(torrentName, torrentDir, torrentHash):
	global config, db, XHelper, easytv
	cursor = db.cursor(MySQLdb.cursors.DictCursor)
	if not os.path.exists(torrentDir + "/" + torrentName):
		syslog.syslog("Torrent doesn't exists! (" + torrentDir + "/" + torrentName + ") " + torrentHash)
		sql = "DELETE FROM pending WHERE TR_TORRENT_HASH = '" + torrentHash + "'"
		if (config['debug']):
			syslog.syslog("SQL: " + sql)
		cursor.execute(sql)
		return()

	sql = "SELECT episodes.*, shows.* FROM pending JOIN episodes ON pending.episode = episodes.id AND pending.season = episodes.season AND pending.showid = episodes.showid JOIN shows ON episodes.showid = shows.id WHERE LOWER(pending.TR_TORRENT_HASH) = LOWER('" + torrentHash + "')"
	numRows = cursor.execute(sql)

	if (numRows > 0):
		result = cursor.fetchall()[0]
		filename = {}
		filename['original'] = torrentDir + '/' + torrentName
		filename['path'] = '%s/%s' % (config['showspath'], easytv.stripper(result['shows.title'], 1))
		filename['outfile'] = '%s - %02dx%02d - %s' % (easytv.stripper(result['shows.title'], 1), result['season'], result['id'], easytv.stripper(result['title'], 1))
		filename['extension'] = os.path.splitext(filename['original'])[1]
		if os.path.isdir(filename['original']):
			if (config['debug']):
				syslog.syslog("File " + filename['original'] + " is contained into a directory. Aborting.\n\n")
			sql = "DELETE FROM pending WHERE TR_TORRENT_HASH = '" + torrentHash + "'"
			if (config['debug']):
				syslog.syslog("SQL: " + sql)
			cursor.execute(sql)
			return()
		try:
			showdict = easytv.tvshowToDict(result['thetvdbid'], config['thetvdblanguage'])
		except:
			print "Timeout from TheTvDB.com"
			return()
		showid = str(result['thetvdbid'])
		ourEpisode = showdict[showid]['Episodes']['%02dx%02d' % (result['season'], result['id'])]
		
		if not os.path.exists(filename['path']):
			if (config['debug']):
				syslog.syslog("Creating directory " + filename['path'])
			os.makedirs(filename['path'])
		tDestination = '%s/%s%s' % (filename['path'], filename['outfile'], filename['extension'])
		if os.path.exists(tDestination):
			if (config['debug']):
				syslog.syslog("Destination " + tDestination + " already exists. Aborting.")
			sql = "DELETE FROM pending WHERE TR_TORRENT_HASH = '" + torrentHash + "'"
			if (config['debug']):
				syslog.syslog("SQL: " + sql)
			cursor.execute(sql)
			return()
		else:
			if (config['debug']):
				syslog.syslog("Moving file " + filename['outfile'])
			os.system('/bin/mv "%s" "%s"' % (filename['original'], filename['path'] + '/' + filename['outfile'] + filename['extension']))
		if not os.path.exists('%s/%s.tbn' % (filename['path'], filename['outfile'])):
			if (config['debug']):
				syslog.syslog("Downloading thumbnail")
			try:
				thumb = urllib2.urlopen(ourEpisode['filename']).read()
				open(filename['path'] + '/' + filename['outfile'] + '.tbn', 'w').write(thumb)
			except:
				pass
		if not os.path.exists(filename['path'] + '/fanart.jpg'):
			if (config['debug']):
				syslog.syslog("Downloading Show fanart")
			for element in showdict[showid]['Banners']:
				if ((element['BannerType'] == 'fanart') and ((element['Language'] == config['thetvdblanguage']) or (element['Language'] == 'en'))):
					open(filename['path'] + '/fanart.jpg', 'w').write(urllib2.urlopen(element['BannerPath']).read())
					break
		if not os.path.exists(filename['path'] + '/folder.jpg'):
			if (config['debug']):
				syslog.syslog("Downloading Show poster")
			for element in showdict[showid]['Banners']:
				if ((element['BannerType'] == 'poster') and ((element['Language'] == config['thetvdblanguage']) or (element['Language'] == 'en'))):
					open(filename['path'] + '/folder.jpg', 'w').write(urllib2.urlopen(element['BannerPath']).read())
					showposter = element['BannerPath']
					break
		if not os.path.exists(filename['path'] + '/season-all.tbn'):
			if (config['debug']):
				syslog.syslog("Downloading all seasons poster")
			for element in showdict[showid]['Banners']:
				try:
					if (((element['BannerType'] == 'poster') and (element['BannerPath'] != showposter)) and ((element['Language'] == config['thetvdblanguage']) or (element['Language'] == 'en'))):
						open(filename['path'] + '/season-all.tbn', 'w').write(urllib2.urlopen(element['BannerPath']).read())
						break
				except NameError:
					try: # If can't find anything... try to take another (whatever) poster...
						for element in showdict[showid]['Banners']:
							if (element['BannerType'] == 'poster'):
								open(filename['path'] + '/season-all.tbn', 'w').write(urllib2.urlopen(element['BannerPath']).read())
								break
					except:
						pass
		if not os.path.exists(filename['path'] + '/season%02d.tbn' % result['season']):
			if (config['debug']):
				syslog.syslog("Downloading season poster")
			for element in showdict[showid]['Banners']:
				if (((element['BannerType2'] == 'season') and (element['Season'] == ourEpisode['SeasonNumber'])) and ((element['Language'] == config['thetvdblanguage']) or (element['Language'] == 'en'))):
					open(filename['path'] + '/season%02d.tbn' % result['season'], 'w').write(urllib2.urlopen(element['BannerPath']).read())
					break
		if not os.path.exists('%s/%s.nfo' % (filename['path'], filename['outfile'])):
			if (config['debug']):
				syslog.syslog("Creating episode NFO")
			generatedNFO = XHelper.createNFO(ourEpisode, showdict[showid])
			open('%s/%s.nfo' % (filename['path'], filename['outfile']), 'w').write(generatedNFO)
		if not os.path.exists(filename['path'] + '/tvshow.nfo'):
			if (config['debug']):
				syslog.syslog("Creating Show NFO")
			generatedNFO = XHelper.createNFO(showdict[showid])
			open(filename['path'] + '/tvshow.nfo','w').write(generatedNFO)
		sql = "INSERT INTO downloaded (episodeid, season, showid, subbed) VALUES ('" + str(result['id']) + "', '" + str(result['season']) + "', '" + str(result['showid']) + "', 0)"
		if (config['debug']):
			syslog.syslog("SQL: " + sql)
		cursor.execute(sql)
		sql = "DELETE FROM pending WHERE TR_TORRENT_HASH = '" + torrentHash + "'"
		if (config['debug']):
			syslog.syslog("SQL: " + sql)
		cursor.execute(sql)
		os.system(myPath + '/subtitles.php')
	else:
		if (config['debug']):
			syslog.syslog("Can't find the torrent hashString '" + torrentHash + "'. Follows the SQL\n" + sql)


myPath = os.path.dirname(os.path.realpath(__file__))
config = parseConfigPHP()
db = MySQLdb.connect(host=config['dbhost'], user=config['dbuser'], passwd=config['dbpass'], db=config['dbname'])
cursor = db.cursor(MySQLdb.cursors.DictCursor)
XHelper = XBMCHelper()
easytv = EasyTVDB(config['thetvdbapikey'])

if (os.environ.get("TR_TORRENT_NAME") != None):
	tName = os.environ.get("TR_TORRENT_NAME")
	tDir = os.environ.get("TR_TORRENT_DIR")
	tHash = os.environ.get("TR_TORRENT_HASH")
	tID = os.environ.get("TR_TORRENT_ID ")

	sql = "SELECT * FROM torrents WHERE LOWER(hash) = LOWER('" + tHash + "')"
	numRows = cursor.execute(sql)
	if (numRows>0):
		result = cursor.fetchall()[0]
		sql = "INSERT INTO pending (TR_TORRENT_DIR, TR_TORRENT_NAME, TR_TORRENT_HASH, episode, season, showid) VALUES ('" + tDir + "', '" + tName + "', '" + tHash  + "', '" + str(result['episode']) + "', '" + str(result['season']) + "', '" + str(result['showid']) + "')"
		cursor.execute(sql)
		sql = "UPDATE torrents SET hash = '' WHERE hash = '" + tHash + "'"
		cursor.execute(sql)
	else:
		syslog.syslog("Can't find the torrent hash " + tHash)

sql = 'SELECT value1 FROM configuration WHERE configuration.key = "serieslocation"'
result = cursor.execute(sql)
if (result == 0):
	if (config['debug']):
		syslog.syslog(syslog.LOG_ERR, "Shows location NOT CONFIGURED. CANNOT CONTINUE. (Configure it through the web interface)\n\n")
	quit()
config['showspath'] = cursor.fetchone()['value1']

sql = 'SELECT value1 FROM configuration WHERE configuration.key = "thetvdblanguage"'
result = cursor.execute(sql)
if (result == 0):
	config['thetvdblanguage'] = 'en'
else:
	config['thetvdblanguage'] = cursor.fetchone()['value1']

sql = "SELECT * FROM pending"
numPendings = cursor.execute(sql)
for i in range(0, numPendings):
	singleRow = cursor.fetchone()
	processTorrent (singleRow['TR_TORRENT_NAME'], singleRow['TR_TORRENT_DIR'], singleRow['TR_TORRENT_HASH'])

db.close()