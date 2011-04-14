#!/usr/bin/env python
#-*- coding: UTF-8 -*-

from BaseHTTPServer import BaseHTTPRequestHandler, HTTPServer
import subprocess, time

last_ch = 0

class TvServerHandler(BaseHTTPRequestHandler):
   def do_GET(self):
      global last_ch
      cmd = self.path.split('/')
      if 'favicon.ico' in cmd:
         return
      ch = int(cmd[1])
      if not ch or ch < 1:
         ch = 1
      if ch == last_ch:
         return
      last_ch = ch
      
      p = subprocess.Popen("killall VLC",shell=True)
      time.sleep(0.5)
      cmd = "/Applications/VLC.app/Contents/MacOS/VLC -I dummy eyetv:// --sout='#std{access=http,mux=ts,dst=<your ip>:8484}' --sout-keep --autocrop --intf dummy --eyetv-channel=%s" % ch
      p = subprocess.Popen(cmd,shell=True,stdout=subprocess.PIPE,stderr=subprocess.STDOUT,close_fds=True)
      time.sleep(0.5)

      self.send_response(301)
      self.send_header("Location", "http://<your ip>:8484?t=%f" % time.time())
      self.end_headers()
      return
      
   def do_POST(self):
      pass
      return

def main():
   try:
      server = HTTPServer(('',8485),TvServerHandler)
      print 'server started'
      server.serve_forever()
   except KeyboardInterrupt:
      print 'shutting down'
      server.socket.close()

if __name__ == '__main__':
   main()

