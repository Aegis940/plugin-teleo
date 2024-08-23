#!/bin/bash

# This file is part of Plugin teleo for jeedom.

#set -x  # make sure each command is printed in the terminal
PROGRESS_FILE=/tmp/dependency_teleo_in_progress
if [ ! -z "$1" ]; then
    PROGRESS_FILE=$1
fi
touch "${PROGRESS_FILE}"

echo 0 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "             Installation des dépendances               "
echo "********************************************************"

echo 5 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "        Update package lists from repositories          "
echo "********************************************************"
sudo apt-get update
 
echo 20 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "                     Install Firefox                    "
echo "********************************************************"
sudo apt-get install -y firefox-esr

if [ "$(sudo dpkg --get-selections | grep -v deinstall | grep -E "firefox")" = "" ]; then
	echo "No firefox installed"
	exit 0
fi
 
echo 30 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "              Install xvfb                              "
echo "********************************************************"
sudo apt-get install -y xvfb

echo 40 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "              Install geckodriver                       "
echo "********************************************************"

driver_version=""
driver_name=""
url=""

if [ $( uname -s ) == "Linux" ]; then

    if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -ge 115 ]; then
  	  	driver_version="v0.35.0"    
    elif [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -ge 91 ]; then
  	  	driver_version="v0.31.0"
    else
  	  	echo "$(firefox --version) not supported by geckodriver"
    fi
        
	case $( uname -m ) in
	armv7l)
		echo "Machine Hardware name: armv7l"
		driver_name="geckodriver-$driver_version-linux-armv7l.tar.gz";;
	aarch64)
		echo "Machine Hardware name: aarch64"
		url="https://github.com/mozilla/geckodriver/releases/download"
		driver_name="geckodriver-$driver_version-linux-aarch64.tar.gz";;
	x86_64|amd64)
		echo "Machine Hardware name:$(uname -m)"
		url="https://github.com/mozilla/geckodriver/releases/download"
		driver_name="geckodriver-$driver_version-linux64.tar.gz";;
	x86|i386|i686)
		echo "Machine Hardware name: $(uname -m)"
		url="https://github.com/mozilla/geckodriver/releases/download"
		driver_name="geckodriver-$driver_version-linux32.tar.gz";;
	*)
		echo "Other : $(uname -m)"
		echo "not supported";;
	esac
else
	echo "$(uname -s) not supported"
fi

if [ "$driver_version" != "" ]; then

	if [ -f "/usr/local/bin/geckodriver" ]; then
		sudo cp /usr/local/bin/geckodriver $(echo "/usr/local/bin/$(uname -m)_$(geckodriver --version)" | grep "geckodriver" | sed s/' '/'_'/g | head -1 | cut -d '(' -f1)sav
	fi
	
	if [ $(uname -m) == "armv7l" ]; then
		sudo tar xzf /var/www/html/plugins/teleo/resources/geckodriver/$driver_name
		sudo mv geckodriver /usr/local/bin
		sudo chmod +x /usr/local/bin/geckodriver
	else
		sudo wget $url/$driver_version/$driver_name
		sudo tar xzf $driver_name
		sudo mv geckodriver /usr/local/bin
		sudo chmod +x /usr/local/bin/geckodriver
		sudo rm $driver_name
	fi

	if [ -f "/usr/local/bin/geckodriver" ]; then
		echo "geckodriver driver successfully installed"
	else
		echo "Error in geckodriver driver installation"
	fi	
else
	echo "Error: geckodriver missing, but its mandatory for Veolia IDF WebSite. The Python script will not be able to be executed."
	exit 0
fi

echo 50 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "            Install Python3 and dependencies            "
echo "********************************************************"
sudo apt-get install -y python3 python3-pip

echo 60 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'selenium' module                "
echo "********************************************************"
sudo pip3 install selenium -U

urllib3_version=""

if [ $(sudo pip3 list 2>&1 | grep -E "selenium" | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -eq 4 ]; then

	if [ $(sudo pip3 list 2>&1 | grep -E "selenium" | sed -e "s/.*\.\([0-9][0-9]*\)\..*/\1/") -lt 11 ]; then
		echo "$(sudo pip3 list 2>&1 | grep -E "selenium")"
		echo "La version de selenium 4 'disponible est inférieur à la version 4.11.0, installation de la version 3.141.0"
		sudo pip3 install selenium==3.141.0
		urllib3_version="1.26.16"
	elif [ $(lsb_release -r | sed -e "s/Release:\t\([0-9][0-9]*\)/\1/") -eq 10 ]; then
		echo "$(sudo pip3 list 2>&1 | grep -E "selenium")"
		echo "La version de selenium 4 'disponible est nécessite python 3.8+ indisponible sous Debian 10, installation de la version 3.141.0"
		sudo pip3 install selenium==3.141.0
		urllib3_version="1.26.16"
	fi
fi

echo 70 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'pyvirtualdisplay' module        "
echo "********************************************************"
sudo pip3 install pyvirtualdisplay

echo 90 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'urllib3' module                 "
echo "********************************************************"
# compatibility issue beetween selenium and urllib3
if [ "$urllib3_version" != "" ]; then
	sudo pip3 install urllib3==$urllib3_version
else
	sudo pip3 install urllib3 -U
fi

echo 100 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "              Installation terminée                     "
echo "********************************************************"

echo "Résumé installation :"
echo

echo "1. $(firefox --version)"
# Check Firefox version
case $( uname -m ) in
	armv7l)
			if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 91 ]; then
			echo "Error: Mozilla Firefox must be 91.x or higher for Veolia IDF WebSite. The Python script will not be able to be executed."
		fi;;
	*)
		if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 115 ]; then
			echo "Error: Mozilla Firefox must be 102.x or higher for Veolia IDF WebSite. The Python script will not be able to be executed."
		fi;;
esac	

echo "2. geckodriver :"
if [ -f "/usr/local/bin/geckodriver" ]; then
	echo "$(uname -m) $(geckodriver --version | head -1)"
else
	echo "Error: geckodriver missing, but its mandatory for Veolia IDF WebSite. The Python script will not be able to be executed."
fi

echo "3. Packages:"
echo "$(sudo  dpkg --get-selections | grep -v deinstall | grep -E "xvfb|firefox|python3\-pip")"

echo "4. $(python3 --version)"

echo "5. Python modules:"
echo "$(sudo pip3 list 2>&1 | grep -E "selenium|PyVirtualDisplay|urllib3")"

rm "${PROGRESS_FILE}"
