
from __future__ import print_function
import sys

ENABLE=True
DISABLE=False
def bytes_array_to_hex_string(arr):
    s=""
    if len(arr)>0:
        for i in range(len(arr)):
            s+="%02X "% arr[i]
    return s
def print_bytes(DBG, arr,print_mode=False):
    if len(arr)>0:
        s = bytes_array_to_hex_string(arr)
        if print_mode==True:
            dbgprint(DBG, s)
            dbgprint(DBG, "\n")
        else:
            dbglog(DBG, s)

def dbg(enable, args, mode="log"):
    import inspect
    import logging
    logger = logging.getLogger('root')
    logger.setLevel(logging.DEBUG)
    # Get the previous frame in the stack, otherwise it would
    # be THIS function!
    func = inspect.currentframe().f_back.f_back.f_code #go upwards twice, 
    # Dump the message + the name of this function to the log.
    prefix=""
    if enable == True:
        if mode =="log":
            prefix="%-48s %-20s" % (func.co_name + "():" + str(func.co_firstlineno) + ": ", args)
            myend='\n'
        elif mode =="print":
            prefix=args
            myend=''
        else:
            raise Exception ("unknown log mode:", mode)
        print(prefix,file=sys.stderr, end=myend)

        
def dbgprint(enable, args):
    dbg(enable, args, mode="print")

def dbglog(enable, args):
    dbg(enable, args, mode="log")
