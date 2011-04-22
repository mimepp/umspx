== how to start umsp ==
 * 1. double-click on setup_xampp.bat
  * if you fail to run this command, please install VC9 run time library:
   * http://code.google.com/p/umspx/wiki/VC9_run_time_library
  * you need run it only once, when you want to run umsp again after a reboot, just go to step 2;
 * 2. double-click on apache_start.bat
 * 3. change the IP address in umsp_start.bat to your PC's address.
 * 4. double-click on run umsp_start.bat

== what's the port of umspx ==
 * default port is 7703.

== how umsp works ==
 * 1. ssdp
  * it will use wget to request the ssdp php page
  * e.g.:
   * wget.exe http://192.168.1.3:7703/umsp/send-ssdp.php?btnSend=Submit -O - -q
 * 2. sleep
  * then sleep for 5 seconds by using ping command.