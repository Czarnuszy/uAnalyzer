'''
Created on Jun 20, 2011

@author: aes
'''

from pyzwave.ZW_classcmd import *
from struct import *
import logging
import socket
import array
import threading
import thread
import time
import random
import sys, os
import dtls_client
from ipaddr import IPAddress, IPv6Address, IPv4Address
class TimeoutError(Exception): 
    pass 

import ssl
import socket,time

from dtls import do_patch

'''
TLV iterator which returns the tlv as a tuple.
If the TLV parsing fails, an exception is raised.
'''
def tlv_iterator(pkt):
    i=0
    
    while(i < len(pkt)):
        t = ord(pkt[i])
        l = ord(pkt[i+1])
        v = pkt[i+2:i+2+l]
        i = i + 2+l
        yield (t,v)
    if(i!=len(pkt)):
        raise Exception("BAD TLV")

class ZIPConnection(threading.Thread):
    class TxSession:
        pass

    ZIP_PACKET_FLAGS0_ACK_REQ =0x80
    ZIP_PACKET_FLAGS0_ACK_RES = 0x40
    ZIP_PACKET_FLAGS0_NACK_RES= 0x20
    ZIP_PACKET_FLAGS0_WAIT_RES= (1<<4)
    ZIP_PACKET_FLAGS0_NACK_QF = (1<<3)
    ZIP_PACKET_FLAGS1_HDR_EXT_INCL     = 0x80
    ZIP_PACKET_FLAGS1_ZW_CMD_INCL      = 0x40
    ZIP_PACKET_FLAGS1_MORE_INFORMATION = 0x20
    ZIP_PACKET_FLAGS1_SECURE_ORIGIN    = 0x10
    ZIP_OPTION_EXPECTED_DELAY = 1
    ZIP_OPTION_MAINTENANCE_GET = 2
    ZIP_OPTION_MAINTENANCE_REPORT = 3
    
    STATE_SENT = "Sent"
    STATE_WAITING ="Waiting"
    STATE_ACK = "Ack"
    STATE_NACK = "Nack"
    STATE_QF = "QueueFull"
    STATE_TIMEOUT = "Timeout"
    STATE_OPTION_ERROR = "OptionError"
    
    IMA_OPTION_RC = 0
    IMA_OPTION_TT = 1
    IMA_OPTION_LWR = 2

    speed_to_str =["NA","9.6kbit","40kbit","100kbit","200kbit"]
    wiatdelay1 = 1.0#0.350 #timeout when sending a message to the first waiting message
    wiatdelay2 = 62    #GW should send a Waiting message once every 60s
    '''
    ZIP connection on connected socket. 
    '''

    def __init__(self,sock,defaultHandler = None,psk= None,imaEnabled = False):
        self.imaEnabled = imaEnabled
        self.sock = sock
        self.seq =random.randint(0,0xff)
        self.advEv = threading.Event()
        self.cmdHandler = dict()
        self.defaultHandler =  defaultHandler
        
        self.running = True
        threading.Thread.__init__(self)
        self.daemon = True  
        self.start()
        self.lock = threading.Lock()
        self.sessions =[]

    def stop(self):
        self.running = False
        self.join()
                
    def do_read(self):
        try:
            pkt = self.sock.recv(512+4)
        except:
            return
        (cmdClass,cmd) = unpack("2b",pkt[0:2])
           
        logging.debug("Data....." + pkt.encode("hex"))
        #print ("Data....." + pkt.encode("hex"))
        if(cmdClass==COMMAND_CLASS_ZIP and cmd == 3):
            pass #Keepalive
        elif(cmdClass==COMMAND_CLASS_ZIP and cmd == COMMAND_ZIP_PACKET):
            (flags0,flags1,seq,send,dend) = unpack("5B",pkt[2:7])

            if(flags0 & self.ZIP_PACKET_FLAGS0_ACK_REQ):
                ack =[COMMAND_CLASS_ZIP,COMMAND_ZIP_PACKET,
                      self.ZIP_PACKET_FLAGS0_ACK_RES,
                      flags1 & self.ZIP_PACKET_FLAGS1_SECURE_ORIGIN, 
                      seq,dend,send]
                
                self.sock.send( array.array('B',ack).tostring() )

            #find the session of this message
            try:
                s = next(x for x in self.sessions if (x.seq == seq and x.lendpoint == dend and x.rendpoint == send) )
                #Remove s from the list, we might add it again later...
                self.lock.acquire()
                self.sessions.remove(s)
                self.lock.release()                

                if(flags0 & self.ZIP_PACKET_FLAGS0_NACK_RES):                
                    if(flags0 & self.ZIP_PACKET_FLAGS0_WAIT_RES):                    
                        #logging.debug("NACK Waiting")
                        s.timer = time.time() + self.wiatdelay2 #GW should send a Waiting message once every 60s
                        s.expected_delay = self.wiatdelay2
                        s.state = self.STATE_WAITING
                    elif(flags0 & self.ZIP_PACKET_FLAGS0_NACK_QF):
                        s.state = self.STATE_QF
                        logging.error("Target reported Queue full")
                    else:                        
                        s.state = self.STATE_NACK                        
                if(flags0 & self.ZIP_PACKET_FLAGS0_ACK_RES):
                    s.state = self.STATE_ACK
            except StopIteration:
                s = None
            
            l = 7
            #parse extended headersebne
            if(flags1 & self.ZIP_PACKET_FLAGS1_HDR_EXT_INCL):
                exh_len = ord(pkt[l])
                self.ima=dict()
                if(s):
                    logging.debug("Extended header",pkt[l+1:l+exh_len].encode("hex"))
                    logging.debug("Header len", len(pkt[l+1:l+exh_len]))
                    
                    for otype,odata in tlv_iterator(pkt[l+1:l+exh_len] ):
                        if((otype & 0x7f ) ==self.ZIP_OPTION_EXPECTED_DELAY):
                            s.expected_delay =ord(pkt[l+2]) <<16 | ord(pkt[l+3]) <<8 | ord(pkt[l+4])
                        #Parse IMA options
                        if((otype & 0x7f ) ==self.ZIP_OPTION_MAINTENANCE_REPORT):
                            for itype,idata in tlv_iterator(odata):
                                
                                if(itype == self.IMA_OPTION_RC ):
                                    self.ima["route_change"] =  ord(idata) >0 
                                elif(itype == self.IMA_OPTION_LWR):
                                    #Seperate hoplist and speed
                                    self.ima["last_working_route"] = map(ord,idata[:4])
                                    self.ima["speed"] = self.speed_to_str[ord(idata[4])]
                                #elif(itype == self.IMA_OPTION_SNR):
                                #    self.snr = ord(idata)
                                elif(itype == self.IMA_OPTION_TT):
                                    (tx_time,) = unpack("!H",idata);
                                    self.ima["tx_time"] = tx_time

                        if(otype & 0x80):
                            s.state  = self.STATE_OPTION_ERROR
                l = l +exh_len
            
            if(s):
                #Waiting messages should be re-inserted in the session list
                if(s.state == self.STATE_WAITING):
                    self.lock.acquire()
                    self.sessions.append(s)
                    self.lock.release()                
            
                if(s.cb):
                    s.cb(s)

            if(flags1 & self.ZIP_PACKET_FLAGS1_ZW_CMD_INCL):            
                frame =pkt[l:]
                (hid,) = unpack("!H",frame[0:2])                
                if(hid in self.cmdHandler):
                    f= self.cmdHandler[hid]
                else:
                    f = self.defaultHandler

                if(f): 
                    
                    # Old cmd handler functions cannot receive the source ip of 
                    # the incoming ZIP packet. New function handlers need it.
                    # So we inspect the function handler and supplies the
                    # source ip if the function supports it.
                    import inspect
                    _, _, varkw, _ = inspect.getargspec(f)
                    #TODO do we really need new threads here?
                    if varkw:
                        srcaddr = self.sock.getpeername()
                        #f(frame,source_ip=srcaddr[0], source_port=srcaddr[1])
                        thread.start_new_thread(f,(frame,), {'source_ip':srcaddr[0],'source_port':srcaddr[1]})
                    else:
                        thread.start_new_thread(f,(frame,))
                        #f(frame)
                else:
                    logging.warn("No handler registered for this command %4.4x" % (hid))                    
                    
        elif(cmdClass==COMMAND_CLASS_ZIP_ND and cmd == ZIP_NODE_ADVERTISEMENT):            
            self.nodeADV = pkt
            self.advEv.set()
        else:
            
            logging.error( "Garbage len %i" % len(pkt) )
            logging.error( "---------" ,pkt.encode("hex") )
            
                    
    def zip_hdr(self,sEndpoint=0,dEndpoint=0):
        self.seq = (self.seq + 1) & 0xFF
        
        flag1 = self.ZIP_PACKET_FLAGS1_ZW_CMD_INCL |  self.ZIP_PACKET_FLAGS1_SECURE_ORIGIN
        if self.imaEnabled:
            flag1 = flag1 | self.ZIP_PACKET_FLAGS1_HDR_EXT_INCL
        s= pack("7B",
                    COMMAND_CLASS_ZIP,
                    COMMAND_ZIP_PACKET,
                    self.ZIP_PACKET_FLAGS0_ACK_REQ,
                    self.ZIP_PACKET_FLAGS1_ZW_CMD_INCL | 
                    flag1,
                    self.seq & 0xFF,sEndpoint,dEndpoint                                       
                    )

        if(self.imaEnabled):
            s=s + array.array('B',[3,self.ZIP_OPTION_MAINTENANCE_GET,0]).tostring()
        return s
                
    def sendDataAsync(self,cmd,cb,dendpoint=0,sendpoint=0,retransmit=True,max_time=None):
        '''
        Sends command formatted as sting to zip node. 
        
        cb is a callback function which will be called when there is new 
        status information available. cb might be called several times with
        the waiting state.        
        '''
        s = self.TxSession()
        s.data = self.zip_hdr(sEndpoint=sendpoint,dEndpoint=dendpoint) +cmd
        s.seq = self.seq
        s.state = self.STATE_SENT
        s.retry = 2 if retransmit else 0
        s.timer = time.time()+self.wiatdelay1 # We start with a 500ms timeout, the GW should send a waiting with in 200ms
        s.cb = cb
        s.lendpoint = sendpoint
        s.rendpoint = dendpoint
        
        self.lock.acquire()
        self.sessions.append(s)
        self.lock.release()

        #Reopen ssl socket if it has been closed
        if(hasattr( self.sock, "_sslobj") and self.sock._sslobj == None):
            host = self.sock.getpeername()
            family = socket.getaddrinfo( host[0],host[1] )[0][0]
            psk = self.sock.psk
            self.sock = dtls_client.create_dtls_psk_sock(host , lambda x: (None,psk), family)
        self.sock.send(s.data)

        return True

    def sendData(self,cmd,**kwargs):
        '''
        A sync version of sendDataAsync it will return the
        transmission state
        '''
        ev = threading.Event()
        state = []
        def cb(s): #local callback function            
            if(s.state != self.STATE_WAITING):
                state.append(s.state)
                ev.set()
                                
        self.sendDataAsync(cmd, cb, **kwargs)
        
        if('max_time' in kwargs):
            time = kwargs['max_time']
        else:
            time = 2
        
        if not ev.wait(time):
            raise TimeoutError
        logging.debug("Send Done")
        
        if(len(state)> 0):
            return state[0]
        else:
            return 0
        
    def registerHandler(self,cmdClass,cmd, fun):
        hid = cmdClass<< 8 | cmd;                            
        self.cmdHandler[hid] = fun        
        
    def ipOfNode(self,node):
        self.nodeADV = None
        self.advEv.clear()
        self.sock.send(pack("4B",
             COMMAND_CLASS_ZIP_ND,ZIP_INV_NODE_SOLICITATION,
             0, node))
 
        #On windows the timeout value does nothing, the call returns immedeatly  
        self.advEv.wait(3)
        return self.nodeADV

    def run(self):
        keep_alive_timer = time.time()
        #Set the socket timeout
        self.sock.settimeout(0.1)
        while(self.running):
            self.do_read()
            
            #Send a keep alive signal to the Gateway
            if(  isinstance( self.sock, ssl.SSLSocket ) and (time.time() - keep_alive_timer) > 25):
                keep_alive_timer = time.time()
                #self.sock.send("\042\042")
                self.sock.send( "230380".decode("hex") );
                
            for s in self.sessions:
                if(s.timer < time.time()):
                    if(s.retry==0):
                        self.lock.acquire()
                        self.sessions.remove(s)
                        self.lock.release()                
                        s.state = self.STATE_TIMEOUT
                        if(s.cb):
                            s.cb(s)
                        break
                    else:
                        s.retry = s.retry - 1
                        s.timer = time.time() + self.wiatdelay1                        
                        self.sock.send(s.data)
        print "Client shutdown"
        if(isinstance( self.sock, ssl.SSLSocket )): 
        	dtls_client.dtls_close(self.sock)


class ZIPClient(ZIPConnection):
    def __init__(self, host=None,secure=True,psk="1234567890".decode("hex"),*args,**kwargs):
        
        if isinstance(host, IPv6Address) or isinstance(host, IPv4Address):
            host = str(host)
        
        if(host):
            new_ip = IPAddress(host)
            if hasattr(new_ip, 'ipv4_mapped') and new_ip.ipv4_mapped:
                new_ip = new_ip.ipv4_mapped
                host = str(new_ip)
            
        if(host != "<broadcast>"):
            family = socket.getaddrinfo(host,4123)[0][0]
        else:
            family = socket.AF_INET
                
        
        if(secure):    
            sock = dtls_client.create_dtls_psk_sock((host,41230) , lambda x: ("Client_identity",psk), family)
            sock.psk = psk
        else:
            sock = socket.socket(family,socket.SOCK_DGRAM)
            sock.connect((host,4123))
            
        if(host == "<broadcast>"):
            sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            sock.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST,1)

        if(host==None):
            sock.bind(("",4123))
        
        ZIPConnection.__init__(self,sock,*args,**kwargs)
        self.host = host
        self.secure = secure
        
    pass

import SocketServer

class ZIPServer(threading.Thread):
    '''
    Implementation of ZIP connection. Right now only a Secure socket works.
    '''
    def __init__(self,secure=True,defaultHandler=None,af = socket.AF_INET,psk="1234567890".decode("hex")):
        self.secure = secure
        self.psk = psk
        self.sock  = socket.socket(af, socket.SOCK_DGRAM)
        
        if(secure):
            do_patch()
            self.sock = ssl.wrap_socket(self.sock,server_side=True,
                                   certfile="../utils/cert.pem",
                                   keyfile="../utils/key.pem",
                                   do_handshake_on_connect = False)
            self.sock.bind(('', 41230))
            self.sock.listen(5)
            dtls_client.dtls_set_server_psk(self.sock,self.psk)
        else:
            self.sock.bind(('', 4123))
            
        self.sock.settimeout(5)
        self.defaultHandler = defaultHandler
        self.handlers =[]
        if(secure):
            self.running =True
            self.cons = []
            threading.Thread.__init__(self)
            self.start()
        else:
            self.conn = ZIPConnection(self.sock)
    
    def run(self):
        while(self.running):
            c = None
            while (not c):
                #print "."
                time.sleep(0.1)
                try:
                    c = self.sock.accept()
                except socket.timeout:
                    continue

            print "Accept",c

            conn,addr = c
            #print "accept done,",c
            conn.settimeout(1)
            dtls_client.dtls_set_server_psk(conn,self.psk)
            conn.do_handshake()
            
            zc = ZIPConnection(conn)
            zc.defaultHandler = self.defaultHandler
            #Add handlers to the connection
            for h in self.handlers:
#                print "Handlers: ",h
                zc.registerHandler(*h)
                
            self.cons.append(zc)
        
        for c in self.cons:
            c.stop()
            c.join(2)
    
    def registerHandler(self,*args,**kwargs):
        self.handlers.append(args)
        for c in self.cons:
            c.registerHandler(*args,**kwargs)
            
    def stop(self):
        if(self.secure):
            self.running = False
            self.join(2)
        else:
            self.conn.stop()
        
    
def testCallback(s):
    print s.state

def testVersionCallback(frame):
    print "Reply: ",frame.encode("hex")


def testClient():
    client = ZIPClient("192.168.1.108")
    client.registerHandler(COMMAND_CLASS_VERSION, VERSION_REPORT, testVersionCallback)
    
    #print "IP" +  client.ipOfNode(1)
    client.sendData(array.array('B',[COMMAND_CLASS_VERSION,VERSION_GET]).tostring())
    #client.getNodelist(printNodeList)    

    time.sleep(2)
    client.stop()

def testServer():
    s = ZIPServer(secure=True)
    
if __name__ == '__main__':
    #testServer()
    testClient()
    print "stop"
    pass
