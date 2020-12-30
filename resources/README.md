# script get_veolia_idf_consommation.py

Dans le cas où le script *get_veolia_idf_consommation.py* ne peut être installé sur la machine hébergeant Jeedom et donc que le script soit exécuté sur une autre machine

## Installation prérequis

***la version de Python 3.7.x est indispensable*** (*python3 --version*) et Firefox 60 ou supérieur (donc mieux vaut une **distrib buster**)
```bash
sudo apt-get update
sudo apt-get sshpass
sudo apt-get install python3 python3-pip xvfb iceweasel
sudo pip3 install selenium pyvirtualdisplay urllib3
```

** Pour la version du driver geckodriver, elle dépend de votre architecture
si la commande *uname -m* retourne **armv7l** (cas d'un Raspberry PI) alors :
```bash
sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.23.0/geckodriver-v0.23.0-arm7hf.tar.gz && sudo tar xzfz geckodriver-v0.23.0-arm7hf.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.23.0-arm7hf.tar.gz
```
sinon
```bash
32bit : sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.28.0/geckodriver-v0.28.0-linux32.tar.gz && sudo tar xzfz geckodriver-v0.28.0-linux32.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.28.0-linux32.tar.gz
64bit : sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.28.0/geckodriver-v0.28.0-linux64.tar.gz && sudo tar xzfz geckodriver-v0.28.0-linux64.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.28.0-linux64.tar.gz
```

## Création fichier bash
> Créer un fichier sh : **conso_veolia.sh** dans le répertoire *conso_veolia* de l'utilsateur *pi* (par exemple) avec dedans :

```bash
#!/bin/bash

/home/pi/conso_veolia/get_veolia_data.sh IDF <username> <password> /home/pi/conso_veolia

if [ $? -eq 1 ]; then
    sshpass -p "<Mdp_ssh_jeedom>" scp /home/pi/conso_veolia/historique_jours_litres.csv <user_jeedom>@<adresse_ip_local_jeedom>:/tmp/teleo
    mv /home/pi/conso_veolia/historique_jours_litres.csv /home/pi/conso_veolia/historique_jours_litres.old
fi
```

>**Attention** l'utilisateur ssh doit avoir les droits d'écriture sur répertoire destination */tmp/teleo* 

> Modifier le fichier sh **get_veolia_data.sh** :
```bash
...
# Setup
root='home/pi/conso_veolia'
...f
```

## Création commande cron
> Editer le fichier crontab de l'utilisateur *pi* :
```bash
crontab -e
```
Et ajouter par exemple, la ligne suivante pour un lancement à 00h05 :
```bash
5 0 * * * /home/pi/conso_veolia/conso_veolia.sh >/dev/null 2>&1
```
