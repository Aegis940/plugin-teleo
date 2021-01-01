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

# Check Firefox version
if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 60 ]; then
	echo "Warning: Mozilla Firefox must be 60.x or higher for Veolia IDF WebSite"
	echo "Current version is: $(firefox --version)"
fi
 
echo 30 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "              Install iceweasel and xvfb                "
echo "********************************************************"
sudo apt-get install -y xvfb iceweasel

echo 40 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "              Install geckodriver driver                "
echo "********************************************************"

driver_version=""
driver_name=""

if [ $( uname -s ) == "Linux" ]; then
  
	case $( uname -m ) in
	armv7l)
		echo "Machine Hardware name: armv7l"
		driver_version="v0.23.0"
		driver_name="geckodriver-$driver_version-arm7hf.tar.gz";;
	x86_64|aarch64|AMD64)
		echo "Machine Hardware name:$(uname -m)"
		driver_version="v0.28.0"
		driver_name="geckodriver-$driver_version-linux64.tar.gz";;
	x86|i686)
		echo "Machine Hardware name: $(uname -m)"
		driver_version="v0.28.0"
		driver_name="geckodriver-$driver_version-linux32.tar.gz";;
	*)
		echo "other : $(uname -m)"
		echo "not supported";;
	esac
else
	echo "$(uname -s) not supported"
fi

if [ $driver_version != "" ]; then
	sudo wget https://github.com/mozilla/geckodriver/releases/download/$driver_version/$driver_name
	sudo tar xzfz $driver_name
	sudo mv geckodriver /usr/local/bin
	sudo chmod +x /usr/local/bin/geckodriver
	sudo rm $driver_name

	if [ -f "/usr/local/bin/geckodriver" ]; then
		echo "geckodriver driver successfully installed"
	else
		echo "Error in geckodriver driver installation"
	fi	
fi

echo 50 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "            Install Python3 and dependencies            "
echo "********************************************************"
sudo apt-get install -y python3 python3-pip

# Check Python version
if [ $(python3 --version 2>&1 | grep -c 'Python 3.7.') == "0" ]; then
	echo "Warning: Python version must be 3.7.x for Veolia IDF WebSite"
	echo "Current version is: $(python3 --version)"
fi

echo 70 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'requests' module                "
echo "********************************************************"
sudo pip3 install requests

echo 75 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'lxml' module                    "
echo "********************************************************"
sudo pip3 install lxml

echo 80 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'xlrd' module                    "
echo "********************************************************"
sudo pip3 install xlrd

echo 85 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'selenium' module                "
echo "********************************************************"
sudo pip3 install selenium

echo 90 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'pyvirtualdisplay' module        "
echo "********************************************************"
sudo pip3 install pyvirtualdisplay

echo 95 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "               Python3 'urllib3' module                 "
echo "********************************************************"
sudo pip3 install urllib3

echo 100 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "              Installation terminée                     "
echo "********************************************************"

echo "Résumé installation :"
echo

echo "1. $(firefox --version)"
if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 60 ]; then
	echo "Warning: Mozilla Firefox must be 60.x or higher for Veolia IDF WebSite. The Python script will not be able to be executed."
fi

echo "2. geckodriver driver:"
if [ -f "/usr/local/bin/geckodriver" ]; then
	echo "geckodriver driver $driver_version for $(uname -m)"
else
	echo "Warning: geckodriver driver missing, but its mandatory for Veolia IDF WebSite. The Python script will not be able to be executed."
fi

echo "3. Packages:"
echo "$(sudo  dpkg --get-selections | grep -v deinstall | grep -E "xvfb|firefox|iceweasel|python3\-pip|python3\-requests|python3\-urllib3")"

echo "4. $(python3 --version)"
if [ $(python3 --version 2>&1 | grep -c 'Python 3.7.') == "0" ]; then
	echo "Warning: Python version must be 3.7.x for Veolia IDF WebSite. The Python script will not be able to be executed."
fi

echo "5. Python modules:"
echo "$(sudo pip3 list --format=legacy | grep -E "requests|lxml|xlrd|selenium|PyVirtualDisplay|urllib3")"

rm "${PROGRESS_FILE}"