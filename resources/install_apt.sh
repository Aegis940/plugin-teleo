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
echo "*             Installation des dépendances             *"
echo "********************************************************"

echo 5 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*        Update package lists from repositories        *"
echo "********************************************************"
sudo apt-get update

echo 20 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*         Install iceweasel and xvfb                   *"
echo "********************************************************"
sudo apt-get install -y xvfb iceweasel

echo 30 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*         Install geckodriver driver                   *"
echo "********************************************************"

driver_version=""
driver_name=""

if [ $( uname -s ) eq "Linux" ]; then
  
	case $( uname -m ) in
	armv7l)
		echo "Machine Hardware name: armv7l"
		driver_version="v0.23.0"
		driver_name="geckodriver-$driver_version-arm7hf.tar.gz";;
	x86_64|aarch64)
		echo "Machine Hardware name:$(uname -m)"
		driver_version="v0.28.0"
		driver_name="geckodriver-$driver_version-linux64.tar.gz";;
	i686)
		echo "Machine Hardware name: i686"
		driver_version="v0.28.0"
		driver_name="geckodriver-$driver_version-linux32.tar.gz";;
	*)
		echo "other : $(uname -m)"
		echo "not supported";;
		rm ${PROGRESS_FILE}
		exit 1
	esac
else
	echo "$(uname -s) not supported"
	rm ${PROGRESS_FILE}
	exit 1
fi

if [ $driver_version != "" ]; then
	sudo wget https://github.com/mozilla/geckodriver/releases/download/$driver_version/$driver_name
	sudo tar xzfz $driver_name
	sudo mv geckodriver /usr/local/bin
	sudo rm $driver_name
fi

echo 40 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*         Install Python3 and dependencies             *"
echo "********************************************************"
sudo apt-get install -y python3 python3-pip

echo 70 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*             Python3 'requests' module                *"
echo "********************************************************"
sudo pip3 install requests

echo 75 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*             Python3 'lxml' module                    *"
echo "********************************************************"
sudo pip3 install lxml

echo 80 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*             Python3 'xlrd' module                    *"
echo "********************************************************"
sudo pip3 install xlrd

echo 85 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*              Python3 'selenium' module               *"
echo "********************************************************"
sudo pip3 install selenium

echo 90 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*              Python3 'pyvirtualdisplay' module       *"
echo "********************************************************"
sudo pip3 install pyvirtualdisplay

echo 95 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*              Python3 'urllib3' module                *"
echo "********************************************************"
sudo pip3 install urllib3

echo 100 > "${PROGRESS_FILE}"
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm "${PROGRESS_FILE}"