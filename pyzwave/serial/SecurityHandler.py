'''
Created on Feb 24, 2012

@author: aes
'''
import unittest
from ZWBasicNode import *
from random import randint
from time import time
from Crypto.Cipher import AES
import logging
from pyzwave.ZW_classcmd import SECURITY_NONCE_GET
from threading import Timer
'''
Implementation of the Security command class
'''
class SecuritySate:
    LEARN_START = 1
    SCHEME_REPORT_SENT = 2
    KEY_VERIFY_SENT = 3
    SCHEME_REPORT_SENT2 = 4
    LEARN_COMPLETE =5
    LEARN_FAIL = 6
    
    ADD_START = 7
    SCHEME_GET_SENT = 8
    KEY_SENT = 9
    SCHEME_INHERIT_SENT = 10
    ADD_DONE =11

    
class NoSavedKeyError(StandardError):
    pass

class SecurityHandler(AppHandler):
    zwclass = COMMAND_CLASS_SECURITY    

    TEST_SCHEME_REPORT_DELAYED     =1
    TEST_KEY_VERIFY_DELAYED        =2
    TEST_SCHEME_REPORT2_DELAYED    =3
    TEST_NONCE_REPORT_DELAYED      =4
    TEST_ENCMSG_DELAYED            =5
    TEST_ENCMSG2_DELAYED           =6
    TEST_BAD_KEY_VERIFY            =7
    TEST_BAD_MAC                   =8
    TEST_NONCE_REPORT_DELAYED2     =9
    TEST_BAD_MAC2                  =10
    TEST_NONCE_REUSE               =11
    TEST_NONCE_REUSE_NO_GET        =12
    
    def restore_key_from_file(self):
        #TODO: Implement this
        raise NoSavedKeyError
    
    def save_key_to_file(self):
        # TODO: Implement this
        pass

    def setup(self):
        global sec_id
        
        sec_id = randint(0,16)
        
        self.noncetable = []
        self.sessions = []
        self.sec_id = dict()        
        print 'SecurityHandler setup'
        try:
            key = self.restore_key_from_file()
        except NoSavedKeyError:
            pass
        if self.z.is_controller and self.z.nodeid == 1:
            key=""        
            for i in range(0,16):            
                key+= chr(randint(0,255))
        else:
            key = chr(0) * 16;
        self.load_key(key);
        self.my_netkey = key

        self.state = SecuritySate.LEARN_START
        self.first_part = None
        self.appHandlers = dict()
        self.testmode = 0
        self.testmode_disable_secure_inclusion = False
        self.testmode_disable_security = False
        self.testmode_ignore_nonce_get = False
        self.tt = None
        
    def __del__(self):
        if(self.tt!=None):
            self.tt.cancel()
            self.tt.join()

    def post_add_hook(self,status,node):
        print "securityhandler post_add_hook"
        if self.testmode_disable_secure_inclusion:
            return
        self.state = SecuritySate.ADD_START
        self.adding_nodeid = node        
        self.z.ZW_SendData(node,pack("3B",
                     COMMAND_CLASS_SECURITY,
                     SECURITY_SCHEME_GET,0), 0x1,
                   lambda txstatus,data: self.new_state_on_tx_ok(SecuritySate.SCHEME_GET_SENT,txstatus));
        
    def post_learn_hook(self,status):
        if self.testmode_disable_secure_inclusion:
            return

        self.load_key(chr(0)*16);
        self.state = SecuritySate.LEARN_START        
        print 'post_learn'
        logging.debug("Secure learn_mode")

    def isController(self,node):
        #inf = self.z.ZW_GetNodeProtocolInfo(node);
        #TODO make it right
        return True
        
    def new_state_on_tx_ok(self,new_state,status):
        if status == TRANSMIT_COMPLETE_OK:
            self.state = new_state
        else:
            SecuritySate.LEARN_FAIL;
            
        if(new_state==SecuritySate.LEARN_COMPLETE):
            self.send_commands_supported_get(self.source)
       
    def send_sec_scheme_report(self,node):
        self.send_secure_message(node,0x1,pack("3B",COMMAND_CLASS_SECURITY,SECURITY_SCHEME_REPORT,0), 
                                 lambda txstatus: self.new_state_on_tx_ok(SecuritySate.LEARN_COMPLETE,txstatus) )        

    def send_key_verify(self,node):
            self.send_secure_message(node,0x1,pack("2B",COMMAND_CLASS_SECURITY,NETWORK_KEY_VERIFY),
                                 lambda txstatus: self.new_state_on_tx_ok(SecuritySate.KEY_VERIFY_SENT,txstatus))

    def send_scheme_report(self,node):
        self.z.ZW_SendData(node,pack("3B",
                             COMMAND_CLASS_SECURITY,
                             SECURITY_SCHEME_REPORT,0), 0x1,
                           lambda txstatus,data: self.new_state_on_tx_ok(SecuritySate.SCHEME_REPORT_SENT,txstatus));

    def send_commands_supported_get(self,node):
        self.send_secure_message(node,0x1,pack("2B",COMMAND_CLASS_SECURITY,SECURITY_COMMANDS_SUPPORTED_GET),
                                 None)
                
    def send_nonce_report(self,node):
        self.z.ZW_SendData(node,pack("2B",
                 COMMAND_CLASS_SECURITY,
                 SECURITY_NONCE_REPORT) + self.new_nonce(node), 0x1,0);

    def on_key_sent(self,status):
        self.load_key(self.my_netkey)
        self.state = SecuritySate.KEY_SENT;
        
    def handler(self, rxStatus,source,cmd, data):
        self.source = source
        if self.testmode_disable_security: return
        if(cmd == SECURITY_SCHEME_GET and self.state == SecuritySate.LEARN_START):
            logging.debug("SECURITY_SCHEME_GET -> SECURITY_SCHEME_REPORT")
            if(self.testmode == self.TEST_SCHEME_REPORT_DELAYED):
                self.tt = Timer(15.0, self.send_scheme_report,[source]).start()
            else:
                self.send_scheme_report(source)
        elif(cmd == SECURITY_NONCE_GET):
            logging.debug("SECURITY_NONCE_GET -> SECURITY_NONCE_REPORT")
            if self.testmode_ignore_nonce_get: return            
            if(self.testmode == self.TEST_NONCE_REPORT_DELAYED):
                self.tt =Timer(self.nonce_report_delay, self.send_nonce_report,[source]).start()
            else:
                self.send_nonce_report(source)
        elif(cmd == SECURITY_SCHEME_REPORT and self.state==SecuritySate.SCHEME_GET_SENT and source == self.adding_nodeid):           
            self.load_key(chr(0)*16)            
            self.send_secure_message(source,0x1,pack("2B",COMMAND_CLASS_SECURITY,NETWORK_KEY_SET) + self.my_netkey,self.on_key_sent)
            pass
        elif(cmd == SECURITY_SCHEME_INHERIT and  self.state ==SecuritySate.KEY_VERIFY_SENT ):
            if(self.testmode == self.TEST_SCHEME_REPORT2_DELAYED):
                self.tt =Timer(25.0, self.send_sec_scheme_report,[source]).start()
            else:
                self.send_sec_scheme_report(source)
        elif(cmd == SECURITY_NONCE_REPORT):
            for s in self.sessions:
                if(s.dst == source):
                    if(self.testmode == self.TEST_ENCMSG_DELAYED and s.state == SecuritySession.S_NONCE_GET_SENT):
                        s.timer.cancel()
                        self.tt =Timer(15.0, s.register_nonce,[source,data]).start()
                    elif(self.testmode == self.TEST_ENCMSG2_DELAYED and s.state == SecuritySession.S_ENC_MSG1_SENT):
                        s.timer.cancel()
                        self.tt =Timer(15.0, s.register_nonce,[source,data]).start()
                    else:
                        s.register_nonce(source,data)
                        return   
            logging.warning("Unable to match session to nonce")
                                             
        elif(cmd == NETWORK_KEY_SET and self.state == SecuritySate.SCHEME_REPORT_SENT):
            logging.debug("NETWORK_KEY_SET -> NETWORK_KEY_VERIFY")
            
            if(self.testmode == self.TEST_BAD_KEY_VERIFY):
                self.load_key(chr(0xFF)*16)
            else:
                self.load_key(data[0:16])

            self.my_netkey = data[0:16]
                
            if(self.testmode == self.TEST_KEY_VERIFY_DELAYED):
                self.tt =Timer(15.0, self.send_key_verify,[source]).start()
            else:
                self.send_key_verify(source)
            pass        
        elif(cmd == NETWORK_KEY_VERIFY and self.state == SecuritySate.KEY_SENT and self.adding_nodeid == source):            
            if(self.isController(source)):
                self.send_secure_message(source,0x1,pack("3B",COMMAND_CLASS_SECURITY,SECURITY_SCHEME_INHERIT,0),
                                         lambda txstatus: self.new_state_on_tx_ok(SecuritySate.ADD_DONE,txstatus))
            else:
                self.state = SecuritySate.ADD_DONE
            
            pass                
        elif(cmd == SECURITY_COMMANDS_SUPPORTED_GET):            
            s=""
            for a in self.appHandlers.keys():
                s+=chr(a)
            self.send_secure_message(source,0x1,pack("3B",COMMAND_CLASS_SECURITY,SECURITY_COMMANDS_SUPPORTED_REPORT,0)+s)
            pass
        elif(cmd == SECURITY_COMMANDS_SUPPORTED_REPORT):            
            pass                        
        elif( (cmd & 0x81) == SECURITY_MESSAGE_ENCAPSULATION):
            logging.debug("<- SECURITY_MESSAGE_ENCAPSULATION")
            self.decrypt_message(rxStatus, cmd,source,data)
            
            if(cmd == SECURITY_MESSAGE_ENCAPSULATION_NONCE_GET):
                if(self.testmode == self.TEST_NONCE_REPORT_DELAYED2):
                    self.tt =Timer(15.0, self.send_nonce_report,[source]).start()
                else:
                    self.send_nonce_report(source)
            pass
        
    def new_nonce(self,nodeid=None):
        nonce=""        
        while True:
            for i in range(0,8):            
                nonce+= chr(randint(0,255))
            if( nonce[0] != chr(0) ):
                break

        if(nodeid != None):
            self.noncetable.append( (nodeid,time()+10,nonce) )
        
        return nonce
        
    def get_nonce(self,ri,nodeid):
        #Remove timedout elements        
        self.noncetable = filter( lambda x: time()< x[1]   ,self.noncetable )
        for (node,t,nonce) in self.noncetable:
            if(node == nodeid):
                if(nonce[0]==ri):
                    self.noncetable = filter( lambda x: x[0]!=nodeid  ,self.noncetable ) 
                    return nonce
        return None
    
    def load_key(self,netkey):
        print "Current key ", netkey.encode("hex")
        aes = AES.new(netkey, AES.MODE_ECB)
        self.auth_key = aes.encrypt(chr(0x55)*16)
        aes = AES.new(netkey, AES.MODE_ECB)
        self.enc_key = aes.encrypt(chr(0xAA)*16)

    def SecAppHandler(self,rxOptions,source,data):
        handled=False
        try:            
            self.appHandlers[ord(data[0])](data)
            handled =True;
        except KeyError:
            None
            
        if(not handled):
            self.z.ApplicationCommandHandler(rxOptions | 0x100, source, len(data), data)
                
    def decrypt_message(self,rxOptions,sh,source,data):
        iv1 = data[0:8]
        enc_data = data[8:-9]
        ri = data[-9:-8]
        mac = data[-8:]

        iv2 = self.get_nonce(ri,source)
        if(iv2==None):
            print "Nonce not found"
            return

        auth_data =iv1 + iv2 +pack("4B",sh,source,self.z.nodeid,len(enc_data)) + enc_data
             
        olen = len(enc_data)
        pad = 16 - (olen % 16)
        enc_data+=chr(0)*pad

        pad = 16 - (len(auth_data) % 16)
        auth_data+=chr(0)*pad        
                
        IV = chr(0) * 16
        a = AES.new(self.auth_key,AES.MODE_CBC, IV)
        mac1 = a.encrypt(auth_data)[-16:-8]

        #print 'Initialization vector'
        #print 'received mac:', str(mac).encode('hex')
        #print 'calculated mac:', str(mac1).encode('hex')
        if(mac1 == mac):
            d = AES.new(self.enc_key,AES.MODE_OFB,iv1+iv2)
            dec = d.decrypt(enc_data)
            flags = ord(dec[0])
            if(flags == 0):    
                self.SecAppHandler(rxOptions, source, dec[1:olen])
            elif(flags & SECURITY_MESSAGE_ENCAPSULATION_PROPERTIES1_SEQUENCED_BIT_MASK):
                if(flags & SECURITY_MESSAGE_ENCAPSULATION_PROPERTIES1_SECOND_FRAME_BIT_MASK and (self.first_part !=None) and( self.sec_id[source] == flags & 0xF)):
                    self.SecAppHandler(rxOptions, source, self.first_part + dec[1:olen])
                    self.first_part = None
                else: #first part
                    self.sec_id[source] = flags & 0xF
                    self.first_part = dec[1:olen]
        else:
            logging.error("Unable to verify authentication tag")
        

    def send_secure_message(self,*param):
        s = SecuritySession(self,*param)
        
        self.sessions = filter(lambda x: x.state !=SecuritySession.S_DONE, self.sessions )
        self.sessions.append(s)
        

class SecuritySession:
    S_NONCE_GET = 1
    S_NONCE_GET_SENT = 2
    S_ENC_MSG1 = 3
    S_ENC_MSG1_SENT =4
    S_ENC_MSG2 = 5
    S_ENC_MSG2_SENT =6
    S_DONE =5
    
    def __init__(self,handler,dst,txopt,pkt,cb=None):        
        self.s = handler
        self.timer = None
        self.sec_id = 0
        self.state = self.S_NONCE_GET
        self.txopt = txopt
        self.dst = dst
        self.pkt = pkt
        self.cb = cb
        self.multi_fragment = (len(self.pkt) > (46-20))
        self.fistFragment = True
        if self.s.testmode == SecurityHandler.TEST_NONCE_REUSE_NO_GET and self.s.reuse_nonce != None: 
            self.noce_get_sent(TRANSMIT_COMPLETE_OK, None)
            self.register_nonce(dst, self.s.reuse_nonce)
        else:
            self.s.z.ZW_SendData(dst, pack("2B",COMMAND_CLASS_SECURITY,SECURITY_NONCE_GET), txopt, self.noce_get_sent)


    def endfail(self):
#        print "End fail"
        self.state = self.S_DONE
        if(self.cb != None):
            self.cb(TRANSMIT_COMPLETE_FAIL)
            
    def noce_get_sent(self,status,data):
        if(status == TRANSMIT_COMPLETE_OK):
            self.state = self.S_NONCE_GET_SENT
            
            self.timer = threading.Timer(20, self.endfail)            
            self.timer.start()
        else:
            self.endfail()
            
    #Register the nonce this is where the security package is genrated 
    def register_nonce(self,node,nonce):
        global sec_id
        
        if(self.dst != node):
            logging.warning("Nonce report from node %i I'm expecting %i" (node,self.dst))
            return
                
        if(self.state==self.S_NONCE_GET_SENT):
            self.state = self.S_ENC_MSG1
        elif(self.state == self.S_ENC_MSG1_SENT):
            self.state = self.S_ENC_MSG2
        else:
            return
        
        if(self.timer!=None):
            self.timer.cancel()
        
        mynonce = self.s.new_nonce()

        datalen = len(self.pkt)
        flags =0
        #/* Check if we should fragment this message */
        if( self.multi_fragment ):
            logging.debug("-> SECURITY_MESSAGE_ENCAPSULATION")
            sh = SECURITY_MESSAGE_ENCAPSULATION
            if(len(self.pkt) +20 <46 ):
                flags = SECURITY_MESSAGE_ENCAPSULATION_PROPERTIES1_SEQUENCED_BIT_MASK | SECURITY_MESSAGE_ENCAPSULATION_PROPERTIES1_SECOND_FRAME_BIT_MASK | (self.sec_id & 0xF)
            else:
                sec_id = sec_id +1
                self.sec_id = sec_id
                datalen = 46-20;
                sh = SECURITY_MESSAGE_ENCAPSULATION_NONCE_GET
                flags = SECURITY_MESSAGE_ENCAPSULATION_PROPERTIES1_SEQUENCED_BIT_MASK | (self.sec_id & 0xF);
                logging.debug("-> SECURITY_MESSAGE_ENCAPSULATION_NONCE_GET")
        else:
            sh = SECURITY_MESSAGE_ENCAPSULATION
            logging.debug("-> SECURITY_MESSAGE_ENCAPSULATION")
            
        if self.s.testmode == SecurityHandler.TEST_NONCE_REUSE or self.s.testmode == SecurityHandler.TEST_NONCE_REUSE_NO_GET:
            if self.s.reuse_nonce != None:
                print 'Reusing nonce'
                nonce = self.s.reuse_nonce
                self.s.reuse_nonce = None
            else:
                print 'Saving this nonce for reuse'
                self.s.reuse_nonce = nonce
        iv = mynonce + nonce 
        sender = self.s.z.nodeid        

        #Padding         
        enc_data = chr(flags) + self.pkt[:datalen]
        self.pkt = self.pkt[datalen:]
        
        pad = 16 - (len(enc_data) % 16)
        enc_data+=chr(0)*pad        
        d = AES.new(self.s.enc_key,AES.MODE_OFB,iv)
        enc_data = d.encrypt(enc_data)[0:-pad]

        auth_data =iv +pack("4B",sh,sender,node,len(enc_data)) + enc_data
                
        pad = 16 - (len(auth_data) % 16)
        auth_data+=chr(0)*pad        
        IV = chr(0)*16
        a = AES.new(self.s.auth_key,AES.MODE_CBC, IV)
        
        if(self.s.testmode == SecurityHandler.TEST_BAD_MAC):
            mac1 = chr(0)*8
        elif(self.s.testmode == SecurityHandler.TEST_BAD_MAC2):
            mac1=a.encrypt(auth_data)[-16:-8]
            mac1 = chr(ord(mac1[0]) ^ 0x80) + mac1[1:7] # flip a bit in MAC
            
        else:
            mac1 = a.encrypt(auth_data)[-16:-8]
                
        self.s.z.ZW_SendData(self.dst,
                        pack("2B",COMMAND_CLASS_SECURITY,sh) + mynonce + enc_data + nonce[0] + mac1
                        , self.txopt, self.enc_sent)


    def enc_sent(self,status,data):       
        if(status == TRANSMIT_COMPLETE_OK):
            if(self.multi_fragment and self.state==self.S_ENC_MSG1):
                self.state = self.S_ENC_MSG1_SENT
                self.timer = threading.Timer(10, self.endfail)
                self.timer.start()
                return
            else:
                self.state = self.S_DONE
        else:
            self.state = self.S_DONE
            
        if(self.cb!=None):
            self.cb(status)
            
    
    def __del__(self):
        if(self.timer!=None):
            self.timer.cancel()
            self.timer.join()
        
    
