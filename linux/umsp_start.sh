#!/bin/sh

case $1 in
start)
	rm -rf /tmp/ssdp.stop
	count=0
	echo "umsp started ..."
	while [ ! -e /tmp/ssdp.stop ]; do
		if [ $count -eq 5 ] ; then
			wget http://192.168.1.3:7703/umsp/send-ssdp.php?btnSend=Submit -O /dev/null 2>/dev/null >/dev/null
			count=0
		fi
		count=`expr $count + 1`
		sleep 1;
	done
	;;
stop)
        touch /tmp/ssdp.stop
	;;
esac
