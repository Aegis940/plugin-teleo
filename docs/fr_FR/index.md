# Plugin Veolia Téléo
![plugin-teleo logo](https://aegis940.github.io/plugin-teleo/assets/images/logo.png)

Plugin permettant la récupération des consommations du compteur communicant *Téléo* par l'interrogation du compte-client *Veolia*. Les données n'étant pas mises à disposition en temps réel, le plugin récupère chaque jour les données de consommation d'eau de la veille. 

Les types de données de consommation suivants sont accessibles :
- L'**index** de consommation *(en L)*.
- la **consommation journalière** *(en L)*.
- la **consommation hebdomadaire** *(en L)*.
- la **consommation mensuelle** *(en L)*.
- la **consommation annuelle** *(en L)*.

>**Important**      
> Il est nécessaire d'être en possession d'un compteur connecté **Téléo** et d'un compte-client Veolia. Le plugin récupère les informations, en fonction de la configuration choisie dans le plugin, à partir de la partie *mon espace* <a href="https://www.vedif.eau.veolia.fr/" target="_blank">du site Veolia Ile de France</a> pour les clients d'Ile de France ou à partir de la partie *mon espace personnel* <a href="https://www.service.eau.veolia.fr/" target="_blank">du site Veolia</a> pour les autres, il faut donc vérifier que vous y avez bien accès avec vos identifiants habituels et que les données y sont visibles. 
> Dans le cas contraire, le plugin ne fonctionnera pas.

# Configuration

## Configuration du plugin

Le plugin **Veolia Téléo** récupère les informations en utilisant un script Python (un pour le site Veolia Ile de France et un pour les autres sites). Chaque script nécessite l'installation de composants spécifiques :

### Pour le script Veolia Ile de France
***la version de Python 3.7.x est indispensable*** (*python3 --version*)
```bash
sudo apt-get update
sudo apt-get install python3 xvfb iceweasel
sudo pip3 install selenium pyvirtualdisplay urllib3
```

**Pour la version du driver geckodriver, elle dépend de votre architecture**
si la commande *uname -m* retourne **armv7l** (cas d'un Raspberry PI) alors :
```bash
sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.23.0/geckodriver-v0.23.0-arm7hf.tar.gz && sudo tar xzfz geckodriver-v0.23.0-arm7hf.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.23.0-arm7hf.tar.gz
```
sinon :
```bash
sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.26.0/geckodriver-v0.26.0-linux32.tar.gz && sudo tar xzfz geckodriver-v0.26.0-linux32.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.26.0-linux32.tar.gz
```
### Pour les autres sites Veolia
**la version de Python 3.x est indispensable**
```bash
sudo pip install requests lxml xlrd
```

>Les données sont vérifiées toutes les heures entre 4h et 22h et mises à jour uniquement si non disponibles dans Jeedom.

## Configuration des équipements

Pour accéder aux différents équipements **Veolia Téléo**, dirigez-vous vers le menu **Plugins → Energie → Veolia Téléo**.

> **A savoir**    
> Le bouton **+ Ajouter** permet d'ajouter un nouveau compte **Veolia Téléo**.

Sur la page de l'équipement, renseignez :

- le **Site Veolia** auquel se connecter (*Veolia Ile de France* ou *Autre site Veolia*)
- l'**identifiant** ainsi que le **mot de passe** de votre compte-client *Veolia* 

Puis cliquez sur le bouton **Sauvegarder**.

Décocher l'option **Connexion au site Veolia** permet de ne pas lancer le script Python pour récupérer les données mais l'exploiter un fichier résultat déposé dans le **Répertoire d'export des données du site Veolia** (à utiliser dans le cas où les prérequis pour le script pyhton ne peuvent être satisfait sur la machine hébergeant Jeedom).
L'option **Forcer la récupération des données** permet de récupérer les informations de consommation même si elles ont déjà été récupérées pour la période concernée.
L'option **Template de widget** permet d'utiliser le widget au couleur du compteur Téléo.


Le plugin va alors vérifier la bonne connexion au site *Veolia* et récupérer et insérer en historique :
- **L'index de consommation** : en Litres, utilisé pour les calculs des consommations
- **consommation journalière** : en Litres, basé sur l'index,
- **consommation hebdomadaire** : en Litres, basé sur l'index,
- **consommation mensuelle** : en Litres, basé sur l'index,
- **consommation annuelle** : en Litres, basé sur l'index,

> **A savoir** 
> Le calcul de consommation se faisant sur l'index, il est nécessaire de conserver son historique au moins sur 1 à 2 ans. 
> **Le premier jour d'installation de l'équipement, les valeurs seront à 0** et c'est seulement au second jour que les données de consommation seront visibles.


# Remarques

Il peut arriver que cela ne fonctionne pas à chaque fois : pas de problème, il suffit d'attendre le prochain passage prévu.

Le plugin se repose sur la manière dont le site Veolia est structuré. Tout changement sur le site entrainera vraisemblablement une erreur sur le plugin et nécessitera une adaptation des scripts python plus ou moins difficile à faire.

# Contributions

Ce plugin gratuit est ouvert à contributions (améliorations et/ou corrections). N'hésitez pas à soumettre vos pull-requests sur <a href="https://github.com/Aegis940/plugin-teleo" target="_blank">Github</a>

# Credits

Ce plugin s'est inspiré des travaux suivants :

- [Jeedom](https://github.com/jeedom) via leur plugin Enedis :  [plugin-enedis](https://github.com/jeedom/plugin-enedis)
- [hugoKs3](https://github.com/hugoKs3/plugin-jazpar) pour son plugin jazpar
- [Flobul](https://github.com/Flobul/conso_veolia) pour son script Python pour récupérer les données du site Veolia IdF
- [doyenc](https://community.jeedom.com/t/plugin-veolia-eau-plugin-veolia-eau-narrive-pas-a-se-connecter/17839/38) pour son script Python pour récupérer les données du site Veolia
 

# Disclaimer
-   Ce plugin ne prétend pas être exempt de bugs.
-   Ce plugin vous est fourni sans aucune garantie. Bien que peu probable, si il venait à corrompre votre installation Jeedom,l'auteur ne pourrait en être tenu pour responsable.

# ChangeLog
Disponible [ici](./changelog.md).
