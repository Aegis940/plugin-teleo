# plugin-teleo
This is a plugin for Jeedom aimed at retrieveing water consumptions metrics from Veolia. 

This implies to have a communicating water meter provided by Veolia called **Téléo** and a proper local Jeedom installation.

# Credits
This plugin has been inspired by the work done by:
- [Jeedom](https://github.com/jeedom) through their Enedis plugin: [plugin-enedis](https://github.com/jeedom/plugin-enedis)
- [hugoKs3](https://github.com/hugoKs3/plugin-jazpar) through his Jazpar plugin
- [Flobul](https://github.com/Flobul/conso_veolia) for his Python script to retrieve data from Veolia IdF site
- [doyenc](https://community.jeedom.com/t/plugin-veolia-eau-plugin-veolia-eau-narrive-pas-a-se-connecter/17839/38) for his Python script to retrieve data from Veolia site

# Disclaimer
- This code does not pretend to be bug-free
- Although it should not harm your Jeedom system, it is provided without any warranty or liability

# Limitations
- This plugin heavily relies on how the Veolia website is structured/designed. Any change on the website will most probably break the plugin and will then require to perform code changes on the plugin.
- On Jeedom, the plugin is configured to gather data every hour. It may happen that it does not work each time: no issue, just wait for the next scheduled run.

# Contributions
This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes.
