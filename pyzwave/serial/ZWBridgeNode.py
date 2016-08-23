from struct import *
from pyzwave.serial.ZWapi import *
from pyzwave.ZW_controller_api import *
from pyzwave.ZW_classcmd import *
from pyzwave.serial.ZW_basis_api import *
from threading import Timer
import logging
from time import time
from pyzwave.serial.ZWBasicNode import ZWBasicNode
from collections import defaultdict
from pyzwave.Waiter import Waiter
from pyzwave.zip.ipaddr import IPv4Address, IPv6Address

class ZWBridgeNode(ZWBasicNode):
    '''
    classdocs
    '''
    
    virtualAppHandlers = dict()

    def __init__(self,port):        
        '''
        Constructor
        '''
        ZWBasicNode.__init__(self,port)
        
        self.basic_set_value = 0
        self.RegisterHandler(FUNC_ID_APPLICATION_COMMAND_HANDLER_BRIDGE,"4B",self.ApplicationCommandHandlerBridge)
        
        # TODO: SecurityHandler
        #self.RegisterAppHandler( BasicHandler )
        #self.post_init()
        
    def registerVirtualAppHandler(self, handler, *args, **kwargs):
        c = handler(self, *args, **kwargs)
        self.virtualAppHandlers[c.zwclass] = c
        return c
        
    def virtual_post_init(self):
        #Build the class list
        cl=""
        for c in self.virtualAppHandlers.keys():
            if(c != COMMAND_CLASS_BASIC):
                cl = cl + chr(c)
                                
        self.SerialAPI_ApplicationSlaveNodeInformation(1, # This nodeid is ignored in the target 
                                                       APPLICATION_NODEINFO_LISTENING, 
                                                       GENERIC_TYPE_SWITCH_MULTILEVEL, 
                                                       SPECIFIC_TYPE_POWER_SWITCH_MULTILEVEL, 
                                                       cl)

    def ApplicationCommandHandlerBridge(self, rxStatus, dest_id, src_id, length, data):
        multicast_info = data[length:]
        data = data[:length]
        #TODO: Handle multicast to virtual nodes
  
        if dest_id is self.nodeid:
            self.ApplicationCommandHandler(rxStatus, src_id, length, data)
            return

        if(not data or len(data) < 2):
            logging.error("Short packet!")
            return
        
        (cls,cmd) = unpack("2B",data[0:2])
        self.lastRxStatus = rxStatus
        self.lastSrc = src_id
        self.lastpkt = data

        #FIXME: Turn this into a proper Virtual App Command Handler 
        if cls is COMMAND_CLASS_BASIC:
#            print '---'
#            print 'You should make a proper Virtual App Handler for Basic Command Class instead of this ugly hack'
#            print '---'
            self.BridgeBasicHandler(rxStatus, dest_id, src_id, ord(data[1]), data[2:])
            return
        
        try:
            h = self.virtualAppHandlers[cls]
        except KeyError:
            logging.error("No Virtual App handler registered for class 0x%x len %i" % (cls,len(data)))
            logging.error("pkt %s" %(data.encode("hex")) )
            return
        
        h.handler(rxStatus,dest_id, src_id,cmd, data[2:])
        
    def BridgeBasicHandler(self, rxStatus,dest,source,  cmd, data):      
        if(cmd == BASIC_GET):
            if rxStatus & 0x100:
                sh = self.virtualAppHandlers[COMMAND_CLASS_SECURITY].securityHandlers[dest]
                sh.send_secure_message(source,0x1,pack("3B",COMMAND_CLASS_BASIC,BASIC_REPORT,self.basic_set_value), None)
            else:
                self.ZW_SendData_Bridge(dest, source, pack("3B",COMMAND_CLASS_BASIC,BASIC_REPORT,self.basic_set_value) ,0x1,0)
        elif(cmd == BASIC_SET):
            try:
                self.basic_set_value = ord(data[0])
            except IndexError:
                logging.error('Basic Set too short')
                raise AssertionError
    
    def addVirtualNode(self, zip_ctrl, mode='Enable'):
        if mode is 'Add':
            raise Exception('Not implemented')
        if mode is 'Enable':
            return self.addVirtualNodeEnable(zip_ctrl)
            
    def addVirtualNodeEnable(self, zip_ctrl, raiseOnTimeout=False):
        from test_common import AddNodeWaiter, Waiter
        from pyzwave.serial.ZW_controller_bridge_api import VIRTUAL_SLAVE_LEARN_MODE_ENABLE, ASSIGN_COMPLETE
        from pyzwave.ZW_controller_api import ADD_NODE_STATUS_DONE
        from pyzwave.serial.ZW_transport_api import NODE_BROADCAST
        from time import sleep
        from test_common import get_ip
        w = AddNodeWaiter()
        zip_ctrl.startAddNode(w.f)
        sleep(2)
        vw = VirtualNodeEnableWaiter()
        rc = self.ZW_SetSlaveLearnMode(0, VIRTUAL_SLAVE_LEARN_MODE_ENABLE, vw.f)
        if not rc:
            eprint('Bridge controller declined VIRTUAL_SLAVE_LEARN_MODE_ADD, aborting')
            w2 = Waiter(self)
            zip_ctrl.stopAddNode(w2.f)
            w2.wait(raiseOnTimeout=raiseOnTimeout)
            return
        self.ZW_SendSlaveNodeInformation(0, NODE_BROADCAST, 0, None)
        # Retry the NIF once
        if not vw.wait(timeout=4, raiseOnTimeout=False):
            self.ZW_SendSlaveNodeInformation(0, NODE_BROADCAST, 0, None)
            if not vw.wait(timeout=4, raiseOnTimeout=raiseOnTimeout):
                eprint('Add failed with timeout')
                return
        (slave_status, org_id, new_id2) = vw.lastpkt
        
        for h in self.virtualAppHandlers:
            if( hasattr(self.virtualAppHandlers[h], 'post_add_hook')):
                self.virtualAppHandlers[h].post_add_hook(slave_status, new_id2)
        
        if not w.wait(raiseOnTimeout=raiseOnTimeout):
            eprint('Add failed with timeout on GW Add node slave_status.')
            return
        (ctrl_status, new_id) = w.lastpkt
        ADD_NODE_STATUS_SECURITY_FAILED = 9
        if ctrl_status is ADD_NODE_STATUS_SECURITY_FAILED:
            print 'Secure inclusion failed'
            config.errors['Secure inclusion failed'] = config.errors['Secure inclusion failed'] + 1
            return
        
        config.tc.assertIn(ctrl_status, [ADD_NODE_STATUS_DONE])
        config.tc.assertIn(slave_status, [ASSIGN_COMPLETE])
        config.tc.assertEqual(new_id, new_id2, "new nodeid mismatch")

        print "Node %u added" % new_id2
        # Get ip address, retry until it settles in case of ipv4
        new_ip = get_ip(zip_ctrl, new_id2)
        if new_ip.ipv4_mapped:
            new_ip = new_ip.ipv4_mapped
        print 'new_ip:', new_ip                             
        if hasattr(self, 'addNodecb'):
            self.addNodecb(slave_status, new_id2)    
        return new_id, new_ip

class VirtualNodeEnableWaiter(Waiter):
    def f(self, *args, **kwargs):
        from pyzwave.serial.ZW_controller_bridge_api import ASSIGN_COMPLETE
        status, org_id, new_id = args
        print 'VirtualNodeEnableWaiter status=%s, org_id=%s, new_id=%s' % args
        if status is ASSIGN_COMPLETE: #or status is ASSIGN_RANGE_INFO_UPDATE:
            self.lastpkt = args
            self.ev.set()

from pyzwave.serial.ZWBasicNode import AppHandler
class VirtualAppHandler(AppHandler):
    #Abstract method
    def handler(self, rxStatus, dest, source,cmd, data):
        None
        
    #Abstract method
    def post_add_hook(self, status, virtual_nodeid, ctrl_nodeid):
        None

        
class VirtualSecurityHandler(VirtualAppHandler):
    zwclass = COMMAND_CLASS_SECURITY 
                
    def setup(self):
        self.securityHandlers = dict()
    
    def handler(self, rxStatus, dest, source, cmd, data):
        sec_handler = self.securityHandlers[dest]
        sec_handler.handler(rxStatus,source,cmd, data)

    def post_add_hook(self, status, virtual_nodeid):
        from SecurityHandler import SecurityHandler
        h = SecurityHandler(ZWApi_Wrapper(self.z, virtual_nodeid))
        self.securityHandlers[virtual_nodeid] = h
        #h.post_add_hook(status, ctrl_nodeid)
        h.post_learn_hook(None)
        
class ZWApi_Wrapper:
    '''
    Act as a translation layer so we can use the same SecurityHandler for
    virtual nodes as for real nodes. Wraps a bridge ZWApi and 
    translates controls used by SecurityHandler.
    
    These controls may originally be implemented in ZWApi and/or ZWBasicNode. 
    E.g. .z.ZW_SendData() calls to .real_z.ZW_SendData_Bridge()
    '''
    
    def __init__(self, zw_api, virtual_nodeid):
        self.real_z = zw_api
        self.virtual_nodeid = virtual_nodeid
        self.nodeid = self.virtual_nodeid
        self.ser = self.DummySer(self)
        
    class DummySer:
        '''
        Dummy serial module to fake SecurityHandler into talking to a bridge.
        '''
        def __init__(self, wrapper):
            self.wrapper=wrapper
        
        def isOpen(self):
            return self.wrapper.real_z.ser.isOpen()
    
    
    def ZW_SendData(self,nodeID, data, txOptions, fun):
        c = self.real_z.ZW_SendData_Bridge(self.virtual_nodeid,nodeID,data, txOptions, fun)
        return c
    
    def ApplicationCommandHandler(self,rxStatus,source,length,data):
        self.real_z.ApplicationCommandHandlerBridge(rxStatus, self.virtual_nodeid, source, length, data)
     
def eprint(*args):
    for a in args:
        print a,
    print
