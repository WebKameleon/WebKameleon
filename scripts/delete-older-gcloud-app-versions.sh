#!/bin/bash

VERSIONS=$(gcloud app versions list --project $1 --service default --sort-by '~version' --format 'value(version.id)')
COUNT=0
echo "Keeping the $2 latest versions of the $1 service"

for VERSION in $VERSIONS
do
    COUNT=`expr $COUNT + 1`
    if [ $COUNT -gt $2 ]
    then
      echo "Going to delete version $VERSION of the $1 "
      gcloud app versions delete $VERSION --service default --project $1 -q
    else
      echo "Going to keep version $VERSION of the $1 service."
    fi
done
