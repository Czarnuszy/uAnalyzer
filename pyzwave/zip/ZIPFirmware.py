'''
Created on Jun 11, 2013

@author: aes
'''
from pyzwave.zip import ZIPDiscover
from pyzwave.ZW_classcmd import *
import pyzwave.zip.ZIPClient
import threading
from time import time
from struct import pack,unpack
from collections import namedtuple
import logging
import os

FIRMWARE_UPDATE_MD_PREPARE_GET=0x08
FIRMWARE_UPDATE_MD_PREPARE_REPORT=0x09

INVALID_COMBINATION = 0x00
OUT_OF_BAND_AUTH_REQUIRED = 0x01
EXCEEDS_MAX_FRAGMENT_SIZE = 0x02
FIRMWARE_NOT_UPGRADABALE = 0x03
LESS_THAN_MIN_FRAGMENT_SIZE = 0x04
FIRMWARE_UPDATE_IN_PROGESS = 0x05
INITIATE_FWUPDATE = 0xFF

logger = logging.getLogger('ZIPFirmware')
logger.setLevel(logging.DEBUG)

'Waiter object used for waiting on zwave packages'
class Waiter():
    def __init__(self):
        # A reference to the test case must be supplied explicitly in the initializer
        # or through the global config module.
        self.ev = threading.Event()        
    
    def f(self, *args, **kwargs):
        # Find the keyword argument 'data'. Otherwise use the last positional arg.
        if 'data' in kwargs: data=kwargs['data']
        elif len(args) > 0: data = args[-1]
        else: data = None
        self.lastpkt = data
        self.ev.set()
    
    def tostr(self):
        return self.lastpkt.encode("hex")
    
    def __str__(self):
        return self.lastpkt.encode("hex")
    
    def wait(self,timeout=60):
        ''' Wait for callback to be called, raise exception or return False on timeout '''
        t1 = time()
        
        self.ev.wait(timeout)
        self.ev.clear()                
        if( (time()-t1) >=timeout ):
            raise Exception("No reply")
        else:
            return True

class ZW_Firmware(pyzwave.zip.ZIPClient.ZIPClient):
    IDLE=1
    GET_SENT = 2 
    REPLYING = 3
    
    GET_IMAGE =4

    
    def __init__(self,node):
        pyzwave.zip.ZIPClient.ZIPClient.__init__(self,node)
        
        self.registerHandler( COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_GET, self.send_report)
        self.registerHandler( COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_STATUS_REPORT, self.got_status)
        self.registerHandler( COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_PREPARE_REPORT, self.prepare_report)        
        self.registerHandler( COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_REPORT, self.got_report)
        
        
        self.fu_state = self.IDLE;
        self.updateProcess = threading.Event()
        self.timer = None
        self.data=""
        self.send_timer = None
        self.mylock = threading.Lock() 
    
    def stop_with_msg(self,msg):
        self.status_txt = msg;
        self.fu_state = self.IDLE
        self.updateProcess.set()
        
    def got_report(self,data):
        self.mylock.acquire()
        if(self.fu_state != self.GET_IMAGE):
            logger.debug("Got unexpect report")
            return
         
        (_,_, reportnumber) = unpack("!2BH",data[:4])
        fragment = data[4:-2]
        (crc,) = unpack("!H",data[-2:])
        last = (reportnumber & 0x8000)>0
        reportnumber = reportnumber & 0x7FFF;

        logger.debug("Got report %i len =%i\n" % (reportnumber,len(fragment)) )
        crc_verify = self.crc16(data[:-2])
        
        if (crc_verify != crc):
            self.stop_with_msg("Checksum error in report %i expected %x got %x\n"  % (reportnumber,crc_verify,crc))
            
        if(reportnumber == self.report_number):
            self.data = self.data + fragment
            if(last):
                self.stop_with_msg("Last report received %i\n"  % reportnumber)
            else:
                self.report_number = self.report_number + 1
                if(self.report_number > self.last_report):
                    self.request_data()
        elif(reportnumber > self.report_number):
            #TODO instead of bailing out we could just request the missing reports.
            self.stop_with_msg("Recived unexpected report %i\n"  % reportnumber)
        
        self.mylock.release();

            
    def request_data(self,num = 3):
        '''
        Request a number of firmware fragments by sending firmware update md get
        '''

        if(self.timer):
            self.timer.cancel()

        self.last_report = self.report_number+num-1;
        logger.debug("requesting report %i to %i inclusive\n" % (self.report_number,self.last_report))
        self.sendData(pack("!3BH",
                           COMMAND_CLASS_FIRMWARE_UPDATE_MD,
                           FIRMWARE_UPDATE_MD_GET, 
                           num,
                           self.report_number))

        
        
            
        self.timer = threading.Timer(20.0,self.timeout )
        self.timer.start()
        
    def prepare_report(self,data):
        (_,_,_,status) = unpack("2BHB",data[:5])
        
        if(status == INITIATE_FWUPDATE):
            self.data=""
            self.report_number = 0
            self.request_data()
            
        else:
            self.stop_with_msg("Device is not ready for firmware update %i" % status);
    
    def get_image(self,fw_id,target_id):
        ''' Retrieve a firmware image
        '''
        if(self.fu_state == self.IDLE):
            self.fu_state = self.GET_IMAGE
            self.sendData(pack("!2B2H",COMMAND_CLASS_FIRMWARE_UPDATE_MD,FIRMWARE_UPDATE_MD_PREPARE_GET,fw_id,target_id), max_time= 1.0)
    
        
    def md_get(self):
        '''
        The Firmware Meta Data Get Command is used to request information on the current firmware in the device. 
        This call is typically used to check whether or not the firmware needs to be updated.
        '''        
        w = Waiter()        
        self.registerHandler( COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_MD_REPORT, w.f)
        self.sendData(pack("2B",COMMAND_CLASS_FIRMWARE_UPDATE_MD,FIRMWARE_MD_GET), max_time= 1.0)
        w.wait(1)
                
        Info = namedtuple('Info', 'manufacturer_id firmware_id frim0_crc mod_upgarde num_targets max_fragsize')
        if(len(w.lastpkt)<12):
            self.info = Info._make(unpack( "!3H2BH", w.lastpkt[2:8]+"\000"*2 +"\000\050" ))            #Set num targets to 0 and max_fragsize to 40 bytes
        else:
            self.info = Info._make(unpack( "!3H2BH", w.lastpkt[2:12] ))
        self.targets=[0]
        for i in range(0,self.info.num_targets) :
            self.targets.append( unpack("!H",w.lastpkt[12+i*2:12+i*2+2])[0] )
        return (self.info,self.targets) 
        
    def md_update_request_get(self,target,fid,filename,progress_handler=None):
        '''
        The Firmware Update Meta Data Request Get Command is used to request that
        a firmware update is initiated. The firmware update MUST NOT be initiated 
        if the Manufacturer ID and the Firmware ID do not match the actual firmware image values.
        The firmware update MUST be aborted if the checksum does not match the calculated checksum after
        the firmware image has been transferred'''
        w = Waiter()
        self.fu_state = self.GET_SENT
        self.progress_handler = progress_handler
        
        self.updateProcess.clear()
        self.total_fragments = os.path.getsize(filename) / self.info.max_fragsize
        logger.debug("Total number of fragments is %d" % (self.total_fragments))

        self.file = open(filename,"rb");
        chk = self.crc16( self.file.read() )
        self.file.seek(0,0)
        self.registerHandler( COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_REQUEST_REPORT, w.f)        
        self.sendData(pack("!2B3HBH",COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_REQUEST_GET,
                       self.info.manufacturer_id,fid,chk,target,self.info.max_fragsize), max_time=2.0)
        
        w.wait(1)                
        
        rc = ord(w.lastpkt[2])
        if(rc == 0 ):
            raise Exception("DENIED. Invalid combination of Manufacturer ID and Firmware ID.")
        elif(rc == 1):
            raise Exception("DENIED. Device expected an out of band authentication event to enable firmware update.")
        elif(rc == 2):
            raise Exception("DENIED. The requested Fragment Size exceeds the Max Fragment Size.")
        elif(rc == 3):
            raise Exception("DENIED. This firmware target is not upgradable.")
        elif(rc == 0xFF):
            if(self.fu_state == self.GET_SENT):
                self.timer = threading.Timer(20.0,self.timeout )
                self.timer.start()
            return
        else:
            raise Exception("Unknown status")
    
    def timeout(self):
        if(hasattr(self,"file") and self.file):
            self.file.close()
            
        if(self.send_timer):
            self.send_timer.cancel()        
        logger.warning("timeout")
        self.stop_with_msg("Timeout")

    def got_status(self,data):
        logger.debug("Got status")
        self.file.close()
        status = ord(data[2])
        #print "done"
        if(self.timer):
            self.timer.cancel();
        if(self.send_timer):
            self.send_timer.cancel()        

        if(status==0):
            self.stop_with_msg("The device was unable to receive the requested firmware data without checksum error")
        elif(status==1):
            self.stop_with_msg("The device was unable to receive the requested firmware data.")
        elif(status==0xFF):
            self.stop_with_msg("New firmware was successfully stored in temporary non volatile memory.")
    
    report_number = 0

    def do_send_report(self):
        import pdb
        '''
        Worker function which actually sends the data. Each block is spaced a little in time 
        '''            
        if(self.updateProcess.is_set()):
            logger.debug("Not sending report because we are done")
            return

        if(self.report_number >= self.last_report):
            logger.debug("inconsistent report number")
            pdb.set_trace()
            return
        #TODO            
        self.file.seek(self.info.max_fragsize*(self.report_number-1),0)
        payload = self.file.read(self.info.max_fragsize)
        
        if(len(payload) < self.info.max_fragsize):
            last = True
        else:
            last = False

        if(last):
            self.report_number |= 0x8000

        logger.debug("Sending report %i of %i" % (self.report_number,self.last_report))
        frame = pack("!2BH",COMMAND_CLASS_FIRMWARE_UPDATE_MD, FIRMWARE_UPDATE_MD_REPORT,self.report_number) + payload                              
        crc =self.crc16(frame)
        frame = frame +pack("!H",crc)

        try:
            self.sendData(frame , max_time=60.0)
        except pyzwave.zip.ZIPClient2.TimeoutError:
            logger.error("Transmit timeout error")
            self.timeout()

        if(self.progress_handler):
            self.progress_handler(self.report_number,self.total_fragments)

        self.report_number = (self.report_number + 1) & 0x7FFF            
            
        #Check if the update process has completed        

        if(self.timer):
            self.timer.cancel()
            
        if (self.updateProcess.is_set()):
            logger.debug("We are no longer updating")
            return
        elif(last):
            self.timer = threading.Timer(70.0,self.timeout )
            self.timer.start()
            logger.debug("Image transfered")
        elif(self.report_number < self.last_report):
            logger.debug("more reports to send %i ",self.report_number)
            self.send_timer = threading.Timer(0.05,self.do_send_report )
            self.send_timer.start()            
        else:
            logger.debug("last report sent %i ",self.report_number)            
            self.fu_state = self.GET_SENT
            self.timer = threading.Timer(70.0,self.timeout ) # TODO: Should be 10*7 - needs abort button first            
            self.timer.start()        
                    
                    
    
    def send_report(self,data):        
        '''
        The Firmware Update Meta Data Report command is used to
        transfer one firmware image fragment. The Firmware Update Meta Data Report command(s)
        can only be sent in response to a Firmware Update Meta Data Get command.
        A node SHOULD request missing reports individually, i.e. one at a time.
        '''
        logger.debug("Got get")
        self.mylock.acquire()
        if(self.fu_state == self.IDLE):
            logger.warning("wrong state %d ignoring request " %(self.fu_state))
            return

        _,_,number_of_reports,self.report_number = unpack("!3BH",data)
        
        self.report_number = self.report_number & 0x7FFF
        if( self.report_number<1 ):
            logger.error("Invalid report number")
            return;            

        self.last_report = self.report_number + number_of_reports
        
        logger.debug("Request for report " +str(self.report_number) + "to" + str(self.last_report))
        self.fu_state = self.REPLYING

        if(self.timer):               
            self.timer.cancel()
        self.timer = None

        self.do_send_report()
        
                        
        self.mylock.release()
            
    def crc16(self,data):
        POLY = 0x1021
        CRC_INIT_VALUE  =  0x1D0F
        crc = CRC_INIT_VALUE
        for c in data:
            bitmask=0x80;
            while(bitmask!=0):            
                newbit = (( ord(c) & bitmask ) != 0) ^ ((crc & 0x8000) !=0)
                crc = (crc << 1) & 0xFFFF
                if(newbit):
                    crc = crc ^ POLY
                    
                bitmask = (bitmask >> 1) & 0xff
        return crc;


    def wait_for_update_process(self):
        '''
        Wait for the initiated update process to complete
        '''
        self.updateProcess.wait()
        return self.status_txt


        
if __name__ == '__main__':
    import sys

    logger.setLevel(level=logging.DEBUG)

    #zips = ZIPDiscover.get_all_services();
    #host = zips[0]
    #host = sys.argv[1]
    host = "192.168.1.160"
    f = ZW_Firmware(host)
    f.md_get()
    
    ZW_FW_ID = 0
    ZIPR_FW_ID=1
    CERT_FW_ID=2
    DEFAULT_CONFIG_ID = 3
    
    ZW_FW_TARGET_ID = 0
    ZIPR_FW_TARGET_ID=1
    CERTS_TARGET_ID=2
    DEFAULT_CONFIG_TARGET_ID = 3

    ZW_FW_EEPROM_TARGET_ID=4
    
#     rc = f.md_update_request_get(target=int(sys.argv[2]),fid=int(sys.argv[3]) , filename=sys.argv[4])
#     f.wait_for_update_process();
#     f.stop()
    f.get_image(ZW_FW_TARGET_ID, ZW_FW_EEPROM_TARGET_ID)
    f.wait_for_update_process();
    f.stop()
    print f.status_txt
    print "Data len is %x" % len(f.data)
    pass
