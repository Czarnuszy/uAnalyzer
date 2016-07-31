#!/usr/bin/python


import time
import serial
import string

global x

from threading import Thread, Lock
from time import sleep


def readlineCR(port):
    #sleep(0.5)
    rv = ""
    while True:
        ch = port.read()
        rv += ch
        if ch=='\r' or ch=='':
            return rv


def isdigits(s):
    idtrans=string.maketrans('0123456789','xxxxxxxxxx')
    sint=s.translate(idtrans).strip('x')
    if sint:
        return False
    else:
        return True



import os
#ret = os.system('rwee -d')
#sleep(0.1)
#ret = os.system('rwee -n')
#sleep(30)


with serial.Serial("/dev/ttyUSB0",baudrate=115200, timeout=0.5) as port:

    #rcv = readlineCR(port)
    #print rcv
    #port.write("\r")
    #rcv = readlineCR(port)
    #print rcv
    time.sleep(0.5)

    #start sweep test
    port.write(b"r")
    port.write(b"x")
    port.write(b"s")
    port.write(b"w")
    port.write(b"e")
    port.write(b"e")
    port.write(b"p")
    port.write("\r")

    rcv = readlineCR(port)
    print rcv

    #start frequency
    port.write(b"9")
    port.write(b"0")
    port.write(b"0")
    port.write(b"0")
    port.write(b"0")
    port.write(b"0")
    port.write(b"\r")

    rcv = readlineCR(port)
    print rcv
    rcv = readlineCR(port)
    print rcv
    rcv = readlineCR(port)
    print rcv
    time.sleep(0.5)

    port.write(b"9")
    port.write(b"2")
    port.write(b"2")
    port.write(b"0")
    port.write(b"0")
    port.write(b"0")
    time.sleep(0.5)

    #port.write(b"9")
    #port.write(b"2")
    #port.write(b"2")
    #port.write(b"0")
    #port.write(b"0")
    #port.write(b"0")

    rcv = readlineCR(port)
    print rcv
    port.write(b"\r")
    rcv = readlineCR(port)
    print rcv
    rcv = readlineCR(port)
    print rcv
    rcv = readlineCR(port)
    print rcv

    time.sleep(1)

    # step is 200kHz
    port.write(b"2")
    port.write(b"0")
    port.write(b"0")
    port.write(b"\r")
    rcv = readlineCR(port)
    print rcv

    #port.write(b"\r")

    #fo = open("/tmp/tmpAnalyzerData.txt", "w")
    #fo.seek(0,0)

    rcv = readlineCR(port)
    rcv = readlineCR(port)
    rcv = readlineCR(port)
    rcv = readlineCR(port)
    rcv = readlineCR(port)
    print rcv
    time.sleep(0.5)
    port.write(b"y")
    rcv = readlineCR(port)
    print rcv

    # write datat to file
    frequency = []
    rssi = []
    for i in range(0, 120):
        frequency.append(0)
        rssi.append(0)

    #while 1:
    #    rcv = readlineCR(port)
    #    print rcv
    def save_to_file(buffersize):
        fo = open("../zniffer/data/AnalyzerData.csv", "w")
        fo.seek(0, 0)
        
        for i in range(0, buffersize):
            fo.write(str(frequency[i]) + "," + str(rssi[i]) + "\n")

        fo.close()

    while 1:
        x = 0
        while True:
            rcv = readlineCR(port)
            print rcv

            buffersize = len(rcv)
            #print rcv
            #temprssi = str(rcv.rfind("*", 0, len(rcv)))
            temprssi = rcv[-3:-1]
           # tfrequency = ""

            #   if rcv[1:7] == "900000":
            #      x = 0
            # testing for errors
            print str(x)+":" + rcv[1:7] + "--|"+temprssi
         #   print int(temprssi)
            x += 1
            if buffersize > 10:
                if rcv[1] == "9" and temprssi > 0:
                    tfrequency = rcv[1:7]
                    if float(tfrequency) < 901000:
                        x = 0
                        print "sync"
                    if isdigits(tfrequency):
                        frequency[x] = rcv[1:7]
                        rssi[x] = temprssi
                        print "OK:" + str(buffersize)
                else:
                    print "oops"
                    if x > 1:
                        x -= 1
            else:
                if x > 1:
                    x -= 1
                    print "oops1"
            if frequency[x] == "920000" or frequency[x] == "920200" or frequency[x] == "920400":
                #pass
                if x > 50:
                    x += 1
                    save_to_file(x)
                    break
            #x += 1
            if x > 110:
                x = 0 # protect buffer


