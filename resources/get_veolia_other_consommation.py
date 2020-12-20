# Auteur : doyenc  [https://community.jeedom.com/t/plugin-veolia-eau-plugin-veolia-eau-narrive-pas-a-se-connecter/17839/38]
#
# Modif : Aegis pour intégration au plugin teleo
#		: Ajout support arguments get_veolia_other_consommation.py <username> <password> <outputdir>

# -*- coding: latin-1 -*-

import requests
from lxml import html
import xlrd
import datetime
import json
import sys

# Identifiants
if len( sys.argv ) == 4:
	veolia_username = sys.argv[1]
	veolia_password = sys.argv[2]
else:
	veolia_username = "xxxxx"
	veolia_password = "xxxxx"
	
# Page de login
url = "https://www.service.eau.veolia.fr"
url_page_login = url + "/connexion-espace-client.html"
url_action_login = url + "/home/connexion-espace-client/bloc-connexion-area/connexion-espace-client.loginAction.do"
url_page_histo = url + "/home/espace-client/votre-consommation.html?vueConso=historique"
url_fichier_histo = url + "/home/espace-client/votre-consommation.exportConsommationData.do?vueConso=historique"

# Nouvelle session
session = requests.Session()

# Récuperation du token du form de login (hidden indispensable pour la connexion)
home = session.get(url_page_login)
tree = html.fromstring(home.content)
token = (tree.xpath('//input[@name="token"]')[0]).get('value')

# Connexion
data = {
    'token': token, 
    'veolia_username': veolia_username, 
    'veolia_password': veolia_password
}
page = session.post(url_action_login, data=data)

# Page historique (il faut passer par la page obligatoirement)
page = session.get(url_page_histo)

# Recuperation du xls
xls = session.get(url_fichier_histo)

# Sauvegarde du fichier temportaire
open('temp.xls', 'wb').write(xls.content)

# Ouverture du fichier temporaire
wb = xlrd.open_workbook("temp.xls")
sheet = wb.sheet_by_index(0)

# Donnees du dernier releve
date = datetime.datetime.strptime('1900-01-01', '%Y-%m-%d') + datetime.timedelta(sheet.cell_value(sheet.nrows - 1, 0) - 2)
dateF = date.strftime("%d/%m/%Y")
index = sheet.cell_value(sheet.nrows - 1, 1)
conso = sheet.cell_value(sheet.nrows - 1, 2)
releve = sheet.cell_value(sheet.nrows - 1, 3)

# Resultat si appel par plugin teleo
if len( sys.argv ) == 4:
	downloadPath = sys.argv[3]
	downloadFile = downloadPath + '/historique_jours_litres.csv'
	
	open(downloadFile, 'w').write(date.strftime("%Y-%m-%d") + ';' + str(index) + ';' + str(conso) + '\n')
	print(1)
	exit()
	
# Sinon retour Jeedom
if sys.argv[1] == 'index':
	print(index)
elif sys.argv[1] == 'conso':
	print(conso)
elif sys.argv[1] == 'date':
	print(dateF)
else:
	print(-1)

