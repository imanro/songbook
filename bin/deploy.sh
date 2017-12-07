#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
LIBS_DIR="$SCRIPT_DIR/libs";
CFG_DIR="$SCRIPT_DIR/../etc"
SSH_CMD="ssh";

. $LIBS_DIR/shini.sh

__shini_parsed ()
{
    if [ "$1" == "$ENVIRONMENT" ]; then
	export $2="$3";
    fi;
}

if [ $# -eq 0 ]; then
    echo "You should pass environment and (optionaly) \"go\" parameters to this script";
else

    ENVIRONMENT=$1;
    shini_parse "$CFG_DIR/deploy.ini";

    if [ -z "$SSH_USER" ] || [ -z "$HOST" ] || [ -z "$REMOTE_PATH" ]; then
	echo "Wrong environment name given: its not defined in deploy.ini";
	exit 1;
    fi;
    
    if [[ -z $2 ]]; then
	DRY_RUN_OPT="--dry-run";
        echo "Running dry-run"
    elif [ $2 == "go" ]; then
	DRY_RUN_OPT="";
        echo "Running actual deploy"
    else
        echo "Only \"go\" parameter acceptable";
	exit 1;
    fi;

    if [[ ! -z "$SSH_PASS" ]]; then
	RSYNC_CMD="sshpass -p ${SSH_PASS} rsync --progress -rlptDzE $DRY_RUN_OPT --force --delete --exclude-from=$CFG_DIR/deploy_exclude.txt ${SCRIPT_DIR}/../ ${SSH_USER}@${HOST}:${REMOTE_PATH}";
    else
	RSYNC_CMD="rsync -rlptDzE $DRY_RUN_OPT --force --delete --progress --exclude-from=$CFG_DIR/deploy_exclude.txt ${SCRIPT_DIR}/../ ${SSH_USER}@${HOST}:${REMOTE_PATH}";
    fi;
    echo $RSYNC_CMD;
    $RSYNC_CMD;
fi;


exit 0;
