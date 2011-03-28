#!/bin/bash

datestr=`date -d'-1 month' +'%m%Y'`
proc='/bin/ls -1 /var/pathtoawstats'$datestr'.*.txt | /usr/bin/xargs -L 1 /usr/bin/php /usr/pathtoscript/parse.php'

eval $proc

exit 0