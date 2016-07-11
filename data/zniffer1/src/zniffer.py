#!/usr/bin/python


import time
import serial


from threading import Thread, Lock
import signal
import sys
import Queue
import argparse
import os
from datetime import datetime
import inspect
import resource



currentdir = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))
parentdir = os.path.dirname(currentdir)
common_lib_dir=parentdir+"/common"

sys.path.insert(0,common_lib_dir)

from kbhit import KBHit
from y_bintools import *
from zniffer_globals import  *
from zniffer_parser import *
from y_dbglog import * 
from y_timestamp import *

CRASHDBG=DISABLE
PARSEDBG=DISABLE
SERIALDBG=DISABLE
TIMINGDBG=DISABLE
BASEDBG=DISABLE

ENABLE_MEM_USAGE_ANALYSIS=False


"""globals"""
LITTLE_ENDIAN=True


QUEUE_MESSAGE_QUIT=0x1
ZLF_FILE_SUFFIX=".zlf"
CSV_FILE_SUFFIX=".csv"
SESSION_NUMBER=0x00
API_TYPE_ZNIFFER=0x00
COMMAND_PAYLOAD_ONE=0x01
MAX_SERIAL_DATA_SIZE=256
HEADER_SIZE=0x800
EOD_VALUE=0xFE

ENABLE_PARSER=True 

if ENABLE_PARSER == True:
    SLEEP_TIME=0.5 #seconds
    SERIAL_TIMEOUT=0.2 
else:
    SLEEP_TIME=0.001 #seconds
    SERIAL_TIMEOUT=0.001 #seconds
#it is best to have ENABLE_PARSER==True: serial frames are parsed and splitted appropriately, serial and thread timeouts can be quite relaxed.
#else to distinguish between two serial frames, serial and thread timeouts must be very short and CPU usage explodes.


""" inits """



critical_section_lock = Lock()


def open_serial(string):
# configure the serial connections (the parameters differs on the device you are connecting to)
    ser = serial.Serial(
	port=string,
        timeout=SERIAL_TIMEOUT, #timeout=SERIAL_TIMEOUT,
	baudrate=230400,
	parity=serial.PARITY_NONE,
	stopbits=serial.STOPBITS_ONE,
	bytesize=serial.EIGHTBITS
    )
    if ser.isOpen():
        ser.close()
    ser.open()
    return ser

class CRC(object):
    def __init__(self,buf):
        self.buf=buf
    def compute(self):
        #update_crc
        #print (len(self.buf))
        #print (binascii.hexlify(self.buf))
        crc_sum=0xFFFF
        #print (crc_sum)
        POLY=0x1021
        for buf_idx in range(len(self.buf)):
            ch=char_to_int(self.buf[buf_idx])
            v = 0x80
            for i in range(8):
                if (crc_sum & 0x8000) != 0:
                    xor_flag = 1
                else:
                    xor_flag = 0
                crc_sum = (crc_sum << 1) & 0xffff

                if (ch & v) != 0:
                    crc_sum = (crc_sum + 1) &0xffff

                if (xor_flag != 0):
                    crc_sum = (crc_sum ^ POLY) & 0xffff

                v = (v >> 1) & 0xffff

        #augment_message_for_crc
        for i in range(16):
            if crc_sum & 0x8000 != 0:
                xor_flag = 1
            else:
                xor_flag = 0
            crc_sum = (crc_sum << 1) & 0xffff
            if xor_flag != 0:
                crc_sum = (crc_sum ^ POLY) & 0xffff
        return BinaryItem("crc_sum", 2, crc_sum)
                    
class RXSerialLoopThread(Thread):

    def __init__(self, zniffer):
        Thread.__init__(self)
        self.zniffer=zniffer

    def run(self):
        dbglog(CRASHDBG, "start thread")
        kb=KBHit()
        while True:
            key=''
            if kb.kbhit():
                key=kb.getch()
                if key=='m'  or key=='M':
                    print 'Memory usage: %s (kb)' % resource.getrusage(resource.RUSAGE_SELF).ru_maxrss
                elif ENABLE_MEM_USAGE_ANALYSIS == True and (key=='c' or key=='C'):
		    import gc
		    import objgraph
                    gc.collect()  # don't care about stuff that would be garbage collected properly
                    print "Garbage collected. Now objgraph says:"
                    objgraph.show_most_common_types()
                print ""

            self.zniffer.rx_log()
            
            dbglog(CRASHDBG, "thread: get queue")
            try:
                message=self.zniffer.msg_queue.get_nowait()
                dbglog(CRASHDBG, "thread: got queue")
                if message==QUEUE_MESSAGE_QUIT:
                    cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_STOP, COMMAND_PAYLOAD_ZERO, 0x49, 0x47] #FIXME 
                    self.zniffer.tx_log_cmd_rx_log(cmd)
                    dbglog(CRASHDBG, "quit thread")
                self.zniffer. msg_queue.task_done() #unblock queue.join
                break
            except Queue.Empty:
                dbglog(CRASHDBG, "thread: no message")
            time.sleep (SLEEP_TIME)
        dbglog(CRASHDBG,"thread loop")



class Zniffer(object):
    def __init__(self, args):
        self.start_date=datetime.datetime.today()
        self.current_date=None 
        self.file_write_counter = 0
        self.change_at_write_counter= INVALID
        self.args=args
        self.msg_queue = Queue.Queue()
        self.serial_handler=open_serial(args.interface)
        self.thread_started=False
        self.zniffer_serial_parser=ZnifferSerialDataParser(PARSEDBG)
        self.rx_serial() #flush whatever existed in uart before properly logging
        return
        
    def get_current_date(self):
        dbglog(CRASHDBG, 'entering')
        now=datetime.datetime.today()
        changed = False
        #if (self.current_date==None) or(now - self.current_date).seconds >10: #only for debug
        if (self.current_date==None) or(now - self.current_date).days >0:
            changed = True
            self.current_date=now
            self.change_at_write_counter=self.file_write_counter #keep trace of write counter when switching date 
        elif self.file_write_counter == self.change_at_write_counter: #as we want to regen all headers (zlf, csv) for this transition
            changed = True

            #dbglog(ENABLE, "switching to date:"+ str(now))
            #must regen header
        dbglog(CRASHDBG, 'exiting')
        return [changed, self.current_date]

    def get_filename_base(self):
        fn_end_idx=len(self.args.output_filename) #full filename
        if self.args.output_filename.endswith(ZLF_FILE_SUFFIX): #already ends with zlf?
            fn_end_idx-=len(ZLF_FILE_SUFFIX)  #remove it...
        
        fn=self.args.output_filename[0:fn_end_idx]
        return fn

    def get_current_filename(self, suffix):

        fn=self.get_filename_base()
        if args.rotate_logs == False:
            if self.file_write_counter == 0: #first time we write, ever?
                changed = True
            else:
                changed = False
            current_filename=fn+suffix
        else:
            [changed, current_date]=self.get_current_date()
            #print(fn)
            current_filename=fn + self.current_date.strftime("_%d%m%y_%H%M%S")+suffix #and reformat it with date
            if changed == True:
                dbglog(ENABLE, "writing to file: "+current_filename)
        return [changed, current_filename]


    def log_raw_data_to_zlf_file(self,raw_data):
        return self.log_raw_data_to_file(raw_data, ZLF_FILE_SUFFIX, self.prepare_zlf_header)

    def log_raw_data_to_csv_file(self,raw_data):
        return self.log_raw_data_to_file(raw_data,CSV_FILE_SUFFIX, self.prepare_csv_header)

    def prepare_csv_header(self):
        dbglog(CRASHDBG, 'entering')
        return ZnifferFrame().get_csv_header()
        
    def log_raw_data_to_file(self, raw_data, extension, header_preparator):
        dbglog(CRASHDBG, 'entering'+ extension)
        if self.args.output_filename ==None:
            return
        [fn_changed, fn]= self.get_current_filename(extension)
        dbglog(CRASHDBG, "fn_changed:"+str(fn_changed))
        if fn_changed == True:
            raw_data=header_preparator()+raw_data
            
        if self.args.rotate_logs == True or self.file_write_counter >0 : #if file exists and if we're asking for rotation
            #appending while asking for rotation won't hurt, there are very few chances that a rotated log filename will collide within the same second...
            mode='a+b' #append, binary (binary is for windows, doesn't hurt under linux/ posix os'es)
        else:
            mode='w+b' #write, seek 0
        dbglog(CRASHDBG, "fn: "+fn)
        f=open(fn, mode)
        f.write(raw_data)
        #file_write_counter is handled by log_all_files()
        f.close()
        dbglog(CRASHDBG, 'exiting')
        return

    def split_data_by_max_sz(data, sz):
        splitted_data=[]
        data_copy=""
        data_copy+=data
        while True:
            cur_data=data_copy[:MAX_SERIAL_DATA_SIZE-1]
            if len(cur_data)==0:
                break
            splitted_data.append(cur_data)
            data_copy=data_copy[MAX_SERIAL_DATA_SIZE:] #nibble, gobble
            
        return splitted_data
    def log_to_zlf_file(self, zniffer_frame, tx): #tx:True rx:False .
        dbglog(CRASHDBG, 'entering')
        zlf_line=self.create_zlf_line(zniffer_frame,tx)
        #for i in range(len(zlf_lines)):
            #for j in range(len(zlf_lines[i])):
            #    print hex(ord(zlf_lines[i][j])),
            #dbglog(ENABLE,"")
        self.log_raw_data_to_zlf_file(zlf_line)
        dbglog(CRASHDBG, 'exiting')
        return
        
    def tx_serial(self,bytes_array):
        dbglog(CRASHDBG, 'entering')
        data=""
        if len(bytes_array) == 0:
            return
        dbgprint(SERIALDBG, "tx("+"%04d"%len(bytes_array)+"):\t raw -> ")
        print_bytes(SERIALDBG,bytes_array,SERIALDBG)

        for i in range(len(bytes_array)):
            b= bytes_array[i]
            #dbglog(ENABLE, "b["+str(i)+"]="+hex(b))
            data+=int_to_bin(b)
            #print (c)
        self.serial_handler.write(data)
        dbglog(CRASHDBG,'exiting')
        return data
    def rx_serial(self):
        dbglog(CRASHDBG, 'entering')
        raw_data=""
        while True:
            b=""
            b =self.serial_handler.read(1)
            if len(raw_data)==1:
                then=datetime.datetime.utcnow()
                dbglog(TIMINGDBG, "start at time "+str(then))
            #print (b)
            if b!="":
                raw_data+=b
            else: #timeout
                if len(raw_data) >0:
                    now=datetime.datetime.utcnow()
                    dbglog(TIMINGDBG, "stop  at time "+str(now)+ " len: "+str(len(raw_data))+" duration "+ str((now-then).total_seconds()*1000))
                break
        if raw_data!="":
            dbgprint(SERIALDBG, "rx("+"%04d"%len(raw_data)+"):\t raw <- ")
            print_bytes(SERIALDBG,bin_to_array(raw_data), SERIALDBG)
            dbgprint(SERIALDBG,"\n")
        dbglog(CRASHDBG,'exiting')

        return raw_data

    def rx_log(self):
        serial_data=self.rx_serial()
        if len(serial_data)==0:
            return
        
        if ENABLE_PARSER == True :
            splitted_frames=self.zniffer_serial_parser.parse(serial_data) #splitted by parsing 
            self.zniffer_serial_parser.clean()
            #dbglog(DATEDBG,str(datetime.utcnow()))
            dbglog(BASEDBG, "<-- parsed "+str(len(splitted_frames))+ " frame(s):")
            if True:
                for i in range(len(splitted_frames)):
                    print_bytes(BASEDBG,bin_to_array(splitted_frames[i].raw_payload),SERIALDBG)
                dbgprint(BASEDBG, "\n")
        else: # splitted by raw size limitation to 256 bytes (will be the unpreferred way of doing it)
            splitted_frames=self.split_data_by_max_sz(serial_data, MAX_SERIAL_DATA_SIZE)
                
        for i in range(len(splitted_frames)):
            self.log_to_all_files(splitted_frames[i],tx=False)


    def log_to_all_files(self,zniffer_frame,tx):
        self.log_to_zlf_file(zniffer_frame, tx=False)
        if args.output_csv == True:
            self.log_raw_data_to_csv_file(zniffer_frame.to_csv())
        self.file_write_counter+=1
    def tx_log_cmd_rx_log(self,cmd):
        dbglog(CRASHDBG,'entering')
        serial_data=self.tx_serial(cmd)
        splitted_frames=self.zniffer_serial_parser.parse(serial_data)
        self.log_to_all_files(splitted_frames[0], tx=True)
        self.rx_log()
        dbglog(CRASHDBG,'exiting')

    def set_region(self):
        dbglog(CRASHDBG, 'entering')
        reg_string="COMMAND_FREQUENCY_CODE_"+args.region.upper()
        statement="freq=%s"%reg_string
        dbglog(CRASHDBG, statement)
        try:
            exec(statement)
        except NameError as e:
            dbglog(ENABLE, "ERROR: region code "+args.region.upper() +" doesn't exist or is not supported")
            dbglog(ENABLE, "exiting...")
            exit(1)
            
            
        #set listening band... 
        #23 05 00 #stop
        sz=COMMAND_PAYLOAD_ZERO
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_STOP, sz]
        self.tx_log_cmd_rx_log(cmd)

        #23 02 01 00 #set to frequency "zero" #fixme: what does freq==0 means?
        sz=COMMAND_PAYLOAD_ONE
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_SET_FREQUENCY, sz, freq]
        self.tx_log_cmd_rx_log(cmd)

        #23 04 00 #start
        sz=COMMAND_PAYLOAD_ZERO
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_START, sz]
        self.tx_log_cmd_rx_log(cmd)
        dbglog(CRASHDBG, 'exiting')
        return

    def init_zniffer(self):
        dbglog(CRASHDBG, 'entering')

        #23 05 00 49 47 #set ... strange
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_STOP, COMMAND_PAYLOAD_ZERO, 0x49, 0x47] #FIXME 
        self.tx_log_cmd_rx_log(cmd)

        #version get (ends with 00)
        sz=COMMAND_PAYLOAD_ZERO
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_GET_VERSION, sz]
        self.tx_log_cmd_rx_log(cmd)

        #23 0e 01 01 #set
        sz=COMMAND_PAYLOAD_ONE
        baud_rate=COMMAND_BAUD_RATE_230400
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_SET_BAUD_RATE, sz, baud_rate]
        self.tx_log_cmd_rx_log(cmd)
    
        #23 03 00 #get frequencies list
        sz=COMMAND_PAYLOAD_ZERO
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_GET_FREQUENCIES, sz]
        self.tx_log_cmd_rx_log(cmd)
        # receivelong string of size 0x18 bytes: first byte after size is current freq code (seems EU==0x00, the rest are available frequencies code.

        #23 13 01 00 #get frequency string for code COMMAND_FREQUENCY_CODE_EU
        sz=COMMAND_PAYLOAD_ONE
        freq=COMMAND_FREQUENCY_CODE_EU
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_GET_FREQUENCIES_STR, sz,COMMAND_FREQUENCY_CODE_EU]
        # fixme: Zniffer doesn't seem to respond to this command!
        self.tx_log_cmd_rx_log(cmd)

        #23 04 00 #start, finally
        sz=COMMAND_PAYLOAD_ZERO
        cmd=[ ZNIFFER_4X_COMMAND_FRAME_SOF, ZNIFFER_4X_COMMAND_TYPE_START, sz]
        self.tx_log_cmd_rx_log(cmd)
        dbglog(CRASHDBG, 'exiting')

    def create_zlf_line(self,zniffer_frame, tx):
        #if a raw serial_data input is bigger than 256 chars, it must be logged among several zlf files
        dbglog(CRASHDBG, 'entering')
        serial_data=zniffer_frame.raw_payload
        if len(serial_data)> MAX_SERIAL_DATA_SIZE:
            raise Exception (" create_zlf_line can only take a serial_data payload of 256 bytes or less. here, it is", len (serial_data), "and it shouldn't happen!")
        zlf_line=""

        csharp_ts= zniffer_frame.zwave_ts.get_csharp_timestamp()
        session=SESSION_NUMBER
        out_session=BinaryItem("outcome_session", 1, session)
        if tx==True:
            out_session.set_value(out_session.get_value()|0x80)

        zlf_line+=csharp_ts.tobin(LITTLE_ENDIAN)
        zlf_line+=out_session.tobin()
        zlf_line+=BinaryItem("zlf_payload_len", 1, len(serial_data)).tobin()
        zlf_line+=BinaryItem("3_empty_bytes_to_get_to_offset_13",3).tobin()
        zlf_line+=serial_data
        eod=BinaryItem("eod_for_zniffer",1, EOD_VALUE - API_TYPE_ZNIFFER)
        zlf_line+=eod.tobin()
        
        dbglog(CRASHDBG, 'exiting')
        return zlf_line

    def get_com_string(self):
        dbglog(CRASHDBG, 'entering')
        string="COM 16 - ver 2.43 @ ZW0500"
        ustr=""
        for i in range(len(string)):
            ustr+=string[i]
            ustr+=BinaryItem("unicode_pad",1).tobin()# damn you stupid unicode
            dbglog(CRASHDBG, 'exiting')
        return ustr

    def prepare_zlf_header(self):
        dbglog(CRASHDBG, 'entering')

        """ from ZnifferSources/Libraries/ZWaveDll/ZWave/StoraegHeader.cs:"
        /// Storage Header 
        /// | 4 bytes header Version | 4 bytes EncodingCode | 512 bytes CommentText |
        /// | 1 byte APItype | 1 byte Number of Sessions | 1 byte SessionId | 1 byte COM name length | N bytes COM name
        /// | 1 byte number of Freq | | 1 byte Freq channels 1 byte Freq code | 1 byte Freq name length | N bytes Freq name
        """
        STORAGE_VERSION=100 #as seen from zniffer traces from sdk 6.51.06
        header_version=BinaryItem("storage_header", 4, STORAGE_VERSION )
        encoding_code=BinaryItem("encoding_code",4)
        comment_text=BinaryItem("comment_text", 512, pad_trail_zeros("", 512))
        api_type=BinaryItem("api_type", 1, API_TYPE_ZNIFFER) #zniffer api
        number_of_sessions=BinaryItem("",1, 1)
        session_id=BinaryItem("",1, number_of_sessions.get_value()-1)
        

        h=""
        h+=header_version.tobin(LITTLE_ENDIAN)
        h+=encoding_code.tobin()
        h+=comment_text.tobin()
        h+=api_type.tobin()
        h+=number_of_sessions.tobin()
        h+=session_id.tobin()
        #print (h)
        com_string=self.get_com_string()
        com_string_sz=BinaryItem("com_string_size",1, len(com_string))
        h+=com_string_sz.tobin()
        h+=com_string
        #print (h)
        #now, the padding
        h=pad_trail_zeros(h, HEADER_SIZE -2) #2 bytes for crc16
        crc=CRC(h)
        h+=crc.compute().tobin(LITTLE_ENDIAN)
        dbglog(CRASHDBG, 'exiting')
        return h

    def Start(self):
        self.init_zniffer()
        self.set_region()
        
        self.rx_serial_t=RXSerialLoopThread(self)
        #self.rx_serial_t.daemon=True
        with critical_section_lock:
            self.rx_serial_t.start()
        self.thread_started=True
        return self.rx_serial_t

    def signal_handler(self,signal, frame):
        dbglog(CRASHDBG,'got signal '+str(signal))
        dbglog(ENABLE, "break, quitting...")
        if self.thread_started == True and self.rx_serial_t.isAlive():
            self.msg_queue.put(QUEUE_MESSAGE_QUIT)
            self.msg_queue.join()
        
        dbglog(CRASHDBG,'exiting')
        sys.exit(0)



parser = argparse.ArgumentParser()
parser.add_argument('-i', '--interface',\
                  help="select zniffer uart interface", required=True)

parser.add_argument('-o', '--output_filename',\
                    help="select output filename ("+ZLF_FILE_SUFFIX+" is automatically added)", required=True)

parser.add_argument('-r', '--rotate_logs',\
                    help="enable log rotation day by day", action='store_true', default=False)

parser.add_argument('--region',\
                    help="choose region among [EU] US ANZ HK MY IN RU IL", default="EU")

parser.add_argument('--output_csv',\
                    help="also output traffic to matching csv file", action='store_true', default=False)

parser.add_argument('-v', '--verbose_level',\
                    help="enable verbose output. max debug ->100, no debug -> -1", type=int, default=0)

#parser.add_argument('-m', '--mode', action='store_true') # or action='store_false')

args = parser.parse_args()
if args.verbose_level >=100:
    CRASHDBG=ENABLE

if args.verbose_level >=10:
    PARSEDBG=ENABLE

if args.verbose_level >=2:
    SERIALDBG=ENABLE
if args.verbose_level >=1:
    TIMINGDBG=ENABLE
if args.verbose_level >=0:
    BASEDBG=ENABLE



#help is implicitly created by argparse! :)

z= Zniffer(args)
signal.signal(signal.SIGINT, z.signal_handler)

z.Start()

#keep awake in main loop
while True:
    time.sleep(SLEEP_TIME)

#ctrl -c led us here
z.rx_serial_t.join()

#and quit
