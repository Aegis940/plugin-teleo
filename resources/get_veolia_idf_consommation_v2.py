from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from pyvirtualdisplay import Display

from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait # available since 2.4.0
from selenium.webdriver.support import expected_conditions as EC

import os
import sys
import time
import datetime


#URL des pages nécessaires
urlHome = 'https://espace-client.vedif.eau.veolia.fr/s/login/'
urlConso = 'https://espace-client.vedif.eau.veolia.fr/s/historique'

#Informations de connexion
veolia_login = sys.argv[1]
veolia_password = sys.argv[2]

#Emplacement de sauvegarde du fichier à télécharger
downloadPath = sys.argv[3]
downloadFile = downloadPath + 'historique_jours_litres.csv'

options = webdriver.FirefoxOptions()
options.add_argument('-headless')
options.add_argument("--no-sandbox")

#Démarre l'affichage virtuel
display = Display(visible=0, size=(800, 600))
display.start()

try:
	#Mise en place du navigateur
	profile = webdriver.FirefoxProfile()
	options = webdriver.FirefoxOptions()
	options.headless = True
	profile.set_preference('browser.download.folderList', 2)
	profile.set_preference('browser.download.manager.showWhenStarting', False)
	profile.set_preference('browser.download.dir', downloadPath)
	profile.set_preference('browser.helperApps.neverAsk.saveToDisk', 'text/csv')
	browser = webdriver.Firefox(firefox_profile=profile, options=options, executable_path=r'/usr/local/bin/geckodriver', service_log_path='./geckodriver.log')

	#Page de login
	browser.get(urlHome)
	WebDriverWait(browser, 20).until(EC.presence_of_element_located((By.CSS_SELECTOR , 'input[type="email"]')))
	nb_form = len(browser.find_elements_by_css_selector('input[inputmode="email"]'))
	if nb_form != 2 : raise Exception('wrong login number') 
	email_field = browser.find_elements_by_css_selector('input[inputmode="email"]')[1]
	email_field.clear()
	email_field.send_keys(veolia_login)
	time.sleep(2)
	password_field = browser.find_elements_by_css_selector('input[type="password"]')[1]
	password_field.clear()
	password_field.send_keys(veolia_password)
	time.sleep(2)
	password_field.send_keys(Keys.RETURN)
	time.sleep(10)

	#Page de consommation
	browser.get(urlConso)
	WebDriverWait(browser, 20).until(EC.presence_of_element_located((By.NAME , 'from')))
	measureButton = browser.find_element_by_xpath("//span[contains(.,'Relevés')]//parent::button")
	measureButton.send_keys(Keys.RETURN)
	time.sleep(30)
	dayButton = browser.find_element_by_xpath("//span[contains(.,'Jours')]//parent::button")
	dayButton.send_keys(Keys.RETURN)
	time.sleep(30)
	literButton = browser.find_element_by_xpath("//span[contains(.,'Litres')]//parent::button")
	literButton.send_keys(Keys.RETURN)
	time.sleep(30)	
	
	nb_kpi = len(browser.find_elements_by_class_name("kpi-value"))
	if nb_kpi != 3 : raise Exception('wrong KPI number')
	yesterday = datetime.date.today() - datetime.timedelta(days = 1)	
	from_field = browser.find_element_by_name("from")
	from_field.clear()
	from_field.send_keys(yesterday.strftime("%d/%m/%Y"))
	time.sleep(2)
	from_field.send_keys(Keys.RETURN)
	time.sleep(30)
	kpi_field = browser.find_elements_by_class_name("kpi-value")
	kpi_value = kpi_field[1].text.split(" - ")
	if kpi_value[0] != yesterday.strftime("%d/%m/%Y") : raise Exception('wrong date') 
	print(kpi_value[0].strftime("%Y-%m-%d") + ';' + kpi_value[1])

	browser.close()
	
except Exception as e: print('exception :', e)
  
finally:
	#fermeture de l'affichage virtuel
	display.stop()