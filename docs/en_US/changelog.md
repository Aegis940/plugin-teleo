# Changelog 

>**Important**
>
>As a reminder if there is no information on the update, it means that it only concerns the updating of documentation, translation or text.

# 13/02/2021
- Add sudo on the command to delete the measurement file
- Single/double quote management in the password

# 07/02/2021
- Adding template colour customization as done in the Linky plugin by [Salvialf]
- Reorganization of equipment parameters for better readability
- Add new parameter named **Contract** for Veolia IDF users **only**, allowing to indicate on which contract the data recovery is done (to be used only if several contracts are linked to the same account). However, the plugin does not currently allow you to manage several equipment for the same Jeedom installation.

# 01/02/2021
- Correction of 'lxml' module dependencies (ibxml2-dev libxslt-dev). Caution: the installation is quite long
- Launching the shell script with sudo command to avoid an issue with geckodriver. **Remember to save the equipment again to update the export directory rights.**

# 21/01/2021
- Remove last history for index and consod in case of modification of the last value (in forced mode)

# 17/01/2021
- Use of dynamic units on commands for Jeedom 4.1 users (will only apply on creation of new equipment)
- Fixed end-of-day error message if *Force data recovery* option is enabled all the time
- Correction pb de calcul de la date de l'index s'il y a des indexes précédents réintégrés
- Fix problem of index date calculation if there are previous indexes reintegrated

# 16/01/2021

- Update of the geckodriver version (upgrade to version 0.26) for the aarch64 and armv71 systems (restart the installation of the dependencies)
- Correction related to password containing special characters
- Management of non-consecutive records (mainly for Veolia Eau customers)
- Management of the reintegration of missed indexes (over 14 days max)
- Added option to ignore estimated records
- Added debug screenshots for the IDF script
- Added recovery of 14 days of indexes for the Other script for the management of missing indexes
- Adding a teleo_python log accessible from Jeedom

# 03/01/2021
- First public version (beta).
