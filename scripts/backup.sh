#!/bin/bash
current_date=$(date +'%d-%m-%Y')
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
echo "Creating Backup Directory"
backup_directory="${DIR}/../backups"
sudo mkdir -p $backup_directory
echo "Backing Up File To ${backup_directory}/${current_date}.sql"
sudo mysqldump -u ironman -p'pNV44xH!N=Jv2%Bu5EWn86Msu7-wX^' superstore_database -h workdatabase.cuyqkwdewraf.eu-west-2.rds.amazonaws.com > "${backup_directory}/${current_date}.sql" &