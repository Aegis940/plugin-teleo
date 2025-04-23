# Veolia Téléo Plugin
![plugin-teleo logo](https://aegis940.github.io/plugin-teleo/assets/images/logo.png)

Plugin allowing the recovery of consumption from the *Teleo* communicating meter by querying the *Veolia* customer account. As the data is not made available in real time, the plugin recovers the water consumption data of the previous day every day. 

The following types of consumption data are available:
- The **consumption index** *(in L)*.
- The **daily consumption** *(in L)*.
- The **weekly consumption** *(in L)*.
- The **monthly consumption** *(in L)*.
- The **annual consumption** *(in L)*.

>**Important**      
> It is necessary to have a communicating water meter **Téléo** and a Veolia customer account. The plugin retrieves the information, according to the configuration chosen in the plugin, from the *my space* <a href="https://connexion.leaudiledefrance.fr" target="_blank">part of the Veolia Ile de France website</a> for Ile de France customers. You must therefore check that you have access to it with your usual identifiers and that the data is visible. **If not, the plugin will not work.**

# Installation of software dependencies

> The **Veolia Téléo** plugin retrieves the information using a Python script.

Python ***3.7.x version*** is required and ***Firefox 115 or higher*** (so use **buster distrib**). However, the recommended version of the plugin is ***Python 3.9.x*** or higher and so a migration to a **bullseye distrib** is more than recommended.


> The software dependencies are installed automatically by Jeedom within 5 minutes. They will also be reinstalled when the plugin is updated if necessary.

# Configuration

## Plugin configuration

> The data is checked every hour between 4 a.m. and 10 p.m. and updated only if not available in Jeedom. The start time can be set between 1 a.m. and 8 p.m.

## Equipment configuration

To access the various **Veolia Téléo** facilities, go to the menu **Plugins → Energy → Veolia Téléo**.

> **Knowledge**
> The **+ Add** button allows you to add a new account **Veolia Téléo**.

On the equipment page, enter :

- the **Veolia Site** to which to connect (*Veolia Ile de France*)
- the **login** and **password** of your *Veolia* customer account 
- If several contracts are attached to the same **Veolia IDF** account, indicate the **contract number** where the data will be retrieved (otherwise leave blank).

Then click on the **Save** button.

- Unchecking the option **Connection to the Veolia** site allows you not to launch the Python script to retrieve the data but to use a result file deposited in the **Veolia site data export directory** (to be used in the case where the prerequisites for the pyhton script cannot be met on the machine hosting Jeedom).
- The option **Ignore estimated records** allows you to skip importing an index record estimated.
- The option **Force data recovery** allows the consumption information to be recovered even if it has already been recovered for the period concerned.
- The **Widget template** option allows the widget to be used in the colour of the Teleo meter.

The plugin will then check the correct connection to the *Veolia* site and recover and insert in history :
- **The consumption index**: in Litres, used to calculate consumption.
- **daily consumption**: in Litres, based on the index,
- **weekly consumption**: in Litres, based on the index,
- **monthly consumption**: in Litres, based on the index,
- **annual consumption**: in Litres, based on the index,

> As the calculation of consumption is based on the index, it is necessary to keep a history of at least 2 years (1 year and 2 days in fact). 
>
> For the *weekly*, *monthly* and *annual* periods, only the last history of the current period is kept.


# Remarks

On Jeedom, the plugin is configured to gather data every hour. It may happen that it does not work each time: no issue, just wait for the next scheduled run.

The plugin is based on the way the Veolia site is structured. Any change on the site will probably lead to an error on the plugin and will require an adaptation of the python scripts more or less difficult to do.

# Contributions

This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes on <a href="https://github.com/Aegis940/plugin-teleo" target="_blank">Github</a>

# Credits

This plugin has been inspired by the work done by:

- [Jeedom](https://github.com/jeedom)  through their Enedis plugin:  [plugin-enedis](https://github.com/jeedom/plugin-enedis)
- [hugoKs3](https://github.com/hugoKs3/plugin-jazpar) through his Jazpar plugin
- [Flobul](https://github.com/Flobul/conso_veolia) for his Python script to retrieve data from Veolia IdF site


# Disclaimer

-   This code does not pretend to be bug-free
-   Although it should not harm your Jeedom system, it is provided without any warranty or liability

# ChangeLog
Available [here](./changelog.md).
