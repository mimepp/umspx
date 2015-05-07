## 安装 ##
  * umspx 是基于 xampp 的，xampp 是将 "Apache + PHP" 打包的一个项目,因此 umspx 提供了 full 版本和 script\_only 版本的内容
    * full 包含了script\_only
    * full = xampp + umspx\_script\_only
    * 如果你在下载里看到有更加新的版本的 script\_only，那么你需要再多下载这个新版本的 script\_only
  * 下载 umspx full 包:
    * 如: umspx\_vx.x\_full.7z
    * http://code.google.com/p/umspx/downloads/list
  * 将其解压缩到 c:\ 根目录
  * 这个时候你应该得到了  c:\umspx
  * 下载最新的 script\_only:
    * 如: umspx\_vx.x\_script\_only.zip
    * 如果下载区域有高版本的 script\_only，那么你需要下载它
    * 将其解压到 c:\, 也即它会覆盖 c:\umspx 下旧版本的script

## 运行 umsp ##
  * 1. 双击 setup\_xampp.bat
    * 如果运行失败，可能是你没有安装 VC9 运行库:
      * http://code.google.com/p/umspx/wiki/VC9_run_time_library
    * 本命令只需要运行一次，以后每次启动机器，不用再次执行本命令，而直接执行 step 2 即可;
  * 2. 双击 apache\_start.bat
  * 3. 修改 umsp\_start.bat 里的 IP 地址为你自己的 PC 的 IP 地址
  * 4. 双击 umsp\_start.bat
  * 5. 这个时候你应该看到 umspx 已经运行起来了，如果你在 upnp server list 里看不到 umspx，那么可能是你 PC 的防火墙没关闭导致的

## umspx 的端口 ##
  * 缺省端口是 7703.

## umspx 如何工作 ##
  * 1. ssdp
    * 它会使用 wget 来请求一个页面，发送 ssdp
    * 如:
      * wget.exe http://192.168.1.3:7703/umsp/send-ssdp.php?btnSend=Submit -O - -q
  * 2. sleep
    * 每次会间隔 5秒发送一次 ssdp
    * 你如果觉得发送过快，那么你可以把这个数值改大些，如 60 秒