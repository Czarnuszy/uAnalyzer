'''
Created on 22/04/2013

@author: jbu
'''
import serial
import thread
import threading
import logging
from ZW_SerialAPI import *
from time import clock
from struct import *
import time
from multiprocessing import TimeoutError,BufferTooShort
from ZW_basis_api import ZW_LIB_CONTROLLER, ZW_LIB_CONTROLLER_STATIC,\
    ZW_LIB_CONTROLLER_BRIDGE
#from serial.serialutil import SerialTimeoutException

class LoggedSerial(serial.Serial):
    '''
    Drop-in replacement for serial.Serial that will log all UART activity to file.
    '''


    def __init__(self, *args, **kwargs):
        '''
        Constructor
        '''
        serial.Serial.__init__(self, *args, **kwargs)
        import random
        filename = 'seriallog.' + kwargs['port'].split('/')[-1]
        self.log = open(filename, 'a')
    #    self.log.write('\n') # Blank line marks restart of ZWApi
        self.log_direction=0

    def logTX(self, msg):
        if self.log_direction == 0:
            self.log.write("\n" + str(int(time.time()*1000)) + " W ")
            self.log_direction = 1
        msg = ' '.join([c.encode('hex') for c in msg])+" "
        self.log.write(msg)
        self.log.flush()

    def logRX(self, ch):
        if self.log_direction == 1:
            self.log.write("\n" + str(int(time.time()*1000)) + " R ")
            self.log_direction = 0
        self.log.write(ch.encode('hex') + ' ')

        self.log.flush()

    def read(self, n):
        ch = super(serial.Serial, self).read(n)
        # if ch:
        #     for c in ch:
        #         self.logRX(c)
        return ch

    def write(self, msg):
        super(serial.Serial, self).write(msg)
    #    self.logTX(msg)
