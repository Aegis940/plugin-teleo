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
	
# URL des pages nécessaires
url = "https://www.service.eau.veolia.fr"
url_page_login = url + "/connexion-espace-client.html"
url_action_login = url + "/home/connexion-espace-client/bloc-connexion-area/connexion-espace-client.loginAction.do"
url_page_histo = url + "/home/espace-client/votre-consommation.html?vueConso=historique"
url_fichier_histo = url + "/home/espace-client/votre-consommation.exportConsommationData.do?vueConso=historique"

logger = logging.getLogger()

def initLogger(logFile):
	logger.setLevel(logging.INFO)
	formatter = logging.Formatter('%(asctime)s :: %(levelname)s :: %(message)s')
	file_handler = RotatingFileHandler(logFile, 'a', 1000000, 1)
	file_handler.setLevel(logging.INFO)
	file_handler.setFormatter(formatter)
	logger.addHandler(file_handler)
	steam_handler = logging.StreamHandler()
	steam_handler.setLevel(logging.INFO)
	steam_handler.setFormatter(formatter)
	logger.addHandler(steam_handler)

try:
	returnStatus = 0

	# Configuration des logs
	tempDir = '/tmp/teleo'
	Path(tempDir).mkdir(mode=0o754,parents=True, exist_ok=True)

	tempFile = tempDir + '/historique.xls'
	logFile = tempDir + '/veolia.log'
	
	initLogger(logFile)

	if len( sys.argv ) < 4:
		logger.error('wrong number of arg')
		sys.exit(returnStatus)

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
	wb = xlrd.open_workbook(tempFile)
	sheet = wb.sheet_by_index(0)

	# Donnees du dernier releve
	date = datetime.datetime.strptime('1900-01-01', '%Y-%m-%d') + datetime.timedelta(sheet.cell_value(sheet.nrows - 1, 0) - 2)
	index = sheet.cell_value(sheet.nrows - 1, 1)
	conso = sheet.cell_value(sheet.nrows - 1, 2)
	releve = sheet.cell_value(sheet.nrows - 1, 3)

	# Resultat
	downloadPath = os.path.normpath(sys.argv[3])
	downloadFile = downloadPath + '/historique_jours_litres.csv'
		
	open(downloadFile, 'w').write(date.strftime("%Y-%m-%d") + ';' + str(index) + ';' + str(conso) + ';' + str(releve))

	returnStatus = 1

except Exception as e: logger.error(str(e))
	
finally:
	# Suppression fichier temporaire
	logger.info('Suppression fichier temporaire')
	if os.path.exists(tempFile):
		os.remove(tempFile)
  
	# Fermeture connexion
	logger.info('Fermeture connexion. Exit code ' + str(returnStatus))
	session.close()
	
	#print(returnStatus)
	sys.exit(returnStatus)

