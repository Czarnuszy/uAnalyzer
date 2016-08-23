'''
Created on Jun 20, 2011

@author: aes
'''

from pyzwave.ZW_classcmd import *
from pyzwave.ZW_controller_api import *
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
import ZIPClient2

sys.path.append(os.path.dirname(__file__) + '/../ipaddr')
from ipaddr import IPAddress, IPv6Address, IPv4Address


class ZIPClient(ZIPClient2.ZIPClient):
    pass

# class ZIPClient(threading.Thread,socket.socket):
# 
#     ZIP_PACKET_FLAGS0_ACK_REQ =0x80
#     ZIP_PACKET_FLAGS0_ACK_RES = 0x40
#     ZIP_PACKET_FLAGS0_NACK_RES= 0x20
#     ZIP_PACKET_FLAGS0_WAIT_RES= (1<<4)
#     ZIP_PACKET_FLAGS0_NACK_QF = (1<<3)
#     ZIP_PACKET_FLAGS1_HDR_EXT_INCL     = 0x80
#     ZIP_PACKET_FLAGS1_ZW_CMD_INCL      = 0x40
#     ZIP_PACKET_FLAGS1_MORE_INFORMATION = 0x20
#     ZIP_PACKET_FLAGS1_SECURE_ORIGIN    = 0x10
#     
#     STATE_SENT = 1
#     STATE_WAITING =2
#     STATE_ACK = 3
#     STATE_NACK = 4
#     STATE_QF = 5
#     STATE_IDLE = 6
#     
#     INFINITE_LIFE = 0xFFFF
#     
#     def create_socket(self,host):
#         if(host):
#             new_ip = IPAddress(host)
#             if hasattr(new_ip, 'ipv4_mapped') and new_ip.ipv4_mapped:
#                 new_ip = new_ip.ipv4_mapped
#                 host = str(new_ip)
#             
#         if(host != "<broadcast>"):
#             family = socket.getaddrinfo(host,4123)[0][0]
#         else:
#             family = socket.AF_INET
#                 
# 
#         if(self.secure):
#             psk = array.array('B',[0x12,0x34,0x56,0x78,0x90])
#             self.sock = dtls_client.create_dtls_psk_sock((host,41230) , lambda x: (None,psk.tostring()), family)
#         else:
#             self.sock = socket.socket(family,socket.SOCK_DGRAM)
#             if(host==None):
#                 self.sock.bind(("",4123))
#             else:
#                 self.sock.bind((host,4123))
#         
#         if(host == "<broadcast>"):
#             
#             self.sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)            
#             self.sock.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST,1)
#             #self.bind(("192.168.1.10",0))
#         self.sock.settimeout(0.5)
#         
#     
#     '''
#     If host is None, we will listen on all interfaces on port 4123 
#     '''
#     def __init__(self, host=None, lifetime=INFINITE_LIFE,defaultHandler = None, secure=True):        
#         if isinstance(host, IPv6Address) or isinstance(host, IPv4Address):
#             host = str(host)
#         
#         self.host = host
#         self.secure = secure
#         self.create_socket(host)
#         
#         self.state = self.STATE_IDLE
#         self.seq =random.randint(0,0xff)
#         
#         self.cmdHandler = dict()
#         self.lifetime = lifetime
#         self.defaultHandler =  defaultHandler
#         self.response = threading.Event()
#         self.advEv = threading.Event()
#         self.enableACK =True #Test parameter to simulate a non responding client
#         self.running = True
#         threading.Thread.__init__(self)        
#         self.start()
#         self.lock = threading.Lock()
#             
#     def stop(self):
#         self.running = False
#         self.join()
#                 
#     def do_read(self):
#         try:
#             pkt = self.sock.recv(512+4)
#             
#         except:
#             return
#         (cmdClass,cmd) = unpack("2b",pkt[0:2])   
#         #logging.debug("Data....." + pkt.encode("hex"))
#         print ("Data....." + pkt.encode("hex"))
#         if(cmdClass==COMMAND_CLASS_ZIP and cmd == COMMAND_ZIP_PACKET):
#             (flags0,flags1,seq) = unpack("3B",pkt[2:5])
# 
#             if(flags0 & self.ZIP_PACKET_FLAGS0_ACK_REQ and self.enableACK):
#                 ack = pack("7B",
#                     COMMAND_CLASS_ZIP,
#                     COMMAND_ZIP_PACKET,
#                     self.ZIP_PACKET_FLAGS0_ACK_RES,
#                     self.ZIP_PACKET_FLAGS1_SECURE_ORIGIN,
#                     seq & 0xFF,ord(pkt[6]),ord(pkt[5]));
#                 self.sock.send( ack )
#                 
#             if(flags0 & self.ZIP_PACKET_FLAGS0_NACK_RES and seq == self.seq ):                
#                 if(flags0 & self.ZIP_PACKET_FLAGS0_WAIT_RES and seq == self.seq ):
#                     logging.debug("NACK Waiting")
#                     self.state = self.STATE_WAITING
#                 elif(flags0 & self.ZIP_PACKET_FLAGS0_NACK_QF):
#                     self.state = self.STATE_QF
#                     logging.error("Target reported Queue full")
#                 else:                        
#                     self.state = self.STATE_NACK
#                 self.response.set()
#             
#             if(flags0 & self.ZIP_PACKET_FLAGS0_ACK_RES):
#                 if(seq == self.seq):                
#                     self.state = self.STATE_ACK
#                     self.response.set()
#                 else:
#                     print "ACK with unexpected seq", seq,self.seq
#                  
#             if(flags1 & self.ZIP_PACKET_FLAGS1_ZW_CMD_INCL and len(pkt)>7 ):             
#                 if( (self.lifetime != self.INFINITE_LIFE) ) : self.lifetime -= 1 
#                 try:
#                     (zwclass,zwcmd) = unpack("2B",pkt[7:9])
#                     hid = zwclass<< 8 | zwcmd;                                                 
#                     f= self.cmdHandler[hid]
#                     #del self.cmdHandler[hid]                
#                 except KeyError as detail:                    
#                     f = self.defaultHandler 
#                     logging.debug("Key Error: ", str(detail),str(hid))
# 
#                 if(f and len(pkt) > 7): 
#                     frame =pkt[7:]
#                     # Old cmd handler functions cannot receive the source ip of 
#                     # the incoming ZIP packet. New function handlers need it.
#                     # So we inspect the function handler and supplies the
#                     # source ip if the function supports it.
#                     import inspect
#                     _, _, varkw, _ = inspect.getargspec(f)
#                     if varkw:
#                         kwargs = dict()
#                         kwargs['source_ip'] = ""#srcaddr[0]
#                         #f(frame,**kwargs)
#                         thread.start_new_thread(f,(frame,), kwargs)
#                     else:
#                         #f(frame)
#                         thread.start_new_thread(f,(frame,))
#                 else:
#                     logging.warn("No handler registered for this command %2.2x %2.2x" % (zwclass,zwcmd) )
#         elif(cmdClass==COMMAND_CLASS_ZIP_ND and cmd == ZIP_NODE_ADVERTISEMENT):            
#             self.nodeADV = pkt
#             self.advEv.set()
#         else:
#             print "Garbage " , pkt.encode("hex")
#             
#         if(self.lifetime == 0): self.stop()
#         
#     def zip_hdr(self,sEndpoint=0,dEndpoint=0):
#         self.seq = (self.seq + 1) & 0xFF
#         return pack("7B",
#                     COMMAND_CLASS_ZIP,
#                     COMMAND_ZIP_PACKET,
#                     self.ZIP_PACKET_FLAGS0_ACK_REQ,
#                     self.ZIP_PACKET_FLAGS1_ZW_CMD_INCL | self.ZIP_PACKET_FLAGS1_SECURE_ORIGIN,
#                     self.seq & 0xFF,sEndpoint,dEndpoint                                       
#                     )
#     #Wait for a new state, return false on timeout
#     def wait_for_new_state(self,to):
#         t = time.time()
#         self.response.wait(to)
#         self.response.clear()
#         #print "State is " + str(self.state)
#         return (t + to > time.time())        
#                 
#     def sendData(self,cmd,endpoint=0,max_time=0,retransmit=True):
#         '''
#         Returns True if ZIP ACK received
#                 False if ZIP NACK received
#                 Raises TimeoutErrir if no ACK/NACK received before timeout 
#         '''
#         
#         self.lock.acquire()
#             
#         self.state = self.STATE_SENT
#         if retransmit==True:
#             retx_count = 3
#         else:
#             retx_count = 1    
#         for i in range(1,retx_count):
#             self.response.clear()
#             
#             try:
#                 self.sock.send(self.zip_hdr(dEndpoint=endpoint) +cmd)
#                 if(self.wait_for_new_state(10)):
#                     break
#             except socket.error:
#                 self.create_socket(self.host)
#                 
#         c =0
#         while max_time >  0 and c < max_time and self.state == self.STATE_WAITING:
#             c = c + 1                
#             self.wait_for_new_state(1)
#             logging.debug("Waiting for queued command %is of %is state %i" % (c,max_time,self.state))
# 
#         if (self.state == self.STATE_NACK):
#             self.lock.release()
#             return False
#         if (self.state != self.STATE_ACK):
#             self.lock.release()
#             raise TimeoutError()
# 
#         self.lock.release()
#         return True
# 
#     def registerHandler(self,cmdClass,cmd, fun):
#         hid = cmdClass<< 8 | cmd;                            
#         self.cmdHandler[hid] = fun
#         
#     def ipOfNode(self,node):
#         self.nodeADV = None
#         self.advEv.clear()
#         self.sock.send(pack("4B",
#              COMMAND_CLASS_ZIP_ND,ZIP_INV_NODE_SOLICITATION,
#              0, node))
#  
#         #On windows the timeout value does nothing, the call returns immedeatly  
#         self.advEv.wait(3)
#         return self.nodeADV
# 
#     def run(self):
#         while(self.running):
#             self.do_read()
#         self.close()   

class NetworkManagement(ZIPClient):
    #***************** Network management ****************************    
    #/* Mode parameters to ZW_SetLearnMode */
         
#************************************** COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION *************************        
    def startAddNode(self,fun=None):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, NODE_ADD_STATUS, fun)
        self.sendData(pack("6B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,NODE_ADD, 0 ,0,ADD_NODE_ANY,1 ))

    def stopAddNode(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, NODE_ADD_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,NODE_ADD, 0 ,0,ADD_NODE_STOP ))

    def startDelNode(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, NODE_REMOVE_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,NODE_REMOVE,0 ,0,REMOVE_NODE_ANY ))

    def stopDelNode(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, NODE_REMOVE_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,NODE_REMOVE,0 ,0,REMOVE_NODE_STOP ))

    def removeFailedNode(self,node,fun):
        
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, FAILED_NODE_REMOVE_STATUS , fun)
        self.sendData(pack("4B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,FAILED_NODE_REMOVE ,0,node ))

    def replaceFailedNode(self,node,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, FAILED_NODE_REPLACE_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,FAILED_NODE_REPLACE ,0,node,ADD_NODE_ANY ))

    def stopFailedReplaceNode(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, FAILED_NODE_REPLACE_STATUS, fun)
        self.sendData(pack("4B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,FAILED_NODE_REPLACE ,0,ADD_NODE_STOP ))

    def requestNodeNeighborUpdate(self,node,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, NODE_NEIGHBOR_UPDATE_STATUS, fun)
        self.sendData(pack("4B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,NODE_NEIGHBOR_UPDATE_REQUEST ,0, node))

    def assignReturnRoute(self,srcNode,dstNode,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, RETURN_ROUTE_ASSIGN_COMPLETE, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,RETURN_ROUTE_ASSIGN ,0, srcNode,dstNode))

    def deleteReturnRoute(self,node,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION, RETURN_ROUTE_DELETE_COMPLETE, fun)
        self.sendData(pack("4B", COMMAND_CLASS_NETWORK_MANAGEMENT_INCLUSION,RETURN_ROUTE_DELETE ,0, node))

#************************************** COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC *************************
    def setDefault(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC, DEFAULT_SET_COMPLETE, fun)
        self.sendData(pack("3B", COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC,DEFAULT_SET,0))

    def startLearnMode(self,fun, mode=ZW_SET_LEARN_MODE_CLASSIC):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC, LEARN_MODE_SET_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC,LEARN_MODE_SET,0,0,mode))

    def stopLearnMode(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC, LEARN_MODE_SET_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC,LEARN_MODE_SET,0,0,ZW_SET_LEARN_MODE_DISABLE))

    def sendNodeInfo(self,dst):
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC,NODE_INFORMATION_SEND,0,0,dst))

    def requsetNetworkUpdate(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC, NETWORK_UPDATE_REQUEST_STATUS, fun)       
        self.sendData(pack("3B", COMMAND_CLASS_NETWORK_MANAGEMENT_BASIC,NETWORK_UPDATE_REQUEST,0))
        
#************************************** COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY *************************
    def getCachedInfo(self,node,fun,maxage=0):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY, NODE_INFO_CACHED_REPORT, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY,NODE_INFO_CACHED_GET,0,maxage,node))

    def getNodelist(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY, NODE_LIST_REPORT, fun)
        self.sendData( pack("3B", COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY,NODE_LIST_GET,0))
#************************************** COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY *************************
    def startControllerChange(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_PRIMARY, CONTROLLER_CHANGE_STATUS, fun)
        self.sendData(pack("6B", COMMAND_CLASS_NETWORK_MANAGEMENT_PRIMARY,CONTROLLER_CHANGE,0,0,CONTROLLER_CHANGE_START,1))

    def stopControllerChange(self,fun):
        self.registerHandler(COMMAND_CLASS_NETWORK_MANAGEMENT_PRIMARY, CONTROLLER_CHANGE_STATUS, fun)
        self.sendData(pack("5B", COMMAND_CLASS_NETWORK_MANAGEMENT_PRIMARY,CONTROLLER_CHANGE,0,0,CONTROLLER_CHANGE_STOP))

class Basic(ZIPClient):
    #***************** Network management ****************************    
    #/* Mode parameters to ZW_SetLearnMode */
    def basicSet(self,value,timeout=15):        
        return self.sendData( pack("3B", COMMAND_CLASS_BASIC,BASIC_SET,value),max_time=timeout)

    def basicGet(self,fun,timeout=15):
        self.registerHandler(COMMAND_CLASS_BASIC, BASIC_REPORT, fun)
        return self.sendData( pack("2B", COMMAND_CLASS_BASIC,BASIC_GET),max_time=timeout)

class IPAssociationZIPClient(ZIPClient):
    # DEPRECATED - use IPAssociationNode instead
    def setIpAssociation(self, res_ip, grouping=0, endpoint=0, res_name=None, timeout=0, assoc_src_endpoint=0):
        #self.registerHandler(COMMAND_CLASS_IP_ASSOCIATION, CONTROLLER_CHANGE_STATUS, fun)
        assert(not res_name) # Symbolic names not supported yet
        if res_name: res_name_len = len(res_name)
        else: res_name_len = 0
        self.sendData(pack("3B16s2B", COMMAND_CLASS_IP_ASSOCIATION, IP_ASSOCIATION_SET, grouping,
                       IPAddress(res_ip).packed, endpoint, res_name_len), 
                       endpoint=assoc_src_endpoint, max_time=timeout)

def printNodeList(pkt):
    print map(ord,pkt)
    
if __name__ == '__main__':
    
    client = NetworkManagement("fd00:aaaa::3")
    
    print "IP" +  client.ipOfNode(1)
    
    time.sleep(1)
    client.stop()
    client.join()
    print "Done"
    #client.getNodelist(printNodeList)
    
    pass
