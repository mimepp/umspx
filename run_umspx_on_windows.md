## Install ##
  * since umspx is based on xampp which is a "Apache + PHP" package, there will be a full package and script\_only package for umspx
    * full package includes the script\_only
    * full = xampp + umspx\_script\_only
    * if version of script\_only is large, you need to use the latest one.
  * download umspx full package:
    * e.g.: umspx\_vx.x\_full.7z
    * http://code.google.com/p/umspx/downloads/list
  * uncompressed it to c:\
  * you should get c:\umspx
  * download latest script\_only package:
    * e.g.: umspx\_vx.x\_script\_only.zip
    * if the version of script\_only is the same as version of full usmpx, you do not need to download the script\_only package.
  * unzip it to c:\, it will over write files in the c:\umspx

## How to start umsp ##
  * 1. double-click on setup\_xampp.bat
    * if you fail to run this command, please install VC9 run time library:
      * http://code.google.com/p/umspx/wiki/VC9_run_time_library
    * you need run it only once, when you want to run umsp again after a reboot, just go to step 2;
  * 2. double-click on apache\_start.bat
  * 3. change the IP address in umsp\_start.bat to your PC's address.
  * 4. double-click on run umsp\_start.bat
  * 5. If you could not see umspx on your upnp server list, please close your "windows firewall"

## What's the port of umspx ##
  * default port is 7703.

## How umsp works ##
  * 1. ssdp
    * it will use wget to request the ssdp php page
    * e.g.:
      * wget.exe http://192.168.1.3:7703/umsp/send-ssdp.php?btnSend=Submit -O - -q
  * 2. sleep
    * then sleep for 5 seconds by using ping command.
    * you could change this timeout to a long value you want if you do not want it send ssdp too fast