# Changelog

>**IMPORTANT**
>
>Pour rappel s'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 07/01/2024
- Utililisation de Selenium 3 sous Debian 10 dû à la necessité pour Selenium 4 d'avoir python 3.8+. Attention ceci peut rentre incompatible d'autres plugin utilisant Selenium

# 01/11/2023
- Ajout d'une commande "rafraîchir" et de l'icone d'appel dans le widget (sauver l'équipement après la mise à jour pour ajout la commande)

# 31/10/2023
- Pb de compatibilité entre Selenium et Urllib3

# 22/10/2023
- Ajout d'une vérification de la version de Selenium 4 lors de l'installation des dépendances. Si la version est inférieur à 4.11 alors installation de la version 3.141
- Mise à jour vers la version 0.33.0 du geckodriver

# 08/10/2023
- Correction test version python
- Correction suite au renommage champ login dans la page du site veolia
- Tentative de mise en compatibilité Selenium 4 (Beta)

# 05/05/2023
- Suppression de la vérification de la version de python à l'installation des dépendances
- Suppression de l'installation de iceweasel

# 31/10/2022
- Ajout de la version 0.31.0 du geckodriver pour les architectures armv7l pour la compatibilité avec Firefox 91

# 29/10/2022
- Suppression des fonctions dépréciées dans le script Python
- Ajout du fichier packages.json pour le suivi des version des dépendances dans Jeedom
- Suppression de l'installation des dépendances utilisé par le script get_veolia_other_consommation.py car non supporté par le plugin. Vous pouvez vous tourner vers le plugin Veolia Pro de [thanaus]

# 20/10/2022
- Support core 4.3
- Passage à la version 0.32.0 du geckodriver. Nécessite iceweasel et firefox-esr en version >= 102 (Si le plugin ne fonctionne pas à cause du driver relancer l'installation des dépendances, sinon ne le faite pas)
- Passage à un enregistrement des commandes à 00:00 au lieu de 23:55. Les anciens historiques sont remis à jour en sauvegardant à nouveau l'équipement (des anomalies d'affichage peuvent apparaitre le jour de la transition dans l'historique et ils disparaitront normalement le lendemain)
- Affichage de la date des périodes dans le Widget au lieu des labels génériques

# 10/02/2022
- Suppression du support des sites web Veolia autres que Veolia IDF suite à l'ajout d'un Captcha

# 13/02/2021
- Ajout sudo sur la commande de suppression du fichier de mesure
- Gestion simple/double quote dans le mot de passe

# 07/02/2021
- Ajout de la personnalisation de la couleur de template comme dans le plugin Linky de [Salvialf]
- Réorganisation des paramètres de l'équipement pour plus de lisibilité
- Ajout d'un paramètre **Contrat** pour les utilisateur de Veolia IDF **uniquement**, permettant d'indiquer sur quel contrat se fait la récupération des données (à n'utiliser que si plusieurs contrats sont liés à un même compte). Cependant le plugin ne permet pas actuellement de gérer plusieurs compteurs pour une même installation Jeedom.

# 01/02/2021
- Correction des dépendances module 'lxml' (ibxml2-dev libxslt-dev). **Attention l'installation est assez longue** 
- Lancement du script shell en sudo afin d'éviter un problème avec geckodriver. **Pensez à sauvegarder à nouveau l'équipement pour mettre à jour les droits du répertoire d'export.**

# 21/01/2021
- Suppression de la dernière valeur d'historique pour index et consod (dans le cs du mode forcé)

# 17/01/2021
- Utilisation des unités dynamiques sur les commandes pour les utilisateurs Jeedom 4.1 (ne s'appliquera qu'à la création d'un nouvel équipement)
- Correction du message d'erreur en fin de journée si l'option *Forcer la récupération des données* est activée tout le temps
- Correction d'un problème de calcul de la date de l'index s'il y a des indexes précédents réintégrés

# 16/01/2021
- Mise à jour de la version du geckodriver (passage à la version 0.26) pour les systèmes aarch64 et armv71 (relancer l'installation des dépendances)
- Correction lié au mot de passe contenant des caractères spéciaux
- Gestion des relevés non consécutifs (principalement pour les clients Veolia Eau)
- Gestion de la réintégration des indexes manqués (sur 14 jours max)
- Ajout d'une option pour ignorer les relevés estimés
- Ajout de screenshots de debug pour le script IDF
- Ajout de la récupération de 14 jours d'index pour le script Other pour la gestion des indexes manquants
- Ajout d'un log teleo_python accessible depuis Jeedom

# 03/01/2021
- Première version publique (beta)

