# script get_veolia_idf_consommation.py

Dans le cas où le script *get_veolia_idf_consommation.py* ne peut être installé sur la machine hébergeant Jeedom et dionc que le script soit exécuté sur un autre machine

## Installation prérequis

*** la version de Python 3.7.x est indispensable *** (*python3 --version*)
code`sudo apt-get update
code`sudo apt-get sshpass
code`sudo apt-get install python3 xvfb iceweasel
code`sudo pip3 install selenium pyvirtualdisplay urllib3

** Pour la version du driver geckodriver, elle dépend de votre architecture
si la commande *uname -m* retourne **armv7l** (cas d'un Raspberry PI) alors :
code`sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.23.0/geckodriver-v0.23.0-arm7hf.tar.gz && sudo tar xzfz geckodriver-v0.23.0-arm7hf.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.23.0-arm7hf.tar.gz
sinon
code`sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.26.0/geckodriver-v0.26.0-linux32.tar.gz && sudo tar xzfz geckodriver-v0.26.0-linux32.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.26.0-linux32.tar.gz

## Création fichier bash
> Créer un fichier sh : **get_veolia_data.sh** dans le répertoire *conso_veolia* de l'utilsateur *pi* avec dedans :

code`python3 /home/pi/conso_veolia/get_veolia_idf_consommation.py
code`sshpass -p "<Mdp_ssh_jeedom>" scp /home/pi/conso_veolia/historique_jours_litres.csv <user_jeedom>@<adresse_ip_local_jeedom>:/var/www/html/tmp/teleo
code`rm /home/pi/conso_veolia/historique_jours_litres.csv

**Attention** l'utilisateur ssh doit avoir les droits d'écriture sur répertoire destination */var/www/html/tmp/teleo* 

## Création commande cron
> Editer le fichier crontab de l'utilisateur *pi*:
code`crontab -e

Et ajouter par exemple, la ligne suivante pour un lancement à 00h01 :
code`1 0 * * * /home/pi/conso_veolia/get_veolia_data.sh

