#!/bin/bash

# Check argv
if [ "$#" -ne 4 ]; then
	echo 0
	exit 0
fi

if [ "$1" = "IDF" ]; then
	scriptname="get_veolia_idf_consommation.py"
elif [ "$1" = "Other" ]; then
	scriptname="get_veolia_other_consommation.py"
else
	echo 0
	exit 0
fi

root='/var/www/html/plugins/teleo/resources'
python="python3" 

# Timeout in secondes#
timeout=80

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