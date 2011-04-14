== how to start umsp ==
 * 1. run apache_start.bat
 * 2. change the IP address in umsp_start.bat to your PC's address.
 * 3. run umsp_start.bat

== what's the port of umspx ==
 * default port is 7703.

== how umsp works ==
 * 1. ssdp
  * it will use wget to request the ssdp php page
  * e.g.:
   * wget.exe http://192.168.1.3:7703/umsp/send-ssdp.php?btnSend=Submit -O - -q
 * 2. sleep
  * then sleep for 5 seconds by using ping command.