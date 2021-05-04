# Auteur : [doyenc](https://community.jeedom.com/t/plugin-veolia-eau-plugin-veolia-eau-narrive-pas-a-se-connecter/17839/38)
#
# Modif : [Aegis](https://github.com/Aegis940/plugin-teleo) pour intégration au plugin teleo

# -*- coding: latin-1 -*-

import requests
from lxml import html
import xlrd
import datetime
import json
import os
import sys
import logging
from logging.handlers import RotatingFileHandler
from pathlib import Path
import csv

# URL des pages nécessaires
url = "https://www.service.eau.veolia.fr"
url_page_login = url + "/connexion-espace-client.html"
url_action_login = url + "/home/connexion-espace-client/bloc-connexion-area/connexion-espace-client.loginAction.do"
url_page_histo = url + "/home/espace-client/votre-consommation.html?vueConso=historique"
url_fichier_histo = url + "/home/espace-client/votre-consommation.exportConsommationData.do?vueConso=historique"

logger = None
tempFile = None
session = None
logLevel = None

def setLogLevel(level):
	if (level == '100') : return logging.DEBUG
	if (level == '200') : return logging.INFO
	if (level == '300') : return logging.WARNING
	if (level == '400') : return logging.ERROR
	if (level == '1000') : return logging.CRITICAL
	
	return logging.INFO
	
def initLogger(logFile, logLevel):
	logger.setLevel(logLevel)
	formatter = logging.Formatter('[%(asctime)s][%(levelname)s] : [Script Python] %(message)s')
	
	file_handler = RotatingFileHandler(logFile, 'a', 1000000, 1)
	
	file_handler.setLevel(logLevel)
	file_handler.setFormatter(formatter)
	logger.addHandler(file_handler)
	
	if (sys.argv == 4):
		steam_handler = logging.StreamHandler()
		steam_handler.setLevel(logLevel)
		steam_handler.setFormatter(formatter)
		logger.addHandler(steam_handler)

def parse_file(datafile):
	f = open(datafile)
	csv_reader_object = csv.reader(f)
	
	nrows = 0
	for line in csv_reader_object:
		nrows = nrows + 1

	# Donnees du dernier releve (14 jours précédent afin de palier aux erreurs de remontée)
	startindex = nrows - 14
	if (startindex <= 0) : startindex = 1
	
	# Resultat
	downloadPath = os.path.normpath(sys.argv[3])
	downloadFile = downloadPath + '/historique_jours_litres.csv'
	open(downloadFile, 'w').write('Date de relevé;Index relevé (litres);Consommation du jour (litres);Index Mesuré/Estimé\n')
	
	index = 0
	f = open(datafile)
	csv_reader_object = csv.reader(f)
	
	for col in csv_reader_object:
		
		if (index >= startindex):
			
			cell_value = col[0].split(";")
			date = datetime.datetime.strptime(cell_value[0],'%d/%m/%Y')
			index_value = cell_value[1]
			conso = cell_value[2]
			releve = cell_value[3]

			open(downloadFile, 'a').write(str(date) +';' + str(index_value) + ';' + str(conso) + ';' + str(releve) + '\n')
	

		index = index + 1
		
try:
	returnStatus = 0

	if len( sys.argv ) < 4:
		sys.exit(returnStatus)
	
	#Configuration des logs
	tempDir = os.path.normpath(sys.argv[3])
	
	#logPath = '/var/www/html/log'
	#if (os.path.exists(logPath)) : logFile = logPath + '/teleo_python'
	#else : logFile = tempDir + '/teleo_python.log'	
	logFile = tempDir + '/teleo_python.log'
	
	tempFile = tempDir + '/historique.xls'
	
	Path(tempDir).mkdir(mode=0o777,parents=True, exist_ok=True)		
	
	logger = logging.getLogger()
		
	if len( sys.argv ) >= 5: logLevel = setLogLevel(sys.argv[4])
	else: logLevel = logging.INFO
	
	logLevel = logging.INFO
	initLogger(logFile, logLevel)

	# Identifiants
	veolia_username = sys.argv[1]
	veolia_password = sys.argv[2]
	
	# Nouvelle session
	session = requests.Session()
	
	# Récuperation du token du form de login (hidden indispensable pour la connexion)
	home = session.get(url_page_login)
	if (home.status_code != 200): raise Exception('wrong URL') 
	
	tree = html.fromstring(home.content)
	token = (tree.xpath('//input[@name="token"]')[0]).get('value')

	# Connexion
	logger.info('Page de login')
	data = {
		'token': token, 
		'veolia_username': veolia_username, 
		'veolia_password': veolia_password
	}
	page = session.post(url_action_login, data=data)
	if (page.status_code != 200): raise Exception('wrong login') 

	# Page historique (il faut passer par la page obligatoirement)
	logger.info('Page de consommation')
	page = session.get(url_page_histo)
	if (page.status_code != 200): raise Exception('not logged or wrong URL') 
	
	# Recuperation du xls
	xls = session.get(url_fichier_histo)
	
	# Sauvegarde du fichier temportaire
	logger.info('Téléchargement du fichier')
	open(tempFile, 'wb').write(xls.content)

	# Ouverture du fichier temporaire
	parse_file(tempFile)

	returnStatus = 1

except Exception as e: logger.error(str(e))
	
finally:
	# Suppression fichier temporaire
	logger.info('Suppression fichier temporaire')
	if (tempFile is not None and os.path.exists(tempFile)) : 
		if (logLevel != logging.DEBUG) : os.remove(tempFile)
		
	# Fermeture connexion
	logger.info('Fermeture connexion. Exit code ' + str(returnStatus))
	if (session is not None) : session.close()
	
	#print(returnStatus)
	sys.exit(returnStatus)

