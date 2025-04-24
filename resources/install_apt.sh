######################### INCLUSION LIB ##########################
BASE_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
wget https://raw.githubusercontent.com/NebzHB/dependance.lib/master/dependance.lib --no-cache -O ${BASE_DIR}/dependance.lib &>/dev/null
PLUGIN=$(basename "$(realpath ${BASE_DIR}/..)")
LANG_DEP=en
. ${BASE_DIR}/dependance.lib
##################################################################
wget https://raw.githubusercontent.com/NebzHB/dependance.lib/master/pyenv.lib --no-cache -O ${BASE_DIR}/pyenv.lib &>/dev/null
. ${BASE_DIR}/pyenv.lib
##################################################################

# TARGET_PYTHON_VERSION="3.11"
# VENV_DIR=${BASE_DIR}/venv
# APT_PACKAGES="first1 second2"
MININUM_OS_VERSION=11

pre

if [ $(lsb_release -r | sed -e "s/Release:\t\([0-9][0-9]*\)/\1/") -lt $MININUM_OS_VERSION ]; then
	#echo "Required Debian $MININUM_OS_VERSION or higher"
	${BASE_DIR}/install_apt_deb10.sh
	exit $?
fi

step 5 "Clean apt"
try sudo apt-get clean
step 10 "Update apt"
try sudo apt-get update

# ********************************************************
step 20 "Install Firefox"
try sudo apt-get install -y firefox-esr

# ********************************************************
step 30 "Install xvfb"
try sudo apt-get install -y xvfb

# ********************************************************
step 40 "Install geckodriver"
driver_version=""
driver_name=""
url=""

if [ $( uname -s ) == "Linux" ]; then

    if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -ge 115 ]; then
  	  	driver_version="v0.35.0"      
    elif [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -ge 91 ]; then
  	  	driver_version="v0.31.0"
    else
  	  	echo "** $(firefox --version) not supported by geckodriver"
    fi
        
	case $( uname -m ) in
	armv7l)
		echo "** Machine Hardware name: armv7l"
		driver_name="geckodriver-$driver_version-linux-armv7l.tar.gz";;
	aarch64)
		echo "** Machine Hardware name: aarch64"
		url="https://github.com/mozilla/geckodriver/releases/download"
		driver_name="geckodriver-$driver_version-linux-aarch64.tar.gz";;
	x86_64|amd64)
		echo "** Machine Hardware name:$(uname -m)"
		url="https://github.com/mozilla/geckodriver/releases/download"
		driver_name="geckodriver-$driver_version-linux64.tar.gz";;
	x86|i386|i686)
		echo "** Machine Hardware name: $(uname -m)"
		url="https://github.com/mozilla/geckodriver/releases/download"
		driver_name="geckodriver-$driver_version-linux32.tar.gz";;
	*)
		echo "** Other : $(uname -m)"
		echo "** not supported";;
	esac
else
	echo "** $(uname -s) not supported"
fi

if [ "$driver_version" != "" ]; then

	if [ -f "/usr/local/bin/geckodriver" ]; then
		try sudo cp /usr/local/bin/geckodriver $(echo "/usr/local/bin/$(uname -m)_$(geckodriver --version)" | grep "geckodriver" | sed s/' '/'_'/g | head -1 | cut -d '(' -f1)sav
	fi
	
	if [ $(uname -m) == "armv7l" ]; then
		try sudo tar xzf /var/www/html/plugins/teleo/resources/geckodriver/$driver_name
		try sudo mv geckodriver /usr/local/bin
		try sudo chmod +x /usr/local/bin/geckodriver
	else
		try sudo wget $url/$driver_version/$driver_name
		try sudo tar xzf $driver_name
		try sudo mv geckodriver /usr/local/bin
		try sudo chmod +x /usr/local/bin/geckodriver
		try sudo rm $driver_name
	fi

	if [ -f "/usr/local/bin/geckodriver" ]; then
		echo "** geckodriver driver successfully installed"
	else
		echo "** Error in geckodriver driver installation"
	fi	
else
	echo "** Error: geckodriver missing, but its mandatory for Veolia IDF WebSite. The Python script will not be able to be executed."
fi

# ********************************************************
autoSetupVenv

# ********************************************************
step 60 "Upgrade pip wheel"
try ${VENV_DIR}/bin/python3 -m pip install --upgrade pip wheel

# ********************************************************
step 70 "Install the required python packages"
try ${VENV_DIR}/bin/python3 -m pip install -r ${BASE_DIR}/requirements.txt

# ********************************************************
step 90 "Summary of installed packages"

echo '======================================================================'
echo "1. Firefox version:"
firefox --version

# Check Firefox version
case $( uname -m ) in
	armv7l)
		if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 91 ]; then
			echo "Error: Mozilla Firefox must be 91.x or higher for Veolia IDF WebSite. The Python script will not be able to be executed."
		fi;;
	*)
		if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 115 ]; then
			echo "Error: Mozilla Firefox must be 115.x or higher for Veolia IDF WebSite. The Python script will not be able to be executed."
		fi;;
esac

echo "2. geckodriver :"
if [ -f "/usr/local/bin/geckodriver" ]; then
	echo "$(uname -m) $(geckodriver --version | head -1)"
else
	echo "Error: geckodriver missing, but its mandatory for Veolia IDF WebSite. The Python script will not be able to be executed."
fi

echo "3. Packages:"
echo "$(sudo  dpkg --get-selections | grep -v deinstall | grep -E "xvfb|firefox")"

echo "4. Python version:"
${VENV_DIR}/bin/python3 --version

echo "5. Python modules:"
#${VENV_DIR}/bin/python3 -m pip freeze
echo "$(sudo ${VENV_DIR}/bin/python3 -m pip list 2>&1 | grep -E "selenium|PyVirtualDisplay|urllib3")"

post