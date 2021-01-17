# Changelog

>**IMPORTANT**
>
>Pour rappel s'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 17/01/2021
- Utilisation des unités dynamiques sur les commandes pour les utilisateur Jeedom 4.1 (ne s'appliquera qu'à la création d'un nouvel équipement)
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

