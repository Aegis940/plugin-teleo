#
#

from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait # available since 2.4.0
from selenium.webdriver.support import expected_conditions as EC

from pyvirtualdisplay import Display

import os
import sys
import time
import logging
from logging.handlers import RotatingFileHandler

	
# Configuration des logs
logger = logging.getLogger()
logger.setLevel(logging.INFO)
formatter = logging.Formatter('%(asctime)s :: %(levelname)s :: %(message)s')
file_handler = RotatingFileHandler('/tmp/jeedom/teleo/veolia.log', 'a', 1000000, 1)
file_handler.setLevel(logging.INFO)
file_handler.setFormatter(formatter)
logger.addHandler(file_handler)
steam_handler = logging.StreamHandler()
steam_handler.setLevel(logging.INFO)
steam_handler.setFormatter(formatter)
logger.addHandler(steam_handler)

#URL des pages nécessaires
urlHome = 'https://espace-client.vedif.eau.veolia.fr/s/login/'
urlConso = 'https://espace-client.vedif.eau.veolia.fr/s/historique'

if len( sys.argv ) < 4:
	logger.error('wrong number of arg')
	sys.exit(0)
	
#Informations de connexion
veolia_login = sys.argv[1]
veolia_password = sys.argv[2]

#Emplacement de sauvegarde du fichier à télécharger		
downloadPath = sys.argv[3]
downloadFile = downloadPath + '/historique_jours_litres.csv'

options = webdriver.FirefoxOptions()
options.add_argument('-headless')
options.add_argument("--no-sandbox")

#Démarre l'affichage virtuel
display = Display(visible=0, size=(800, 600))
display.start()

returnStatus = 0

try:
	profile = webdriver.FirefoxProfile()
	options = webdriver.FirefoxOptions()
	options.headless = True
	profile.set_preference('browser.download.folderList', 2)
	profile.set_preference('browser.download.manager.showWhenStarting', False)
	profile.set_preference('browser.download.dir', downloadPath)
	profile.set_preference('browser.helperApps.neverAsk.saveToDisk', 'text/csv')

	# Bien indiquer l'emplacement de geckodriver
	logger.info('Initialisation browser')
	browser = webdriver.Firefox(firefox_profile=profile, options=options, executable_path=r'/usr/local/bin/geckodriver', service_log_path='/tmp/jeedom/teleo/geckodriver.log')

	# Page de login
	logger.info('Page de login')
	browser.get(urlHome)
	WebDriverWait(browser, 20).until(EC.presence_of_element_located((By.CSS_SELECTOR , 'input[inputmode="email"]')))

	nb_form = len(browser.find_elements_by_css_selector('input[inputmode="email"]'))
	if nb_form != 2 : raise Exception('wrong login number') 
	
	# Recherche et remplis les champs d'identification
	idEmail = browser.find_element_by_id('input-3')
	idPassword = browser.find_element_by_css_selector('input[type="password"]')

	idEmail.clear()
	idEmail.send_keys(veolia_login)
	time.sleep(3)

	idPassword.clear()
	idPassword.send_keys(veolia_password)
	time.sleep(3)

	loginButton = browser.find_element_by_class_name('submit-button')
	loginButton.click()
	time.sleep(2)

	# Page de consommation
	logger.info('Page de consommation')
	browser.get(urlConso)
	WebDriverWait(browser, 30).until(EC.presence_of_element_located((By.NAME , 'from')))

	# Sélection boutons
	logger.info('Sélection boutons')

	dayButton = browser.find_element_by_xpath("//span[contains(.,'Jours')]//parent::button")
	dayButton.send_keys(Keys.RETURN)
	time.sleep(5)
	literButton = browser.find_element_by_xpath("//span[contains(.,'Litres')]//parent::button")
	literButton.send_keys(Keys.RETURN)
	time.sleep(5)		

	# Téléchargement du fichier
	logger.info('Téléchargement du fichier')
	downloadFileButton = browser.find_element_by_class_name("slds-button.slds-text-title_caps")
	downloadFileButton.click()

	logger.info('Fichier:' + downloadFile)

	# Resultat
	returnStatus = 1

except Exception as e: logger.error(str(e))
  
finally:
	# fermeture browser
	logger.info('Fermeture connexion')
	browser.close()

	# Suppression fichier temporaire
	logger.info('Suppression fichier log temporaire')
	if os.path.exists("/tmp/geckodriver.log"):
		os.remove("/tmp/geckodriver.log")
			
	# fermeture de l'affichage virtuel
	logger.info('Fermeture display. Exit code ' + str(returnStatus))
	display.stop()
	
	# print (returnStatus)
	sys.exit(returnStatus)

