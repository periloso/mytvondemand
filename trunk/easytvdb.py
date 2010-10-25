#!/usr/bin/python
from xml.etree.ElementTree import ElementTree
from xml.etree.ElementTree import XMLParser
import urllib, urllib2, zipfile, os, socket, tempfile

class EasyTVDB(object):
	def __init__(self, api_key):
		self.series = {}
		self.api_key = api_key
		self.url = 'http://www.thetvdb.com/api/' + self.api_key
		self.searchurl = 'http://www.thetvdb.com/api/'
		self.imageurl = 'http://www.thetvdb.com/banners/'
		socket.setdefaulttimeout(10)
	
	def getCache(self):
		return self.series
		
	def stripper(self, oldString, filename=0):	# If you wanna write a file, set filename = 1
												# If you wanna search for a show, leave the default value
		if (filename):
			return oldString.strip().replace(':',' -').replace('\\','').replace('?','').replace('/','').replace('|','').replace('<','').replace('>','').replace('"','')
		else:
			return oldString.strip().replace(':',' -').replace('\\','').replace('?','').replace('/','').replace('.','').replace('|','').replace('<','').replace('>','').replace('"','')

	def evaluateStrings(self, old, new):
		oldSplit = self.stripper(old).lower().split(' ')
		newSplit = self.stripper(new).lower().split(' ')
		occurrences = 0.0
		for oldString in oldSplit:
			for newString in newSplit:
				if (oldString == newString):
					occurrences = occurrences+1
		return ((occurrences/len(newSplit)) * 100)

	def actorsToDict(self, file, retDict = {}):
		retDict['Actors'] = []
		tree = ElementTree()
		catalog = tree.parse(file)
		for data in catalog:
			tempDict = {}
			for element in data:
				if (element.text != None):
					if (element.tag == 'Image'):
						tempDict[element.tag] = self.imageurl + element.text.encode('utf-8').strip()
					else:
						tempDict[element.tag] = element.text.encode('utf-8').strip()
			retDict['Actors'].append(tempDict)
		return retDict

	def bannersToDict(self, file, retDict = {}):
		tree = ElementTree()
		catalog = tree.parse(file)
		for data in catalog:
			tempDict = {}
			for element in data:
				try:
					if (retDict['Banners'] == None):
						pass
				except KeyError:
					retDict['Banners'] = []
				if (element.text != None):
					if (element.tag == 'Colors'):
						tempDict[element.tag] = element.text.encode('utf-8').strip(' |').split('|')
					elif ((element.tag == 'BannerPath') or (element.tag == 'ThumbnailPath') or (element.tag == 'VignettePath')):
						tempDict[element.tag] = self.imageurl + element.text.encode('utf-8').strip()
					else:
						tempDict[element.tag] = element.text.encode('utf-8').strip()
			retDict['Banners'].append(tempDict)
		return retDict

	def tvshowToDict(self, showid, language='en', update=False):
		showid = str(showid)
		try:
			if ((update!=True) and (self.series[showid] != None) and (self.series[showid]['TvDBLanguage'] == language)):
				return self.series
			else:
				self.series[showid] = {}
		except KeyError:
			self.series[showid] = {}
			pass

		(tempfd, temppath) = tempfile.mkstemp()
		open(temppath,'wb').write(urllib2.urlopen(self.url + '/series/' + showid + '/all/' + language + '.zip').read())

		zfobj = zipfile.ZipFile(temppath)
		tmpfileList = []
		for i in range(0,3):
			tmpfileList.append(tempfile.mkstemp())
		i = 0
		for fileName in zfobj.namelist():
			open(tmpfileList[i][1],'wb').write(zfobj.read(fileName))
			i+=1
		os.remove(temppath)

		tree = ElementTree()
		catalog = tree.parse(tmpfileList[0][1])
		for data in catalog:
			if (data.tag == 'Series'):
				for element in data:
					if element.text != None:
						if (element.tag == 'Genre'):
							self.series[showid][element.tag] = element.text.encode('utf-8').strip(' |').split('|')
						elif ((element.tag == 'filename') or (element.tag == 'banner') or (element.tag == 'fanart') or (element.tag == 'poster')):
							self.series[showid][element.tag] = self.imageurl + element.text.encode('utf-8').strip()
						else:
							self.series[showid][element.tag] = element.text.encode('utf-8').strip()
			elif (data.tag == 'Episode'):
				seasonEpisode = '%02d' % int(data.findtext('SeasonNumber')) + 'x' + '%02d' % int(data.findtext('EpisodeNumber'))
				try:
					if (self.series[showid]['Episodes'] == None):
						pass
				except KeyError:
					self.series[showid]['Episodes'] = {}
				
				try:
					if (self.series[showid]['Episodes'][seasonEpisode] == None):
						pass
				except KeyError:
					self.series[showid]['Episodes'][seasonEpisode] = {}
				for element in data:
					if element.text != None:
						if (element.tag == 'GuestStars'):
							self.series[showid]['Episodes'][seasonEpisode][element.tag] = element.text.encode('utf-8').strip(' |').split('|')
						elif ((element.tag == 'filename') or (element.tag == 'banner') or (element.tag == 'fanart') or (element.tag == 'poster')):
							self.series[showid]['Episodes'][seasonEpisode][element.tag] = self.imageurl + element.text.encode('utf-8').strip()
						else:
							self.series[showid]['Episodes'][seasonEpisode][element.tag] = element.text.encode('utf-8').strip()
			elif (data.tag == 'Actor'):
				pass
			else:
				print 'UNHANDLED TAG: <' + data.tag + '>'
				quit()
		self.series[showid] = self.bannersToDict(tmpfileList[1][1], self.series[showid])
		self.series[showid] = self.actorsToDict(tmpfileList[2][1], self.series[showid])
		self.series[showid]['TvDBLanguage'] = language
		for tmp in tmpfileList:
			os.remove(tmp[1])
		return self.series

	def findShow(self, showname, language = 'en'):
		get_args = urllib.urlencode({'seriesname': showname, 'language': language}, doseq=True)
		geturl = '%sGetSeries.php?%s' % (self.searchurl, get_args)
		
		(f, temppath) = tempfile.mkstemp()
		open(temppath,'w').write(urllib2.urlopen(geturl).read())
		tree = ElementTree()
		catalog = tree.parse(temppath)
		resultList = []
		
		for data in catalog:
			tmpDict = {}
			for show in data:
				tmpDict[show.tag] = show.text.encode('utf-8').strip()
			resultList.append([tmpDict['seriesid'],self.evaluateStrings(showname, tmpDict['SeriesName']),tmpDict])
		os.remove(temppath)
		return (sorted(resultList, key=lambda x: x[1], reverse=True))