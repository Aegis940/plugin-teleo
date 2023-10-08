# Changelog 

>**Important**
>
>As a reminder if there is no information on the update, it means that it only concerns the updating of documentation, translation or text.

# 08/10/2023
- Correction test python version
- Correction following renaming of login field on veolia site page
- Attempt to make Selenium 4 (Beta) compatible

# 05/05/2023
- Removed python version check at dependencies installation
- Removal of iceweasel installation

# 31/10/2022
- Add geckodriver version 0.31.0 for armv7l architectures for compatibility with Firefox 91

# 29/10/2022
- Removal of deprecated functions in the Python script
- Add packages.json file for dependencies version tracking in Jeedom
- Removal of the dependencies installation used by the script get_veolia_other_consumption.py because not supported by the plugin. You can use the Veolia Pro plugin from [thanaus]

# 20/10/2022
- Core 4.3 support
- Upgrade to version 0.32.0 of geckodriver. Requires iceweasel and firefox-esr version >= 102 (If the plugin does not work because of the driver restart the installation of the dependencies, otherwise do not do it)
- Switch to recording commands at 00:00 instead of 23:55. Old values are updated by re-saving the equipment (display anomalies may appear on the day of the transition in the history and they will normally disappear the next day)
- Display of the date of the periods in the widget instead of the generic labels

# 10/02/2022
- Removal of the report of Veolia websites other than Veolia IDF following the addition of a Captcha

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
