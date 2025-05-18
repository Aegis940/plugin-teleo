#!/usr/bin/env python3

# Auteur : [Flobul](https://github.com/Flobul/conso_veolia) and some modif from [JohanSweck] (https://github.com/JohanSweck/conso_veolia)
#
# Modif : [Aegis](https://github.com/Aegis940/plugin-teleo) pour intégration au plugin teleo
#
# Modif : [SHOULDER](https://community.jeedom.com/t/connexion-echouee-rien-sur-le-tableau-de-bord/139250/8) pour adaptation au nouveau site web


import os
import sys
import time
import logging

#=>import pdb    						# DEBOGUEUR PYTHON

import selenium						# Juste pour connaitre la version !!!

from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait		# available since 2.4.0
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.firefox_profile import FirefoxProfile
from selenium.common.exceptions import TimeoutException
from pyvirtualdisplay import Display
from logging.handlers import RotatingFileHandler
from pathlib import Path

# URL des pages nécessaires
#============== VEOLIA ============================
#   CONTRAT AVANT le 01 janvier 2025
#=>url = 'https://espace-client.vedif.eau.veolia.fr'
#   CONTRAT APRES le 01 janvier 2025
URL = 'https://connexion.leaudiledefrance.fr'
urlHome = URL + '/s/login/'
urlAccueil = URL + '/espace-particuliers/s/'
urlConso = URL + '/espace-particuliers/s/historique'
urlConsoMultiContrat = URL + '/espace-particuliers/s/contrats'

Firefox = None
display = None
geckodriverLog = None
logger = None
logLevel = None
debog = False

loginParam = 1
passwordParam = 2
outputParam = 3
logLevelParam = 4
contractParam = 5

def take_screenshot(name,tempDir):
	if (logLevel != logging.DEBUG) : return;
	logging.debug("Taking screenshot : %s"%name)
	screenshot = tempDir  + '/' + name

	Firefox.save_screenshot('%s.png'%screenshot)

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
	file_handler = RotatingFileHandler(logFile, 'a', 5000, 1)

	file_handler.setLevel(logLevel)
	file_handler.setFormatter(formatter)
	logger.addHandler(file_handler)

	if (sys.argv == 4):
	    steam_handler = logging.StreamHandler()
	    steam_handler.setLevel(logLevel)
	    steam_handler.setFormatter(formatter)
	    logger.addHandler(steam_handler)

# Fonction pour cliquer sur un bouton donné
def ButtonClick(element):
	Firefox.execute_script("arguments[0].click();", element)
		
def waitData(exitCond, sleepTime, loopNb):

	kpi_field = Firefox.find_elements(By.CLASS_NAME,"kpi-value")

	nb_kpi = len(kpi_field)
	if nb_kpi != 3 : raise Exception('wrong KPI number')

	loop = 1
	while True:
	    time.sleep(sleepTime)

	    if (kpi_field[2].text.find(exitCond) != -1): break

	    if (loop > loopNb): raise Exception('display data too long')
	    loop = loop + 1

	logger.debug(kpi_field[2].text + ' waitTime = ' + str(loop*sleepTime) + ' sec')	

def initWebBrowser_selenium3():
	options = webdriver.FirefoxOptions()
	options.add_argument('-headless')
	options.add_argument("--no-sandbox")
	options.headless = True

	profile = webdriver.FirefoxProfile()
	profile.set_preference('browser.download.folderList', 2)
	profile.set_preference('browser.download.manager.showWhenStarting', False)
	profile.set_preference('browser.download.dir', downloadPath)
	profile.set_preference('browser.helperApps.neverAsk.saveToDisk', 'text/csv')

	# Bien indiquer l'emplacement de geckodriver
	logger.info('Initialisation du navigateur Firefox s3')
	browser = webdriver.Firefox(firefox_profile=profile, options=options, executable_path=r'/usr/local/bin/geckodriver', service_log_path=geckodriverLog)
	return browser


def initWebBrowser_selenium4():
	options = webdriver.FirefoxOptions()				# Modif voir https://www.selenium.dev/documentation/webdriver/browsers/firefox/
	options.add_argument('-headless')
	options.add_argument('--no-sandbox')

	service = webdriver.FirefoxService(executable_path=r'/usr/local/bin/geckodriver', log_output=geckodriverLog)

	firefox_profile = FirefoxProfile()
	firefox_profile.set_preference('browser.download.folderList', 2)
	firefox_profile.set_preference('browser.download.manager.showWhenStarting', False)
	firefox_profile.set_preference('browser.download.dir', downloadPath)
	firefox_profile.set_preference('browser.helperApps.neverAsk.saveToDisk', 'text/csv')
	options.profile = firefox_profile

	logger.info('Initialisation du navigateur Firefox s4')
	browser = webdriver.Firefox(options=options, service=service)
	return browser


#	Début du programme principal
try:
	# if debog:
	    # import pdb    						# DEBOGUEUR PYTHON
	    # pdb.set_trace()

	returnStatus = 0

	if len( sys.argv ) < 4:
	    sys.exit(returnStatus)

	#Configuration des logs
	#tempDir = os.path.normpath(sys.argv[outputParam]) + '/Test_Log'
	tempDir = os.path.normpath(sys.argv[outputParam])
	traceDir = os.path.normpath(sys.argv[outputParam]) + '/trace'
    
	#logPath = '/var/www/html/log'
	#if (os.path.exists(logPath)) : logFile = logPath + '/teleo_python'
	#else : logFile = tempDir + '/teleo_python.log'	
	logFile = tempDir + '/teleo_python.log'

	geckodriverLog = traceDir + '/geckodriver.log'

	Path(tempDir).mkdir(mode=0o777,parents=True, exist_ok=True)
	Path(traceDir).mkdir(mode=0o777,parents=True, exist_ok=True)

	logger = logging.getLogger()

	if len( sys.argv ) > 4:
	    logLevel = setLogLevel(sys.argv[logLevelParam])
	else:
	    logLevel = logging.INFO

	initLogger(logFile, logLevel)

	#Informations de connexion
	veolia_login = sys.argv[loginParam]
	veolia_password = sys.argv[passwordParam]

	#Emplacement de sauvegarde du fichier à télécharger
	downloadPath = os.path.normpath(sys.argv[outputParam])
	downloadFile = downloadPath + '/historique_jours_litres.csv'

	#Démarre l'affichage virtuel
	display = Display(visible=0, size=(800, 600))
	display.start()

	#Init webdriver Firefox
	#print("Selenium Version: " +selenium.__version__)
	if (selenium.__version__[:1] == '3'):
	    Firefox = initWebBrowser_selenium3()
	else:
	    Firefox = initWebBrowser_selenium4()

	logger.info('==================> Page de login')
	Firefox.get(urlHome)

	# Rechercher et remplir les champs de connexion (Identificateur + mot de passe)
	# CORRECTION VOIR: https://stackoverflow.com/questions/74520997/selenium-python-chrome-driver-send-keys
	#	Champ de l'identificateur
	email1 = WebDriverWait(Firefox, 20).until(EC.presence_of_element_located((By.CSS_SELECTOR, 'input[inputmode="email"]')))
	idEmail = Firefox.find_element(By.CSS_SELECTOR, 'input[type="text"]')
	idEmail.clear()
	idEmail.send_keys(veolia_login)
	time.sleep(8)

	if debog:
	    take_screenshot("Login_form",traceDir)

	#	Champ du mot de passe
	idPassword = Firefox.find_element(By.CSS_SELECTOR, 'input[type="password"]')
	idPassword.clear()
	idPassword.send_keys(veolia_password)
	time.sleep(8)

	if debog:
	    take_screenshot("Password_form",traceDir)

	#	Champ du boutton de validation
	if debog:
	    loginButton = Firefox.find_element(By.CLASS_NAME,'submit-button')
	    take_screenshot("Boutton_validation",traceDir)
	WebDriverWait(Firefox, 10).until(EC.presence_of_element_located((By.CLASS_NAME, 'submit-button'))).click()
	WebDriverWait(Firefox, 15).until(EC.element_to_be_clickable((By.CLASS_NAME, 'submit-button'))).click()

	# Vérification du lien actuel si page d'Accueil
	Urlactuel = Firefox.current_url
	logger.info("==================> Page d\'Accueil: " + Urlactuel)
	if debog:
	    take_screenshot("URL_Accueil",traceDir)

	# Manage Multi-Contract
	if len( sys.argv ) == 6 :
	    contractID = sys.argv[contractParam]
        
        # Page de consommation
	    Firefox.get(urlConsoMultiContrat)
        
	    # Page des contrats
	    #WebDriverWait(Firefox, 15).until(EC.presence_of_element_located((By.LINK_TEXT, 'Voir mes contrats'))).click()
	    Urlactuel = Firefox.current_url
	    logger.info("==================> Page de(s) contrat(s): " + Urlactuel)
	    if debog:
	        take_screenshot("Liste_Contrats",traceDir)									# JUSTE POUR TEST

	    # Page du contrat selectionné
	    WebDriverWait(Firefox, 30).until(EC.presence_of_element_located((By.LINK_TEXT, contractID))).click()

	    if debog:
	        take_screenshot("Contrat_Choisi",traceDir)

	    # Page de consommation
	    Urlactuel = Firefox.current_url
	    logger.info("==================> Page de consommation: " + Urlactuel)

	    # Bouton de la page de "Consommation facturée"
	    liste_btn_1 = Firefox.find_elements(By.CLASS_NAME, 'fra-tab-active')

	    # Boutons des pages [Historique] et [Alertes de consommation]
	    liste_btn_2 = Firefox.find_elements(By.CLASS_NAME, 'fra-tab')

#	Correspond au bouton "Consommation facturée"
#=>	    WebDriverWait(Firefox, 30).until(EC.presence_of_element_located((By.CLASS_NAME, 'fra-tab-active'))).click()
#=>	    etat_CF = WebDriverWait(Firefox, 20).until(EC.element_to_be_clickable((By.CLASS_NAME, 'fra-tab-active'))).click()

#	Click sur l'icon "Historique"
#	'fra-tab'[1] => "Historique"
#	'fra-tab'[2] => "Alertes de consommation"

	    etat_Hist = WebDriverWait(Firefox, 20).until(EC.element_to_be_clickable((By.CLASS_NAME, 'fra-tab'))).click()

	    Urlactuel = Firefox.current_url
	    logger.info("==================> Click de 'Historique': " + Urlactuel)
	    if debog:
	        take_screenshot("Click_Historique",traceDir)

	else:
	# Cas d'un seul contrat

	    # Page de consommation
		Firefox.get(urlConso)
		
		#Urlactuel = Firefox.current_url

		logger.info("==================> Page de consommation: " + urlConso)
 		
		if debog:
			take_screenshot("Click_Historique",traceDir)


	# Partie de gestion des consommations
	WebDriverWait(Firefox, 20).until(EC.presence_of_element_located((By.NAME, 'from')))

	# On attend que les premières données soient chargées
	waitData("jour",5,4)

	# Sélection boutons
	logger.info('==================> Sélection des données en Jours et Litres')

#	RECHERCHE ET LOCALISER DES BOUTONS DANS LA PAGE "Historique"
	liste_btn = Firefox.find_elements(By.XPATH, '//button')
	for btn in liste_btn:
	    # if debog:
	        # print(f"\tListe des boutons: { btn }")
	        # print(f"\tListe des boutons (HTML): { btn.get_attribute('outerHTML') }")
	    if (btn.get_attribute("outerHTML").find("Litres") != -1):
	        id_Litres = liste_btn.index(btn)
	        # print(f"\tBouton 'Litres' est localisé.\n")
	    else:
	        if (btn.get_attribute("outerHTML").find("Télécharger la période") != -1):
	            id_download = liste_btn.index(btn)
	            # print(f"\tBouton 'Télécharger la période' est localisé.\n")

	dayButton = Firefox.find_element(By.XPATH,"//span[contains(.,'Jours')]//parent::button")
	dayButton.send_keys(Keys.RETURN)
	waitData("jour",3,5)

	literButton = Firefox.find_element(By.XPATH,"//span[contains(.,'Litres')]//parent::button")
	literButton.send_keys(Keys.RETURN)
	waitData("Litres",2,5)

#	Click sur l'icon "Litres" de "Mesure"
	ButtonClick( liste_btn[id_Litres] )

	Urlactuel = Firefox.current_url
	logger.info("==================> Selection de Litres: " + Urlactuel)
	if debog:
	    take_screenshot("Click_Litres",traceDir)

	# if debog:
	    # element = Firefox.find_elements(By.CLASS_NAME, "btn-green")							# JUSTE POUR TEST
	    # element = Firefox.find_elements(By.CLASS_NAME, "slds-text-title_caps")					# JUSTE POUR TEST
	    # element = Firefox.find_elements(By.CLASS_NAME, "slds-button")						# JUSTE POUR TEST
	    # element = Firefox.find_elements(By.CLASS_NAME, "slds-align_absolute-center")				# JUSTE POUR TEST
	    # element = Firefox.find_element(By.XPATH, "//*[contains(text(), 'Télécharger la période')]")			# JUSTE POUR TEST

	# Téléchargement du fichier
	logger.info('==================> Téléchargement du fichier')
	etat_tlchg = WebDriverWait(Firefox, 20).until(EC.element_to_be_clickable((By.CLASS_NAME, 'slds-button.slds-text-title_caps'))).click()
	# Autre méthode
#=>	ButtonClick( liste_btn[id_download] )

	logger.info('Fichier des données de consommation : ' + downloadFile)

	# Resultat
	returnStatus = 1


except Exception as e: 
	if (str(e.__class__).find('TimeoutException') != -1) : 
	    logger.error('La page met trop de temps à s\'afficher')
	else :
	    logger.error(str(e))

	if debog:
	    take_screenshot("Exception",traceDir) 

finally:
	# Fermeture du navigateur Firefox
	logger.debug('Fermeture de la connexion')
	if (Firefox is not None):
	    Firefox.quit()

	# Suppression fichier temporaire sauf en debug
	if (geckodriverLog is not None and os.path.exists(geckodriverLog)) : 
	    if (logLevel != logging.DEBUG):
	        os.remove(geckodriverLog)

	# Fermeture de l'affichage virtuel
	logger.info('Fermeture affichage virtuel (display). Exit code ' + str(returnStatus))
	if (display is not None):
	    display.stop()

	# print (f"\t==============> ETAT du RETOUR: {returnStatus}")
	sys.exit(returnStatus)