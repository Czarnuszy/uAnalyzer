'''
Created on Oct 10, 2013

@author: aes
'''
import threading
from time import time

class TimeoutException(Exception):
    pass

class Waiter():
    def __init__(self):
        self.ev = threading.Event()

    def f(self, *args, **kwargs):
        self.args = args;
        self.kwargs = kwargs
        self.ev.set()

    def wait(self,timeout=60,raiseOnTimeout=True):
        ''' Wait for callback to be called, raise exception or return False on timeout '''
        t1 = time()

        self.ev.wait(timeout)
        self.ev.clear()
        if( (time()-t1) >=timeout ):
            return False
        else:
            return True
