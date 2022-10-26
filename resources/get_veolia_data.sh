#!/bin/bash

# Check argv
if [ "$#" -le 4 ]; then
	echo -e "Usage: $0 <site_type> <username> <password> <output_dir> <log_level> [<ContractID>]"
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
timeout=180

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
	if [ $(firefox --version 2>&1 | sed -e "s/.* \([0-9][0-9]*\)\..*/\1/") -lt 102 ]; then
		echo "Mozilla Firefox must be 102.x or higher"
		exit 0
	fi
fi

# Launch python script
nbofretry=2
codret=0

loop=0 
until [ $codret -eq 1 ] || [ $loop -ge $nbofretry ]
do
	timeout --signal=SIGINT ${timeout} $python $root/$scriptname $2 $3 $4 $5 $6
	codret=$?

	((loop++))
done


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
	
	nbprocess=$(pgrep -f "Xvfb -br -nolisten tcp -screen 0 800x600x24 -displayfd 5" -c)
	if [ ! $nbprocess -eq 0 ]; then
		pkill -f "Xvfb -br -nolisten tcp -screen 0 800x600x24 -displayfd 5"
	fi
	
	nbprocess=$(pgrep -f "/usr/local/bin/geckodriver" -c)
	if [ ! $nbprocess -eq 0 ]; then
		pkill -f "/usr/local/bin/geckodriver"
	fi		
fi

echo ${codret}
exit ${codret}
