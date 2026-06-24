SERVER_IP=35.240.172.43

sync:
	rsync -av -e "ssh -i ~/.ssh/chauhm.old" --exclude "runtime" --exclude "vendor" --exclude "web/assets" ./ chauhm@${SERVER_IP}:/home/report/ 