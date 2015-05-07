# Install #
> umspx is based on xampp, so you need to install xampp first.
  * 1. xammp for linux - lampp
    * http://www.apachefriends.org/en/xampp-linux.html
    * please follow its 4 steps to install lampp
    * then you will get lampp in /opt/lampp
  * 2. umspx
    * download umspx "umspx\_vx.x\_script\_only.zip" to /tmp
    * cd /tmp
    * unzip umspx\_vx.x\_script\_only.zip
    * cd umspx
    * sudo cp -rf `*` /opt/lampp
    * sudo cp -rf ./linux/`*` /opt/lampp

# Run #
  * cd /opt/lampp
  * sudo ./lampp start
  * sudo vi umsp\_start.sh
    * change the IP address to your PC's
  * sudo sh umsp\_start.sh start
  * then you should find umspx on your upnp server list