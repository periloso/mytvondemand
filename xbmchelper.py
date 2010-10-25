#!/usr/bin/python

class XBMCHelper(object):
	def __init__(self):
		self.myNFO = {}
		self.tvdbimgurl = 'http://www.thetvdb.com/api/'
		
	def addtodict(self, key, dict, value):
		try:
			self.myNFO[key] = dict[value]
		except:
			pass
	
	def createXML(self):
		ep = sh = 0
		fanartXML = posterXML = seasonXML = seriesXML = writerXML = gueststarXML = ''
		retXML = '<?xml version="1.0" encoding="utf-8"?>\n'
		try:
			if (self.myNFO['episode'] != None):
				ep = 1
				retXML += '<episodedetails xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">\n'
		except KeyError:
			pass

		try:
			if (self.myNFO['status'] != None):
				sh = 1
				retXML += '<tvshow xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">\n'
		except KeyError:
			pass

		for item in self.myNFO.iterkeys():
			key, value = item, self.myNFO[item]
			if (key == 'Actors'):
				for actor in value:
					try:
						retXML += '  <actor>\n    <name>%s</name>\n    <role>%s</role>\n    <thumb>%s</thumb>\n  </actor>\n' % (actor['Name'], actor['Role'], actor['Image'])
					except:
						pass
			elif (key == 'Writer'):
				writerXML += value.strip('|').replace('|',' (Writer) / ') + ' (Writer) / '
			elif (key == 'GuestStars'):
				gueststarXML += ' (Guest Star) / '.join(value) + ' (Guest Star) / '
			elif (key == 'episodeguide'):
				retXML += '    <episodeguide>\n        <url cache="%s.xml">http://www.thetvdb.com/api/E1934C6FD54ECA7B/series/%s/all/en.zip</url>\n    </episodeguide>' % (value, value)
			elif (key == 'Posters'):
				for singleposter in value:
					if (singleposter['BannerType'] == 'fanart'):
						fanartXML += '        <thumb dim="%s" preview="%s">%s</thumb>\n' % (singleposter['BannerType2'], singleposter['ThumbnailPath'], singleposter['BannerPath'])
					elif (singleposter['BannerType'] == 'poster'):
						posterXML += '    <thumb type="poster">%s</thumb>\n' % (singleposter['BannerPath'])
					elif (singleposter['BannerType'] == 'season'):
						seasonXML = '    <thumb type="season" season="%s">%s</thumb>\n' % (singleposter['Season'], singleposter['BannerPath'])
					elif (singleposter['BannerType'] == 'series'):
						seriesXML = '    <thumb>%s</thumb>\n' % singleposter['BannerPath']
			else:
				retXML += '  <%s>%s</%s>\n' % (key, value, key)
		if (ep == 1):
			retXML += '  <credits>%s</credits>\n' % (gueststarXML + writerXML).strip(' /')
			retXML += '</episodedetails>'
		elif (sh == 1):
			retXML += '    <fanart>\n%s    </fanart>\n%s%s%s</tvshow>' % (fanartXML, posterXML, seasonXML, seriesXML)
		return retXML

	def createNFO(self, useDict, showDict = {}):
		self.myNFO = {}
		ep = sh = 0
		try:
			if (useDict['EpisodeNumber'] != None):
				ep = 1 # Episode!
		except:
			sh = 1 # Show!
		if (ep == 1):
			self.addtodict('title', useDict, 'EpisodeName')
			self.addtodict('Actors', showDict, 'Actors')
			self.addtodict('season', useDict, 'SeasonNumber')
			self.addtodict('episode', useDict, 'EpisodeNumber')
			self.addtodict('director', useDict, 'Director')
			self.addtodict('Writer', useDict, 'Writer')
			self.addtodict('GuestStars', useDict, 'GuestStars')
			self.addtodict('aired', useDict, 'FirstAired')
		elif (sh == 1):
			self.addtodict('title', useDict, 'SeriesName')
			self.addtodict('runtime', useDict, 'Runtime')
			self.addtodict('episodeguide', useDict, 'id') # Pay attention while creating the NFO!
			self.addtodict('mpaa', useDict, 'ContentRating')
			self.addtodict('premiered', useDict, 'FirstAired')
			self.addtodict('status', useDict, 'Status')
			self.addtodict('studio', useDict, 'Network')
			self.addtodict('Actors', useDict, 'Actors')
			self.addtodict('Posters', useDict, 'Banners')
			try:
				self.myNFO['genre'] = ' / '.join(useDict['Genre'])
			except KeyError:
				pass
		self.addtodict('rating', useDict, 'Rating')
		self.addtodict('votes', useDict, 'RatingCount')
		self.addtodict('plot', useDict, 'Overview')
		returnXML = self.createXML()
		return returnXML
