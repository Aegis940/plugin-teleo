# Auteur : [Flobul](https://github.com/Flobul/conso_veolia) and some modif form [JohanSweck] (https://github.com/JohanSweck/conso_veolia)
#
# Modif : [Aegis](https://github.com/Aegis940/plugin-teleo) pour intégration au plugin teleo

from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait # available since 2.4.0
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException

from pyvirtualdisplay import Display

import os
import sys
import time
import logging
from logging.handlers import RotatingFileHandler


# Configuration des logs
tempDir = '/tmp'
logFile = tempDir + '/veolia.log'
geckodriverLog = tempDir + '/geckodriver.log'

logger = logging.getLogger()
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
downloadPath = os.path.normpath(sys.argv[3])
downloadFile = downloadPath + '/historique_jours_litres.csv'

options = webdriver.FirefoxOptions()
options.add_argument('-headless')
options.add_argument("--no-sandbox")

#Démarre l'affichage virtuel
display = Display(visible=0, size=(800, 600))
display.start()

def waitData(exitCond, sleepTime, loopNb):

	kpi_field = browser.find_elements_by_class_name("kpi-value")

	nb_kpi = len(kpi_field)
	if nb_kpi != 3 : raise Exception('wrong KPI number')
	
	loop = 1
	while True:
		time.sleep(sleepTime)
		
		if (kpi_field[2].text.find(exitCond) != -1): break

		if (loop > loopNb): raise Exception('display data too long')
		loop = loop + 1
	
	logger.info(kpi_field[2].text + ' waitTime = ' + str(loop*sleepTime) + ' sec')	
	
try:
	returnStatus = 0

	profile = webdriver.FirefoxProfile()
	options = webdriver.FirefoxOptions()
	options.headless = True
	profile.set_preference('browser.download.folderList', 2)
	profile.set_preference('browser.download.manager.showWhenStarting', False)
	profile.set_preference('browser.download.dir', downloadPath)
	profile.set_preference('browser.helperApps.neverAsk.saveToDisk', 'text/csv')

	# Bien indiquer l'emplacement de geckodriver
	logger.info('Initialisation browser')
	browser = webdriver.Firefox(firefox_profile=profile, options=options, executable_path=r'/usr/local/bin/geckodriver', service_log_path=geckodriverLog)

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
	WebDriverWait(browser, 20).until(EC.presence_of_element_located((By.NAME , 'from')))
	
	# On attend que les premières données soient chargées
	waitData("mois",5,4)
	
	# Sélection boutons
	logger.info('Sélection boutons')
	
	dayButton = browser.find_element_by_xpath("//span[contains(.,'Jours')]//parent::button")
	dayButton.send_keys(Keys.RETURN)
	waitData("jour",3,5)
	
	literButton = browser.find_element_by_xpath("//span[contains(.,'Litres')]//parent::button")
	literButton.send_keys(Keys.RETURN)
	waitData("Litres",2,5)
	
	# Téléchargement du fichier
	logger.info('Téléchargement du fichier')
	downloadFileButton = browser.find_element_by_class_name("slds-button.slds-text-title_caps")
	downloadFileButton.click()

	logger.info('Fichier: ' + downloadFile)

	# Resultat
	returnStatus = 1

except (Exception, TimeoutException) as e: logger.error(str(e))
 
finally:
	# fermeture browser
	logger.info('Fermeture connexion')
	browser.quit()

	# Suppression fichier temporaire
	logger.info('Suppression fichier log temporaire')
	if os.path.exists("/tmp/geckodriver.log"):
		os.remove("/tmp/geckodriver.log")
			
	# fermeture de l'affichage virtuel
	logger.info('Fermeture display. Exit code ' + str(returnStatus))
	display.stop()
	
	# print (returnStatus)
	sys.exit(returnStatus)

