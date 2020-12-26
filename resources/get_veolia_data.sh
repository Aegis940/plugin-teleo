#!/bin/bash

# Check argv
if [ "$#" -ne 4 ]; then
	echo -e "Usage: $0 <site_type> <username> <password> <output_dir>"
	exit 0
fi

# Select script according to site type
if [ "$1" = "IDF" ]; then
	scriptname="get_veolia_idf_consommation.py"
elif [ "$1" = "Other" ]; then
	scriptname="get_veolia_other_consommation.py"
else
	echo "Unknown site type"
	exit 0
fi

# Setup
root='/var/www/html/plugins/teleo/resources'
python="python3" 
timeout=80

# Check Python version
if [ "$1" = "IDF" ]; then
	if [ $($python --version 2>&1 | grep -c 'Python 3.7.') == "0" ]; then
		echo "Python version must be 3.7.x"
		exit 0
	fi
elif [ "$1" = "Other" ]; then
	if [ $($python --version 2>&1 | grep -c 'Python 3.') == "0" ]; then
		echo "Python version must be 3.x"
		exit 0
	fi
fi	

# Check Firefox version
if [ "$1" = "IDF" ]; then 
	if [ $(firefox --version 2>&1 | sed -e "s/^Mozilla Firefox \([0-9][0-9]*\)\.[0-9][0-9]*\.[0-9][0-9]*$/\1/") -le 60 ]; then
		echo "Mozilla Firefox must be 60.x or higher"
		exit 0
	fi
fi

# Launch python script
timeout --signal=SIGINT ${timeout} $python $root/$scriptname $2 $3 $4
codret=$?

# Retry one time
if [ $codret -eq 0 ]; then
	timeout --signal=SIGINT ${timeout} $python $root/$scriptname $2 $3 $4
	codret=$?
fi

if [ $codret -eq 1 ]; then
	if [ ! -f "$4/historique_jours_litres.csv" ]; then
		codret=0
	fi
fi

# Kill ghosts processes
nbprocess=$(pgrep -f "$python $root/$scriptname" -c)
if [ ! $nbprocess -eq 0 ]; then
    pkill -f "$python $root/$scriptname"
fi

if [ "$1" = "IDF" ]; then
	nbprocess=$(pgrep -f "firefox-esr -marionette -headless -profile" -c)
	if [ ! $nbprocess -eq 0 ]; then
		pkill -f "firefox-esr -marionette -headless -profile"
	fi
fi

echo ${codret}
exit ${codret}