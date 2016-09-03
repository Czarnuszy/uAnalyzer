'''
Created on Feb 20, 2012

@author: aes
'''
from struct import *
from pyzwave.serial.ZWapi import *
from pyzwave.ZW_controller_api import *
from pyzwave.ZW_classcmd import COMMAND_CLASS_MULTI_CHANNEL_ASSOCIATION_V2, ASSOCIATION_SET_V2,\
MULTI_CHANNEL_ASSOCIATION_SET_V2, MULTI_CHANNEL_ASSOCIATION_SET_MARKER_V2, ASSOCIATION_REMOVE_V2,\
MULTI_CHANNEL_ASSOCIATION_REMOVE_MARKER_V2, MULTI_CHANNEL_ASSOCIATION_SET_V2,\
MULTI_CHANNEL_ASSOCIATION_REMOVE_V2
from pyzwave.ZW_classcmd import *
from pyzwave.serial.ZW_basis_api import *
from threading import Timer
import logging
from time import time
from pyzwave.serial.ZW_slave_api import ASSIGN_COMPLETE
from pyzwave.serial.ZW_transport_api import TRANSMIT_OPTION_ACK,\
  TRANSMIT_OPTION_AUTO_ROUTE

class ZWBasicNode(ZWapi):
    '''
    classdocs
    '''

    def __init__(self,port):
        '''
        Constructor
        '''
        ZWapi.__init__(self,port)

        self.value = 0
        self.nodeid = 0
        self.RegisterHandler(FUNC_ID_APPLICATION_COMMAND_HANDLER,"3B",self.ApplicationCommandHandler)
        self.RegisterHandler(FUNC_ID_APPLICATION_COMMAND_HANDLER_BRIDGE, "4B", self.ApplicationCommandHandlerBridge)

        self.RegisterHandler(FUNC_ID_ZW_APPLICATION_UPDATE,"3B",self.AppliactionControllerUpdate)

        self.appHandlers = dict()
        self.RegisterAppHandler( BasicHandler )
        try:
            self.post_init()
        except Exception as e:
            self.stop()
            print e

    #Should be called after the appHandlers has been set
    def post_init(self):
        #Build the class list
        cl=""
        for c in self.appHandlers.keys():
            if(c != COMMAND_CLASS_BASIC):
                cl = cl + chr(c)

        self.SerialAPI_ApplicationNodeInformation(APPLICATION_NODEINFO_LISTENING,
                                                GENERIC_TYPE_SWITCH_MULTILEVEL,SPECIFIC_TYPE_POWER_SWITCH_MULTILEVEL,cl)
        self.ZW_AddNodeToNetwork(ADD_NODE_STOP, None)
        self.ZW_RemoveNodeFromNetwork(REMOVE_NODE_STOP, None)

        self.nodeid = self.MemoryGetID()["nodeid"]

    def stop(self):
        for  h in self.handlers:
            del h
        ZWapi.stop(self)

    def RegisterAppHandler(self, handler, *args, **kwargs):
        c = handler(self, *args, **kwargs)
        self.appHandlers[c.zwclass] = c
        return c

    def getAppHandler(self, cmdclass):
        return self.appHandlers[cmdclass]

    def isLearnCompleteStatus(self, status):
        '''
        Function returns True for all Learn Mode callback status codes that signify the end
        of learn mode. Codes that will be followed by more callbacks return False.
        '''
        if self.is_controller:
            if(status ==LEARN_MODE_DONE or status == LEARN_MODE_FAILED):
                return True
        else:
            if (status == ASSIGN_COMPLETE):
                return True
        return False;

    def learnCallback(self,status,source,length=None,data=None):
        print "learn done",status, "src=",source," l",length

        if self.isLearnCompleteStatus(status):
            self.nodeid = self.MemoryGetID()["nodeid"]
            for h in self.appHandlers:
                if( hasattr(self.appHandlers[h], 'post_learn_hook')):
                    self.appHandlers[h].post_learn_hook(status)
        if self.learn_mode_cb:
                self.learn_mode_cb(status, source, length, data)

    def addNodeCallback(self,status,source,length=None,data=None):
        if(status == ADD_NODE_STATUS_DONE or status == ADD_NODE_STATUS_FAILED):
            self.ZW_AddNodeToNetwork(ADD_NODE_STOP, None)
            print "Add done, ", status
            for h in self.appHandlers:
                if( hasattr(self.appHandlers[h], 'post_add_hook')):
                    self.appHandlers[h].post_add_hook(status,self.nodeid_just_added)
            if self.addNodecb:
                self.addNodecb(status, source, length, data)

        elif(status == ADD_NODE_STATUS_ADDING_CONTROLLER or status == ADD_NODE_STATUS_ADDING_SLAVE):
            self.nodeid_just_added = source
            self.nodeinfo_just_added = data
        elif(status == ADD_NODE_STATUS_PROTOCOL_DONE):
            self.ZW_AddNodeToNetwork(ADD_NODE_STOP, self.addNodeCallback)

    def removeNodeCallback(self,status,source,length=None,data=None):
        if(status == REMOVE_NODE_STATUS_REMOVING_CONTROLLER or status == REMOVE_NODE_STATUS_REMOVING_SLAVE):
            self.nodeid_just_removed = source
        elif(status == REMOVE_NODE_STATUS_DONE or status == REMOVE_NODE_STATUS_FAILED):
            self.ZW_RemoveNodeFromNetwork(REMOVE_NODE_STOP,None);
            if self.removeNodecb:
                self.removeNodecb(status, self.nodeid)

    def setLearnMode(self, mode=ZW_SET_LEARN_MODE_CLASSIC, fun=None):
        self.ZW_SetLearnMode(mode, self.learnCallback)
        self.learn_mode_cb = fun


    def addNode(self,mode = (ADD_NODE_OPTION_HIGH_POWER | ADD_NODE_OPTION_NETWORK_WIDE | ADD_NODE_ANY),callback = None):
        self.addNodecb = callback
        self.ZW_AddNodeToNetwork(mode,self.addNodeCallback)

    def controllerChange(self,mode = CONTROLLER_CHANGE_START,callback = None):
        self.addNodecb = callback
        self.ZW_ControllerChange(mode,self.addNodeCallback)

    def removeNode(self,mode = REMOVE_NODE_ANY, callback = None):
        self.removeNodecb = callback
        self.ZW_RemoveNodeFromNetwork(mode, self.removeNodeCallback)

    def AppliactionControllerUpdate(self,bStatus, bNodeID, bLen,data):
        logging.debug("AppliactionControllerUpdate %i %i %i" %(bStatus, bNodeID, bLen) )


    def ApplicationCommandHandlerBridge(self, rxStatus, dest_id, src_id, length, data):
        multicast_info = data[length:]
        data = data[:length]
        #TODO: Handle multicast to virtual nodes
        if dest_id is self.nodeid:
           self.ApplicationCommandHandler(rxStatus, src_id, length, data)
           return

    def ApplicationCommandHandler(self,rxStatus,source,length,data):
        if(not data or len(data) < 2):
            logging.error("Short packet!")
            return

        (cls,cmd) = unpack("2B",data[0:2])
        self.lastRxStatus = rxStatus
        self.lastSrc = source
        self.lastpkt = data

        try:
            h = self.appHandlers[cls]
            h.handler(rxStatus,source,cmd, data[2:])
        except KeyError:
            logging.error("No App handler registered for class 0x%x len %i" % (cls,len(data)))
            logging.error("pkt %s" %(data.encode("hex")) )
        return

#Abstract class for a command handler
class AppHandler(object):
    #Abstract property
    zwclass = 0
    #Abstract method
    def setup(self):
        None
    #Abstract method
    #def post_learn_hook(self,status):
    #    None

    #Abstract method
    #def post_add_hook(self,status):
    #    None

    #Abstract method
    def handler(self, rxStatus,source,cmd, data):
        None

    #Send

    def __init__(self, zw_api):
        self.z = zw_api
        self.setup()

#Implementation of the basic command class
class BasicHandler(AppHandler):
    zwclass = COMMAND_CLASS_BASIC

    def setup(self):
        self.z.value = 0  # self.value would work better for MultiChannelHandler
                          # That way a separate value for each endpoint could be kept.

    def handler(self, rxStatus,source,cmd, data):
        if(cmd == BASIC_GET):
          msg = pack("3B",COMMAND_CLASS_BASIC,BASIC_REPORT,self.z.value)
          if not rxStatus & 0x100:
            self.z.ZW_SendData(source, msg ,0x1,0)
          else:
            sechandler = self.z.appHandlers[COMMAND_CLASS_SECURITY]
            txopt = TRANSMIT_OPTION_ACK | TRANSMIT_OPTION_AUTO_ROUTE;
            sechandler.send_secure_message(source, txopt, msg, self.cb_secure_tx)
        elif(cmd == BASIC_SET):
            try:
                self.z.value = ord(data[0])
            except IndexError:
                logging.error('Basic Set too short')
                raise AssertionError
        elif cmd == BASIC_REPORT:
            print 'Got Basic Report value', ord(data[0])

    def cb_secure_tx(self, status):
      print 'Got Secure tx status', status

'''
Implementation of the Wake up command class
'''
class WakeupHandler(AppHandler):
    zwclass = COMMAND_CLASS_WAKE_UP

    def setup(self):
        self.wakeup_interval = 120
        self.wakeup_node = 1

    def handler(self, rxStatus,source,cmd, data):
        if(cmd == WAKE_UP_INTERVAL_GET):
            self.z.ZW_SendData(source,pack("6B",
                                         COMMAND_CLASS_WAKE_UP,
                                         WAKE_UP_INTERVAL_REPORT,
                                         (self.wakeup_interval >> 16) & 0xFF,
                                         (self.wakeup_interval >> 8) & 0xFF,
                                         (self.wakeup_interval >> 0) & 0xFF,
                                         self.wakeup_node),0x1,0)
        elif(cmd == WAKE_UP_INTERVAL_SET):
            self.wakeup_interval = ord(data[0])<< 16 | ord(data[1])<< 8 | ord(data[2])<< 0
            self.wakeup_node = ord(data[3])
            logging.debug("Wakeup interval set to %is and node %i" % (self.wakeup_interval, self.wakeup_node))
            self.t.cancel()
            self.t.join()
            self.wakeUp()

        elif(cmd == WAKE_UP_NO_MORE_INFORMATION):
            logging.debug("No more info")
            self.t.cancel()
            self.t.join()
            self.gotoSleep()

    def wakeUp(self):
        logging.debug("Node is awake")
#        print "Node is awake"
        self.z.ZW_SetRFReceiveMode(1)
        self.z.ZW_SendData(self.wakeup_node,pack("2B",
                             COMMAND_CLASS_WAKE_UP,
                             WAKE_UP_NOTIFICATION),0x1,0)
        self.t.cancel()
        self.t = Timer(1.0, self.gotoSleep)
        self.t.start()

    def setWakeupInterval(self,interval):
        self.wakeup_interval = interval;
        self.wakeUp()

    def gotoSleep(self):
#        print "Node is sleeping "
        logging.debug("Node is sleeping " )
        self.z.ZW_SetRFReceiveMode(0)
        self.t.cancel()
        self.t = Timer(self.wakeup_interval, self.wakeUp)
        self.t.start()

    def post_learn_hook(self,status):
        logging.debug("Addnode hook sleeping")

        self.t = Timer(self.wakeup_interval, self.gotoSleep)
        self.t.start()

    def __del__(self):
        print "Shutdown"
        self.t.cancel()
        self.t.join()

'''
Implementation of the classic Association Command Class.
'''
from collections import defaultdict
import struct
class AssociationHandler(AppHandler):
    '''
    z.associations is a dictionary with keys representing each
    association group. Each value is a list of (nodeid, endpoint)
    tuples, representing the association targets.
    An endpoint value of None represents a single-channel association.
    '''
    zwclass = COMMAND_CLASS_ASSOCIATION

    def setup(self):
        '''
        If our ZWApi is the root device, store associations as a property
        up there (in the same list  as the MultiChannelAssociationHandler,
        if that exists).
        Otherwise we are most likely inside an endpoint of a multichannel
        device. In that case, store the associations locally, so we keep
        associations for each endpoint separate.
        '''
        if isinstance(self.z, ZWBasicNode):
            if not hasattr(self.z, 'associations'):
                self.z.associations = defaultdict(list)
            self.associations = self.z.associations
        else:
            self.associations = defaultdict(list)

    def handler(self, rxStatus,source,cmd, data):
        if(cmd == ASSOCIATION_SET_V2):
            (grouping, nid) = struct.unpack("2B", data)
            self.associations[grouping].append((nid, None))
        elif(cmd == ASSOCIATION_REMOVE_V2):
            (grouping, nid) = struct.unpack("2B", data)
            self.associations[grouping].remove((nid, None))

# Examples exclude command class and commmand to fit with parse_multi_channel_assoc_set
example_multi_channel_assoc_set0 = struct.pack('BB2B', 0,
                                           MULTI_CHANNEL_ASSOCIATION_SET_MARKER_V2,
                                           5, 4)

example_multi_channel_assoc_set1 = struct.pack('BBB2B', 3, 7,
                                           MULTI_CHANNEL_ASSOCIATION_SET_MARKER_V2,
                                           232, 1)

def parse_multi_channel_assoc_set(data):
    '''
    Parse a multichannel association set and return the
    tupple (grouping, singlechannel_assocs, multichannel_assocs).
    Note: Command class and command must be elided.
    '''
    return _parse_multi_channel_assoc_common(data)

def parse_multi_channel_assoc_remove(data):
    '''
    Parse a multichannel association set and return the
    tupple (grouping, singlechannel_assocs, multichannel_assocs).
    Note: Command class and command must be elided.
    '''
    return _parse_multi_channel_assoc_common(data)


def _parse_multi_channel_assoc_common(data):
    MULTI_CHANNEL_ASSOCIATION_BIT_ADDRESS_FLAG = 0x80
    multichannel_assocs=[]  # list of (nodeid, endpoint) tuples
    singlechannel_assocs=[] # list of nodeids
    grouping = ord(data[0])
    #TODO: Check that only one marker is present
    assert MULTI_CHANNEL_ASSOCIATION_SET_MARKER_V2 == MULTI_CHANNEL_ASSOCIATION_REMOVE_MARKER_V2
#    idx = data[1:].index(MULTI_CHANNEL_ASSOCIATION_SET_MARKER_V2)
#    s = data[1:idx+1]
#    m = data[idx+1:]
    s, m = data[1:].split(chr(MULTI_CHANNEL_ASSOCIATION_SET_MARKER_V2), 1)
    print s.encode('hex'), m.encode('hex')
    for x in s: singlechannel_assocs.append(ord(x))
    assert len(m) % 2 is 0
    for (nid, ep) in zip(m[0::2], m[1::2]):
        nid, ep = ord(nid), ord(ep)
        if not ep & MULTI_CHANNEL_ASSOCIATION_BIT_ADDRESS_FLAG:
            multichannel_assocs.append((nid, ep))
        else:
            raise Exception('bit addressing not supported')
    return (grouping, singlechannel_assocs, multichannel_assocs)

class MultiChannelAssociationHandler(AppHandler):
    zwclass = COMMAND_CLASS_MULTI_CHANNEL_ASSOCIATION_V2

    def setup(self):
        '''
        If our ZWApi is the root device, store associations as a property
        up there (in the same list  as the AssociationHandler, if that exists).
        Otherwise we are most likely inside an endpoint of a multichannel
        device. In that case, store the associations locally, so we keep
        associations for each endpoint separate.
        '''
        if isinstance(self.z, ZWBasicNode):
            if not hasattr(self.z, 'associations'):
                self.z.associations = defaultdict(list)
            self.associations = self.z.associations
        else:
            self.associations = defaultdict(list)


    def handler(self, rxStatus,source,cmd, data):
        if(cmd == MULTI_CHANNEL_ASSOCIATION_SET_V2):
            print 'appending multichannel association', str(data).encode('hex')
            grouping, single, multi = parse_multi_channel_assoc_set(data)
            for nid in single:
                self.associations[grouping].append((nid, None))
            for nid, ep in multi:
                self.associations[grouping].append((nid, ep))
        elif(cmd == MULTI_CHANNEL_ASSOCIATION_REMOVE_V2):
            grouping, single, multi = parse_multi_channel_assoc_remove(data)
            for nid in single:
                self.associations[grouping].remove((nid, None))
            for nid, ep in multi:
                self.associations[grouping].remove((nid, ep))

class MultiChannelHandler(AppHandler):
    '''
    Handler for parsing Multi Channel Command Encapsulations
    only. Other Multi Channel commands are not supported yet.
    '''
    zwclass = COMMAND_CLASS_MULTI_CHANNEL_V2
    appHandlers = defaultdict(dict) # dict of dicts appHandlers, one inner dict per endpoint
    last_peer = (None, None, None)

    def setup(self):
        pass

    def registerAppHandler(self, handler, endpoint):
        c = handler(self)
        self.appHandlers[endpoint][c.zwclass] = c
        return c

    def getAppHandler(self, cmdclass, endpoint):
        return self.appHandlers[endpoint][cmdclass]

    def handler(self, rxStatus,source,cmd, data):
        if(cmd == MULTI_CHANNEL_CMD_ENCAP_V3):
            print data.encode('hex')
            src_ep, dest_ep, inner_cc, inner_cmd = struct.unpack('4B', data[0:4])
            inner_data = data[4:]
            if dest_ep & 0x80:
                print 'bit endpoint addressing not supported'
                return
            self.last_peer = (source, src_ep, dest_ep)
            try:
                self.appHandlers[dest_ep][inner_cc].handler(rxStatus, source, inner_cmd, inner_data)
            except KeyError:
                print 'No multichannel app handler registered for class 0x%x, endpoint %x' %  (inner_cc, dest_ep)
        else:
            print 'unsupported multi channel command: %x' % cmd

    def ZW_SendData(self,nodeID, data, txOptions, fun):
        '''
        Proxy for multi-channel encapsulating replies
        '''
        encap_hdr = ''
        last_nid, last_src_ep, last_dest_ep = self.last_peer
        if nodeID is last_nid:
            encap_hdr = struct.pack('4B', COMMAND_CLASS_MULTI_CHANNEL_V3, MULTI_CHANNEL_CMD_ENCAP_V3,
                                    last_src_ep, last_dest_ep)
        self.z.ZW_SendData(nodeID, encap_hdr + data, txOptions, fun)


'''
Implementation of the classic Association Command Class.
'''
class ApplicationControllerUpdateHandler(AppHandler):
    zwclass = FUNC_ID_ZW_APPLICATION_CONTROLLER_UPDATE

    def setup(self):
        self.z.last_nif = []

    def handler(self, rxStatus,source,cmd, data):
        self.z.last_nif = [cmd, data]
