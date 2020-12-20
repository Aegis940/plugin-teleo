# Veolia Téléo Plugin

Plugin allowing the recovery of consumption from the *Teleo* communicating meter by querying the *Veolia* customer account. As the data is not made available in real time, the plugin recovers the water consumption data of the previous day every day. 

The following types of consumption data are available:
- The **consumption index** (in L)*.
- the **daily consumption** *(in L)*.
- the **weekly consumption** *(in L)*.
- the **monthly consumption** *(in L)*.
- the **annual consumption** *(in L)*.

>**Important**      
> It is necessary to have a communicating water meter **Téléo** and a Veolia customer account. The plugin retrieves the information, according to the configuration chosen in the plugin, from the *my space* <a href="https://www.vedif.eau.veolia.fr/" target="_blank">part of the Veolia Ile de France website for Ile de France customers or from the *my personal space* <a href="https://www.service.eau.veolia.fr/" target="_blank">part of the Veolia website for others. You must therefore check that you have access to it with your usual identifiers and that the data is visible. If not, the plugin will not work.

# Configuration

## Plugin configuration

The **Veolia Téléo** plugin retrieves the information using a Python script (one for the Veolia Ile de France site and one for the other sites). Each script requires the installation of specific components:

> For the Veolia Ile de France script
*** Python version 3.7.x is required *** (*python3 --version*)
code`sudo apt-get update
code`sudo apt-get install python3 xvfb iceweasel
code`sudo pip3 install selenium pyvirtualdisplay urllib3

** For the geckodriver version, it depends on your architecture.
if the command *uname -m* returns **armv7l** (case of a Raspberry PI) then:
code`sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.23.0/geckodriver-v0.23.0-arm7hf.tar.gz && sudo tar xzfz geckodriver-v0.23.0-arm7hf.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.23.0-arm7hf.tar.gz
otherwise:
code`sudo wget https://github.com/mozilla/geckodriver/releases/download/v0.26.0/geckodriver-v0.26.0-linux32.tar.gz && sudo tar xzfz geckodriver-v0.26.0-linux32.tar.gz && sudo mv geckodriver /usr/local/bin && sudo rm geckodriver-v0.26.0-linux32.tar.gz

> For other Veolia sites
** Python 3.x version is required ** ** Python 3.x is required
code`sudo pip install requests lxml xlrd


The data is checked every hour between 4 a.m. and 10 p.m. and updated only if not available in Jeedom.

## Equipment configuration

To access the various **Veolia Téléo** facilities, go to the menu **Plugins → Energy → Veolia Téléo**.

>**Knowledge**
> The **+ Add** button allows you to add a new account **Veolia Téléo**.

On the equipment page, enter :

- the **Veolia Site** to which to connect (*Veolia Ile de France* or *Other Veolia Site*)
- the **login** and **password** of your *Veolia* customer account 

Then click on the **Save** button.

Unchecking the option **Connection to the Veolia** site allows you not to launch the Python script to retrieve the data but to use a result file deposited in the **Veolia site data export directory** (to be used in the case where the prerequisites for the pyhton script cannot be met on the machine hosting Jeedom).
The option **Force data recovery** allows the consumption information to be recovered even if it has already been recovered for the period concerned.
The **Widget template** option allows the widget to be used in the colour of the Teleo meter.

The plugin will then check the correct connection to the *Veolia* site and recover and insert in history :
- **The consumption index**: in Litres, used to calculate consumption.
- **daily consumption**: in Litres, based on the index,
- **weekly consumption**: in Litres, based on the index,
- **monthly consumption**: in Litres, based on the index,
- **annual consumption**: in Litres, based on the index,

As the calculation of consumption is based on the index, it is necessary to keep a history of at least 1 to 2 years. 

**On the first day of installation of the equipment, the values will be at 0** and it is only on the second day that the consumption data will be visible.


# Remarks

On Jeedom, the plugin is configured to gather data every hour. It may happen that it does not work each time: no issue, just wait for the next scheduled run.

The plugin is based on the way the Veolia site is structured. Any change on the site will probably lead to an error on the plugin and will require an adaptation of the python scripts more or less difficult to do.

# Contributions

This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes on <a href="https://github.com/Aegis940/plugin-teleo" target="_blank">Github</a>

# Credits

This plugin has been inspired by the work done by:

-   [Jeedom](https://github.com/jeedom)  through their Enedis plugin:  [plugin-enedis](https://github.com/jeedom/plugin-enedis)
-	[hugoKs3] (https://github.com/hugoKs3/plugin-jazpar) through his Jazpar plugin
-	[Flobul] (https://github.com/Flobul/conso_veolia) for his Python script to retrieve data from Veolia IdF site
- 	[doyenc] (https://community.jeedom.com/t/plugin-veolia-eau-plugin-veolia-eau-narrive-pas-a-se-connecter/17839/38) for his Python script to retrieve data from Veolia site


# Disclaimer

-   This code does not pretend to be bug-free
-   Although it should not harm your Jeedom system, it is provided without any warranty or liability

# ChangeLog
Available [here](./changelog.html).
