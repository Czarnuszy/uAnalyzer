from ZWapi import *

zw = ZWapi("/dev/ttyS0")

t=0

while t < 5:
    print zw.ZW_AddNodeToNetwork(02, None)
    time.sleep(1)
    t+=1

zw.stop()
print "Exit"
