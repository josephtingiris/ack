if [ "$WHOM" == "" ]; then export WHOM=$(logname); fi
if [ "$WHO" == "" ]; then export WHO="${WHOM%% *}"; fi
if [ "$WHO" == "" ]; then export WHO=$USER; fi
if [ "$WHO" == "" ]; then export WHO=$LOGNAME; fi
if [ "$WHO" == "" ]; then export WHO=UNKNOWN; fi
if [ "$Apex_User" == "" ]; then export Apex_User=${WHO}@$HOSTNAME; fi
if [ "$Base_User" == "" ]; then export Base_User=$Apex_User; fi

