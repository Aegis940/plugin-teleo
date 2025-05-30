# Plugin Veolia Téléo
![plugin-teleo logo](https://aegis940.github.io/plugin-teleo/assets/images/logo.png)

Plugin permettant la récupération des consommations du compteur communicant *Téléo* par l'interrogation du compte-client *Veolia*. Les données n'étant pas mises à disposition en temps réel, le plugin récupère chaque jour les données de consommation d'eau de la veille. 

Les types de données de consommation suivants sont accessibles :
- L'**index** de consommation *(en L)*.
- la **consommation journalière** *(en L)*.
- la **consommation hebdomadaire** *(en L)*.
- la **consommation mensuelle** *(en L)*.
- la **consommation annuelle** *(en L)*.

> **Important**      
> Il est nécessaire d'être en possession d'un compteur connecté **Téléo** et d'un compte-client Veolia. Le plugin récupère les informations, en fonction de la configuration choisie dans le plugin, à partir de la partie *mon espace* <a href="https://connexion.leaudiledefrance.fr" target="_blank">du site Veolia Ile de France</a> pour les clients d'Ile de France, il faut donc vérifier que vous y avez bien accès avec vos identifiants habituels et que les données y sont visibles. **Dans le cas contraire, le plugin ne fonctionnera pas.**

# Installation des dépendances

> Le plugin **Veolia Téléo** récupère les informations en utilisant un script Python.      

La version de ***Python 3.7.x*** est indispensable et ***Firefox 115 ou supérieur*** (donc une **distrib buster** obligatoire). Cependant la version conseillée pour le plugin est ***Python 3.9.x*** minium et donc une migration vers une **distrib bullseye** est plus que recommendée

> Les dépendances sont installées automatiquement par Jeedom dans les 5 min. Elles seront également réinstallées lors d’une mise à jour du plugin si besoin.

# Configuration

## Configuration du plugin

> Les données sont vérifiées, par défaut, toutes les heures entre 4h et 22h et mises à jour uniquement si non disponibles dans Jeedom. L'heure de début peut être réglée entre 1h et 20h.

## Configuration des équipements

Pour accéder aux différents équipements **Veolia Téléo**, dirigez-vous vers le menu **Plugins → Energie → Veolia Téléo**.

> **A savoir**    
> Le bouton **+ Ajouter** permet d'ajouter un nouveau compte **Veolia Téléo**.

Sur la page de l'équipement, renseignez :

- le **Site Veolia** auquel se connecter (*Veolia Ile de France*)
- l'**identifiant** ainsi que le **mot de passe** de votre compte-client *Veolia* 
- Si plusieurs contrats sont rattachés au même compte **Veolia IDF**, indiquer le **numéro du contrat** où les données seront récupérées (laisser vide sinon)
Puis cliquez sur le bouton **Sauvegarder**.

- Décocher l'option **Connexion au site Veolia** permet de ne pas lancer le script Python pour récupérer les données mais d'exploiter un fichier résultat déposé dans le **Répertoire d'export des données du site Veolia** (à utiliser dans le cas où les prérequis pour le script python ne peuvent être satisfait sur la machine hébergeant Jeedom).
- L'option **Ignorer les relevés estimés** permet de ne pas importer une estimation d'index.
- L'option **Forcer la récupération des données** permet de récupérer les informations de consommation même si elles ont déjà été récupérées pour la période concernée.
- L'option **Template de widget** permet d'utiliser le widget au couleur du compteur Téléo.


Le plugin va alors vérifier la bonne connexion au site *Veolia* et récupérer et insérer en historique :
- **L'index de consommation** : en Litres, utilisé pour les calculs des consommations
- **consommation journalière** : en Litres, basé sur l'index,
- **consommation hebdomadaire** : en Litres, basé sur l'index,
- **consommation mensuelle** : en Litres, basé sur l'index,
- **consommation annuelle** : en Litres, basé sur l'index,

> **A savoir**    
> Le calcul de consommation se faisant sur l'index, il est nécessaire de conserver son historique au moins sur 2 ans (1 an et 2 jours en fait). 
>
> Pour les périodes *hebdomadaire*, *mensuelle* et *annuelle*, seul le dernier historique de la période en cours est conservé.

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

# Disclaimer
-   Ce plugin ne prétend pas être exempt de bugs.
-   Ce plugin vous est fourni sans aucune garantie. Bien que peu probable, si il venait à corrompre votre installation Jeedom,l'auteur ne pourrait en être tenu pour responsable.

# ChangeLog
Disponible [ici](./changelog.md).
