'''
Created on 29/06/2011

@author: aes
'''
import binascii
import serial
import thread
import threading
import logging
from ZW_SerialAPI import *
import time
from struct import *
import time
from multiprocessing import TimeoutError, BufferTooShort
from ZW_basis_api import ZW_LIB_CONTROLLER, ZW_LIB_CONTROLLER_STATIC, \
    ZW_LIB_CONTROLLER_BRIDGE

from ZW_transport_api import TRANSMIT_OPTION_ACK,\
  TRANSMIT_OPTION_AUTO_ROUTE

#from serial.serialutil import SerialTimeoutException
from LoggedSerial import LoggedSerial
#import xml.etree.ElementTree
import xml.sax

EV_ACK = 0x1
EV_NAK = 0x2
EV_SEND_START = 0x3
EV_TIMEOUT = 0x4

TX_IDLE = 0x1
TX_SEND = 0x2


class ZWapi(threading.Thread):
    '''
    classdocs
    '''

    def __init__(self, p):
        '''
        Constructor
        '''
        threading.Thread.__init__ (self)

        self.ser = LoggedSerial(
        #self.ser = Serial(
    		port=p,
    		baudrate=115200,
    		parity=serial.PARITY_NONE,
    		stopbits=serial.STOPBITS_ONE,
    		bytesize=serial.EIGHTBITS,
    		timeout=0.2,
            writeTimeout=1
    	  )

        self.tx_state = TX_IDLE
        self.tx_timeout = 0
        self.ser.close()
        self.ser.open()

        self.ackEvent = threading.Event()
        self.resEvent = threading.Event()
        self.response_consumed_event = threading.Event()
        self.serLock = threading.Lock()
        self.txQueue = []
        self.sendFrameLock = threading.Lock()
        self.sendResFrameLock = threading.Lock()
        self.txLock = threading.Lock()

        self.frame = ""
        self.handlers = dict()
        self.daemon = True

        if(self.ser.isOpen()):
            self.start()
        else:
            raise Exception("Unable to open serial port " + p)
        self.acknak = 0
        self.library_type = 0  # Lazily initialized by TypeDetector
        self.is_controller = False  # Lazily initialized by TypeDetector

        #Send ACK to make target flush its output
        self.ser.write("\006")
        self.type_detector = TypeDetector(self)

    def stop(self):
        self.type_detector.join()
        self.running = False
        self.join()


    def SendFrame(self, zwreq):
        self.sendFrameLock.acquire()
        try:
            self.txQueue.append(zwreq)
            self.ackEvent.clear()
            self.tx_sm(EV_SEND_START)
            self.ackEvent.wait(1.5)
        finally:
            rc = self.ackEvent.isSet()
            self.sendFrameLock.release()
        return rc

    def SendFrameWithResponse(self, zwreq, raiseOnError=True):
        logging.debug("SendFrameWithResponse")
        self.sendResFrameLock.acquire()
        f=None
        try:
            self.resEvent.clear()
            if( not self.SendFrame(zwreq)):
                if raiseOnError: raise TimeoutError()

            t = time.time()
            self.resEvent.wait(0.5)
            if(time.time() - t > 1.5):
                if raiseOnError: raise TimeoutError()

            # Skip length, type, and command byte
            f = self.frame[3:]
            # print "Response"
            self.response_consumed_event.set()
            if(len(f) == 0):
                if raiseOnError:
                    # import pdb; pdb.set_trace()
                    # temporary code to investigate which SerialAPI
                    # call complains about not getting a response
                    msg = 'zwreq: ' + str(zwreq).encode('hex') + ' frame: ' + str(self.frame).encode('hex')
                    raise BufferTooShort(msg)
        finally:
            self.sendResFrameLock.release()
            return  f

    def chunks(self, l, n):
        for i in xrange(0, len(l), n):
            yield l[i:i + n]

    def send_frame(self, cmd):
        frame = pack("2B", len(cmd) + 2, REQUEST) + cmd
        chk = self.checksum(frame)
        try:
            pkt = (pack("B", SOF) + frame + pack("B", chk))
            self.ser.write(pkt)

        except SerialTimeoutException:
            print "Write timeout !!!!! "
            self.ser.close()
            self.ser.open()


    def tx_sm(self, event):
        self.txLock.acquire();

        #if(self.tx_state != TX_IDLE and event == EV_SEND_START):
        #    print "Send but not idle txQeueu len ",self.tx_state,len(self.txQueue)

        if(self.tx_state == TX_IDLE and event == EV_SEND_START):
            ##print "Frame sent...."
            self.tx_count = 0;
            self.send_frame(self.txQueue[0])
            # print "Send " + self.txQueue[0].encode("hex")
            self.tx_state = TX_SEND
            self.tx_timeout = time.time() + 0.2
        elif(self.tx_state == TX_SEND):
            if(event == EV_ACK):
                self.tx_state = TX_IDLE
                self.txQueue.pop(0)
                self.tx_timeout = 0
                # print "Send ACK"
                self.ackEvent.set()
            elif(event == EV_TIMEOUT):  # ignore EV_NAK... just timeout EV_NAK
                if(self.tx_count < 3):
                    self.tx_timeout = time.time() + (0.1 + self.tx_count)
                    self.tx_count = self.tx_count + 1
                    self.send_frame(self.txQueue[0])
                    print "Send retry ", self.tx_count, " event ", event
                else:
                    print "Transmit fault"
                    self.tx_state = TX_IDLE
                    self.txQueue.pop(0)
                    self.tx_timeout = 0
                    self.ackEvent.set()
        self.txLock.release();




    def run(self):
        SOF_STATE = 0x1
        LEN_STATE = 0x2
        TYP_STATE = 0x3
        DAT_STATE = 0x4
        CHK_STATE = 0x5
        SND_STATE = 0x6

        state = SOF_STATE
        lasttime = time.time()
        self.running = True
        left = 0


        # flip =  True
        while self.running:
            try:
                c = self.ser.read(1)
            except serial.SerialException:
                self.ser.close()
                self.ser.open()
                #on linux this message is seen at times:
                # device reports readiness to read but returned no data (device disconnected?)
                print "Serial hiccup, is device(%s) ok? " %(self.ser.getPort());
                continue

            if(state != SOF_STATE and time.time() - lasttime > 1.5):
                state = SOF_STATE
                logging.warn("Serial RX timeout")
                # self.serLock.release()

            if(state == SOF_STATE):
                if(not c or len(c) == 0 ):
                    if(len(self.txQueue) > 0):
                        self.tx_sm(EV_SEND_START)
                    if(self.tx_timeout > 0 and self.tx_timeout < time.time()):
                        self.tx_sm(EV_TIMEOUT)
                    continue

                if (c == chr(SOF)):
                    state = LEN_STATE
                    # self.serLock.acquire()
                    self.frame = ""
                elif(c == chr(ACK)):
                    self.tx_sm(EV_ACK)
                elif(c == chr(NAK) or c == chr(CAN)):
                    self.tx_sm(EV_NAK)
                else:
                    print "strange character %x" % ord(c)

            elif (state == LEN_STATE):
                left = ord(c)
                self.ser.setTimeout(1.5)
                frm = self.ser.read(left)
                self.ser.setTimeout(0.2)
                if(len(frm) < left):
                    state = SOF_STATE
                    continue

                typ = ord(frm[0])
                self.frame=c + frm[:-1]

                if(self.checksum(self.frame) == ord(frm[-1])):
                    #print 'RX ' + str(time.time()) + ":"+ self.frame.encode('hex')

                    try:
                        self.ser.write(chr(ACK))
                    except SerialTimeoutException:
                        print 'Ignoring SerialTimeoutException'

                    # print "TX " + str(time.time()) + ":" + hex(ACK)
                    if(typ == RESPONSE):
                        self.resEvent.set()
                        self.response_consumed_event.clear()
                        if not self.response_consumed_event.wait(timeout=5.0):
                            pass
                            # print 'response_consumed_event.wait() has timed out'
                    else:
                        self.NewFrame()
                    state = SOF_STATE
                    # self.serLock.release()
                else:
                    self.ser.write(chr(NAK))
                    state = SOF_STATE
                    # print "TX" + str(time.time()) + ":" + hex(NAK)

            if(state != SOF_STATE):
                lasttime = time.time()
        self.ser.close()
        None

    def checksum(self, pkt):
        s = 0xff
        for c in pkt:
            s ^= ord(c)
        return s


    def HandleRequest(self, frame):
        if(not self.running):
            return
        print 'HandleRequest', frame.encode("hex")
        cmd = ord(frame[1])
        funcid = ord(frame[2])
        try:
            (param, fun) = self.handlers[funcid]
        except KeyError:
            print "No handler registered for funcid 0x%x" % funcid
            return

        # Most functions starts with LEN|REQUEST|FUNCID|HANDLE|.... except these 3
        if(funcid == FUNC_ID_APPLICATION_COMMAND_HANDLER or funcid == FUNC_ID_ZW_APPLICATION_CONTROLLER_UPDATE or funcid == FUNC_ID_APPLICATION_COMMAND_HANDLER_BRIDGE):
            skip = 3
        else:
            skip = 4

        s = Struct(param).size
        if(len(frame) - skip == s):
            fun(*unpack(param, frame[skip:]), data=None)
        else:
            fun(*unpack(param, frame[skip:skip + s]), data=frame[skip + s:])

    def NewFrame(self):
        f = self.frame
        thread.start_new_thread(self.HandleRequest, (f,))  # Make this pass-by-value
#        self.HandleRequest(self.frame)

    def RegisterHandler(self, func_id, paramstr, fun):
        self.handlers[func_id] = (paramstr, fun)


    def SendWithCallback(self, funcID, data, fun, raiseOnError=True):
        logging.debug("SendWithCallback")
        if(fun):
            self.RegisterHandler(funcID, "B", fun)
            return self.SendFrameWithResponse(chr(funcID) + data + chr(3), raiseOnError=raiseOnError)
        else:
            return self.SendFrameWithResponse(chr(funcID) + data + chr(0), raiseOnError=raiseOnError)

    def bitfieldToList(self, bf):
        l = []
        for i in range(0, 8 * len(bf)):
            if((ord(bf[i / 8]) >> (i % 8)) & 1 == 1):
                l.append(i + 1)
        return l


        # Serial API Functions
    def SerialAPI_GetInitData(self):
        # verified
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_SERIAL_API_GET_INIT_DATA))
        (ver, cap, l) = unpack("3B", f[0:3])
        (chip_type, chip_version) = unpack("2B", f[3 + l:3 + l + 2])
        nodes = self.bitfieldToList(f[3:3 + l])
        return {"ver":ver, "cap": cap, "nodelist" : nodes, "chiptype" :  chip_type, "chipver" : chip_version}


    def SerialAPI_GetCapabilities(self):
        # verified
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_SERIAL_API_GET_CAPABILITIES))
        (ver, rev, man_id, man_prod_type, man_prod_type_id, supported) = unpack("!2B3H32s", f)
        return {"version":ver,
                "revision":rev,
                "man_id": man_id,
                "man_prod_type":man_prod_type,
                "man_prod_type_id":man_prod_type_id,
                "supported" :self.bitfieldToList(supported)}

    def SerialAPI_Softreset(self):
        self.SendFrame(pack("B", FUNC_ID_SERIAL_API_SOFT_RESET))

    def SerialAPI_StartWatchdog(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_WATCHDOG_START))

    def SerialAPI_StopWatchdog(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_WATCHDOG_STOP))

    def SerialAPI_SetTimeouts(self, timeout):
        f = self.SendFrame(pack("2B", FUNC_ID_SERIAL_API_SET_TIMEOUTS, timeout))
        return ord(f[0])

    def SerialAPI_ApplicationNodeInformation(self, deviceOptionsMask, genreic, specific, nodeParam):
        self.SendFrame(pack("5B", FUNC_ID_SERIAL_API_APPL_NODE_INFORMATION, deviceOptionsMask, genreic, specific, len(nodeParam)) + nodeParam)

    def SerialAPI_ApplicationSlaveNodeInformation(self, destNode, listening, generic, specific, nodeParam):
        self.SendFrame(pack("6B", FUNC_ID_SERIAL_API_APPL_SLAVE_NODE_INFORMATION, destNode,
                            listening, generic, specific, len(nodeParam)) + nodeParam)

    def SerialAPI_GetRandom(self, num):
        f = self.SendFrameWithResponse(pack("2B",FUNC_ID_ZW_GET_RANDOM, num))
        return f[2:]

    # Z-Wave Basis API
    def ZW_ExploreRequestInclusion(self):
        f = self.SendRFrameWithResponse(pack("B", FUNC_ID_ZW_EXPLORE_REQUEST_INCLUSION))
        return ord(f[0])
    def ZW_GetProtocolStatus(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_PROTOCOL_STATUS))
        return ord(f[0])

    def ZW_Random(self):
        f = self.SendFrameWithResponse(pack("B", 0x1D))
        return ord(f[0])

    def ZW_RFPowerLevelSet(self, level):
        f = self.SendFrameWithResponse(pack("2B", FUNC_ID_ZW_RF_POWER_LEVEL_SET, level))
        return ord(f[0])

    def ZW_RFPowerLevelGet(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_RF_POWER_LEVEL_GET))
        return ord(f[0])

    def ZW_RequestNetWorkUpdate(self, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_REQUEST_NETWORK_UPDATE, "", fun)
        return ord(f[0])

    def ZW_RFPowerlevelRediscoverySet(self, level):
        self.SendFrame(pack("2B", 0x1E, level))

    def ZW_SendNodeInformation(self, destNode, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_NODE_INFORMATION, pack("2B", destNode, txOptions), fun)
        return ord(f[0])

    def ZW_SendTestFrame(self, nodeID, powerlevel, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_TEST_FRAME, pack("2B", nodeID, powerlevel), fun)
        return ord(f[0])

    def ZW_SetExtIntLevel(self, intSrc, triggerLevel):
        self.SendFrame(pack("3B", FUNC_ID_ZW_SET_EXT_INT_LEVEL, intSrc, triggerLevel))

    def ZW_SetPromiscuousMode(self, state):
        self.SendFrame(pack("2B", FUNC_ID_ZW_SET_PROMISCUOUS_MODE, state))

    def ZW_SetRFReceiveMode(self, mode):
        f = self.SendFrameWithResponse(pack("2B", FUNC_ID_ZW_SET_RF_RECEIVE_MODE, mode))
        return ord(f[0])

    def ZW_Type_Library(self, mode):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_TYPE_LIBRARY))
        return ord(f[0])

    def ZW_Version(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_VERSION))
        if(f):
            return {"version": f[0:12], "library_type" :ord(f[12])}
        else:
            return None

    def ZW_WatchDogEnable(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_WATCHDOG_ENABLE))

    def ZW_WatchDogDisable(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_WATCHDOG_DISABLE))

    def ZW_WatchDogKick(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_WATCHDOG_KICK))

    # 5.3.3    Z-Wave Transport API

    # TEST OK
    def ZW_SendData(self, nodeID, data, txOptions=TRANSMIT_OPTION_ACK|TRANSMIT_OPTION_AUTO_ROUTE, fun=None):
        logging.debug("ZW_SendData")
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_DATA,
                                  chr(nodeID) + chr(len(data)) + data + chr(txOptions), fun)
        if(ord(f[0])==0):
            logging.warning("SendData returned false")
        return ord(f[0])

    def ZW_SendData_Bridge(self, srcNodeID, destNodeID, data, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_DATA_BRIDGE,
                                  pack("3B", srcNodeID, destNodeID, len(data)) + data + pack("5B", txOptions, 0, 0, 0, 0),
                                  fun,
                                  raiseOnError=False)
        return ord(f[0]) if f else 0

    def ZW_SendDataMeta_Bridge(self, srcNodeID, destNodeID, data, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_DATA_META_BRIDGE,
                                  pack("3B", srcNodeID, destNodeID, len(data)) + data + pack("5B", txOptions, 0, 0, 0, 0), fun)
        return ord(f[0])

    def  ZW_SendDataMulti(self, nodelist, data, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_DATA_MULTI,
                                  chr(len(nodelist)) + "".join(map(chr, nodelist)) +
                                  chr(len(data)) + data + chr(txOptions), fun)
        return ord(f[0])


    def  ZW_SendDataMulti_Bridge(self, srcnode, nodelist, data, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_DATA_MULTI_BRIDGE,
                                  pack("2B", srcnode, len(nodelist)) + "".join(map(chr, nodelist)) +
                                  chr(len(data)) + data + chr(txOptions), fun)
        return ord(f[0])

    def ZW_SendDataAbort(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_SEND_DATA_ABORT))

    # 5.3.7    Z-Wave Memory API
    def  MemoryGetID(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_MEMORY_GET_ID))
        (homeID, nodeID) = unpack("!IB", f)
        return {"homeid": homeID, "nodeid": nodeID}

    def MemoryGetByte(self, offset):
        f = self.SendFrameWithResponse(pack("!BH", FUNC_ID_MEMORY_GET_BYTE, offset))
        return ord(f[0])

    def MemoryPutByte(self, offset, data):
        f = self.SendFrameWithResponse(pack("!BHB", FUNC_ID_MEMORY_PUT_BYTE, offset, data))
        return ord(f[0])

    def MemoryGetBuffer(self, offset, length):
        return self.SendFrameWithResponse(pack("!BHB", FUNC_ID_MEMORY_GET_BUFFER, offset, length))

    def  MemoryPutBuffer(self, offset, buf, fun):
        f = self.SendWithCallback(FUNC_ID_MEMORY_PUT_BUFFER, pack("!2H", offset, len(buf)) + buf, fun)
        return ord(f[0])

    def  ZW_SetSleepMode(self, mode, intEnable):
        self.SendFrame(pack("3B", FUNC_ID_ZW_SET_SLEEP_MODE, mode, intEnable))

    # 5.4 Z-Wave Controller API
    def ZW_AddNodeToNetwork(self, mode, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_ZW_ADD_NODE_TO_NETWORK, "3B", fun)
        self.SendFrame(pack("3B", FUNC_ID_ZW_ADD_NODE_TO_NETWORK, mode, ii))

    def ZW_AreNodesNeighbours(self, nodeA, nodeB):
        f = self.SendFrameWithResponse(pack("3B", FUNC_ID_ZW_ARE_NODES_NEIGHBOURS, nodeA, nodeA))
        return ord(f[0])

    def ZW_AssignReturnRoute(self, bSrcNodeID, bDstNodeID, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_ASSIGN_RETURN_ROUTE, pack("2B", bSrcNodeID, bDstNodeID), fun)
        return ord(f[0])

    def ZW_AssignSUCReturnRoute(self, bSrcNodeID, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_ASSIGN_SUC_RETURN_ROUTE, pack("B", bSrcNodeID), fun)
        return ord(f[0])

    def ZW_ControllerChange(self, mode, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_ZW_CONTROLLER_CHANGE, "3B", fun)
        self.SendFrame(pack("3B", FUNC_ID_ZW_CONTROLLER_CHANGE, mode, ii))

    def ZW_DeleteReturnRoute(self, node, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_DELETE_RETURN_ROUTE, pack("B", node), fun)
        return ord(f[0])

    def ZW_DeleteSUCReturnRoute(self, node, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_DELETE_SUC_RETURN_ROUTE, pack("B", node), fun)
        return ord(f[0])

    def ZW_GetControllerCapabilities(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_CONTROLLER_CAPABILITIES))
        return ord(f[0])

    def ZW_GetNeighborCount(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_NEIGHBOR_COUNT))
        return ord(f[0])

    def ZW_GetNodeProtocolInfo(self, node):
        return self.SendFrameWithResponse(pack("2B", FUNC_ID_ZW_GET_NODE_PROTOCOL_INFO, node))

    def ZW_GetRoutingInfo(self, bNodeID, bRemove):
        # Documentation is uncler!!!!
        return self.SendFrameWithResponse(pack("3B", FUNC_ID_GET_ROUTING_TABLE_LINE, bNodeID, bRemove))

    def ZW_GetSUCNodeID(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_SUC_NODE_ID))
        return ord(f[0])

    def ZW_isFailedNode(self, node):
        f = self.SendFrameWithResponse(pack("2B", FUNC_ID_ZW_IS_FAILED_NODE_ID, node))
        return ord(f[0])

    def ZW_IsPrimaryCtrl(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_IS_PRIMARY_CTRL))
        return ord(f[0])

    def ZW_RemoveFailedNodeID(self, NodeID, bNormalPower, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_REMOVE_FAILED_NODE_ID, pack("3B", NodeID, bNormalPower), fun)
        return ord(f[0])

    def ZW_ReplaceFailedNode(self, NodeID, bNormalPower, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_REPLACE_FAILED_NODE, pack("3B", NodeID, bNormalPower), fun)
        return ord(f[0])

    def ZW_RemoveNodeFromNetwork(self, mode, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_ZW_REMOVE_NODE_FROM_NETWORK, "2B", fun)
        self.SendFrame(pack("3B", FUNC_ID_ZW_REMOVE_NODE_FROM_NETWORK, mode, ii))

    def ZW_ReplicationReceiveComplete(self):
        self.SendFrame(pack("B", FUNC_ID_ZW_REPLICATION_COMMAND_COMPLETE));

    def ZW_ReplicationSend(self, destNodeID, data, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_REPLICATION_SEND_DATA,
                                  chr(destNodeID) + chr(len(data)) + data + chr(txOptions), fun)
        return ord(f[0])

    def ZW_SetRoutingMAX(self, max_attempts):
        self.SendFrame(pack("2B", FUNC_ID_ZW_SET_ROUTING_MAX, max_attempts));
        # f = self.SendFrameWithResponse(pack("2B",FUNC_ID_ZW_SET_ROUTING_MAX,max_attempts))
        # return ord(f[0])

    def ZW_RequestNodeInfo(self, nodeID):
        f = self.SendFrameWithResponse(pack("2B", FUNC_ID_ZW_REQUEST_NODE_INFO, nodeID))
        return ord(f[0])

    def ZW_RequestNodeNeighborUpdate(self, node, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_ZW_REQUEST_NODE_NEIGHBOR_UPDATE, "B", fun)
        self.SendFrame(pack("3B", FUNC_ID_ZW_REQUEST_NODE_NEIGHBOR_UPDATE, node, ii))

    def  ZW_SendSUCID(self, node, txOption, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_SUC_ID, pack("2B", node, txOption) , fun)
        return ord(f[0])

    def ZW_SetDefault(self, fun):
        self.RegisterHandler(FUNC_ID_ZW_SET_DEFAULT, "", fun)
        self.SendFrame(pack("B", FUNC_ID_ZW_SET_DEFAULT))

    def ZW_SetLearnMode(self, mode, fun):
        ii = 0
        if(fun): ii = 3
        if self.is_controller: s = '3B'
        else: s = '2B'
        self.RegisterHandler(FUNC_ID_ZW_SET_LEARN_MODE, s, fun)
        self.SendFrame(pack('3B', FUNC_ID_ZW_SET_LEARN_MODE, mode, ii))

    def ZW_SetSUCNodeID(self, nodeID, SUCState, bTxOption, capabilities, fun=None):
        f = self.SendWithCallback(FUNC_ID_ZW_SET_SUC_NODE_ID, pack("4B", nodeID, SUCState, bTxOption, capabilities), fun)
        return ord(f[0])

    def ZW_EnableSUC(self, state, capabilities):
        f = self.SendFrameWithResponse(pack("3B", FUNC_ID_ZW_ENABLE_SUC, state, capabilities))
        return ord(f[0])

    def ZW_CreateNewPrimaryCtrl(self, mode, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_ZW_CREATE_NEW_PRIMARY, "7B", fun)
        self.SendFrame(pack("3B", FUNC_ID_ZW_CREATE_NEW_PRIMARY, mode, ii))

    # 5.6    Z-Wave Bridge Controller API
    def ZW_SendSlaveNodeInformation(self, srcNode, destNode, txOptions, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_SEND_SLAVE_NODE_INFORMATION, pack("3B", srcNode, destNode, txOptions) , fun)
        return ord(f[0]) if f else 0

    def ZW_SetSlaveLearnMode(self, node, mode, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_ZW_SET_SLAVE_LEARN_MODE, "3B", fun)
        f = self.SendFrameWithResponse(pack("4B", FUNC_ID_ZW_SET_SLAVE_LEARN_MODE, node, mode, ii))
        return ord(f[0]) if f else 0

    def ZW_IsVirtualNode(self, node):
        f = self.SendFrameWithResponse(pack("2B", FUNC_ID_ZW_IS_VIRTUAL_NODE, node))
        return ord(f[0])

    def ZW_GetVirtualNodes(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_VIRTUAL_NODES))
        return self.bitfieldToList(f)
    # 5.7    Z-Wave Installer Controller API

    def ZW_GetTransmitCount(self):
        f = self.SendFrameWithResponse(pack("B", FUNC_ID_GET_TX_COUNTER))
        return self.bitfieldToList(f)

    def ZW_ResetTransmitCount(self):
        self.SendFrame(pack("B", FUNC_ID_RESET_TX_COUNTER))

    def ZW_StoreNodeInfo(self, bNodeID, nodeInfo, fun):
        ii = 0
        if(fun): ii = 3
        self.RegisterHandler(FUNC_ID_STORE_NODEINFO, "", fun)
        f = self.SendFrameWithResponse(pack("2B", FUNC_ID_STORE_NODEINFO, bNodeID) + nodeInfo + chr(ii))
        return ord(f[0])

    def ZW_StoreHomeID(self, homeID, nodeID):
        f = self.SendFrameWithResponse(pack("!BIB", FUNC_ID_STORE_HOMEID, homeID, nodeID))
        return ord(f)

    # 5.8    Z-Wave Slave API
    def ZW_IsNodeWithinDirectRange(self, node):
        f = self.SendFrameWithResponse(pack("2B", 0x5D, node))
        return ord(f)

    def ZW_SetAutoProgramming(self):
        self.SendFrame(pack("1B", FUNC_ID_AUTO_PROGRAMMING))


    def ZW_RediscoveryNeeded(self, node, fun):
        f = self.SendWithCallback(FUNC_ID_ZW_REDISCOVERY_NEEDED, pack("B", node) , fun)
        return ord(f[0])

    def ZW_RequestNewRouteDestinations(self, dstlist, fun):
        f = self.SendWithCallback(0x5C, pack("5B", dstlist) , fun)
        return ord(f[0])



    # Handlers
    def SetApplicationCommandHandler(self, fun):
        self.RegisterHandler(FUNC_ID_APPLICATION_COMMAND_HANDLER, "3B", fun)

    def SetApplicationControllerUpdate(self, fun):
        self.RegisterHandler(FUNC_ID_ZW_APPLICATION_CONTROLLER_UPDATE, "3B", fun)

    def SetApplicationCommandHandlerBridge(self, fun):
        self.RegisterHandler(FUNC_ID_APPLICATION_COMMAND_HANDLER_BRIDGE, "4B", fun)

    def get_node_len(self):
        InitData = self.SerialAPI_GetInitData()
        print InitData
        return len(InitData['nodelist']) + 1

    def nodeInfo_to_dic(self, nodeInfo):
        nodeByts = []
        for x in range(0,12, 2):
            nodeByts.append(nodeInfo[x:x+2])
        return nodeByts

    def get_all_node_info(self, length):
        nodeInfo = []
        for x in range(1, length):
            ni = self.ZW_GetNodeProtocolInfo(x)
            ni = binascii.hexlify(ni)
            ni = self.nodeInfo_to_dic(ni)
            nodeInfo.append(ni)
        return nodeInfo



'''

FUNC_ID_ZW_GET_PROTOCOL_VERSION = 0x09
FUNC_ID_SERIAL_API_APPL_NODE_ROLE_TYPE = 0x0A
FUNC_ID_ZW_SEND_DATA_META = 0x18
FUNC_ID_ZW_RESERVED_SD = 0x19
FUNC_ID_ZW_RESERVED_SDM = 0x1A
FUNC_ID_ZW_RESERVED_SRI = 0x1B

FUNC_ID_SERIAL_API_GET_APPL_HOST_MEMORY_OFFSET = 0x25
FUNC_ID_DEBUG_OUTPUT = 0x26
FUNC_ID_CLOCK_SET = 0x30
FUNC_ID_CLOCK_GET = 0x31
FUNC_ID_CLOCK_CMP = 0x32
FUNC_ID_RTC_TIMER_CREATE = 0x33
FUNC_ID_RTC_TIMER_READ = 0x34
FUNC_ID_RTC_TIMER_DELETE = 0x35
FUNC_ID_RTC_TIMER_CALL = 0x36
FUNC_ID_ZW_SET_LEARN_NODE_STATE = 0x40
FUNC_ID_ZW_SET_DEFAULT = 0x42
FUNC_ID_ZW_NEW_CONTROLLER = 0x43
FUNC_ID_ZW_REPLICATION_SEND_DATA = 0x45

FUNC_ID_ZW_RESERVED_FN = 0x4E
FUNC_ID_ZW_RESERVED_AR = 0x4F
FUNC_ID_ZW_GET_ROUTING_MAX = 0x64
FUNC_ID_ZW_SET_ROUTING_MAX = 0x65
FUNC_ID_ZW_AES_ECB = 0x67
FUNC_ID_ZW_RESERVED_ASR = 0x58
FUNC_ID_ZW_EXPLORE_REQUEST_INCLUSION = 0x5E
FUNC_ID_TIMER_START = 0x70
FUNC_ID_TIMER_RESTART = 0x71
FUNC_ID_TIMER_CANCEL = 0x72
FUNC_ID_TIMER_CALL = 0x73
FUNC_ID_LOCK_ROUTE_RESPONSE = 0x90
FUNC_ID_ZW_SEND_DATA_ROUTE_DEMO = 0x91
FUNC_ID_SERIAL_API_TEST = 0x95
FUNC_ID_APPLICATION_SLAVE_COMMAND_HANDLER = 0xA1
FUNC_ID_ZW_SEND_SLAVE_NODE_INFORMATION = 0xA2
FUNC_ID_ZW_SEND_SLAVE_DATA = 0xA3
FUNC_ID_ZW_RESERVED_SSD = 0xA7
FUNC_ID_APPLICATION_COMMAND_HANDLER_BRIDGE = 0xA8
FUNC_ID_PWR_SETSTOPMODE = 0xB0
FUNC_ID_PWR_CLK_PD = 0xB1
FUNC_ID_PWR_CLK_PUP = 0xB2
FUNC_ID_PWR_SELECT_CLK = 0xB3
FUNC_ID_ZW_SET_WUT_TIMEOUT = 0xB4
FUNC_ID_ZW_IS_WUT_KICKED = 0xB5

FUNC_ID_PROMISCUOUS_APPLICATION_COMMAND_HANDLER = 0xD1
'''
class TypeDetector(threading.Thread):
    '''
    This thread queries the SerialAPI to detect target type (controller or slave)
    and stores the result in the ZWApi, then it terminates
    '''

    def __init__(self, ZWApi):
        threading.Thread.__init__(self)
        self.ZWApi = ZWApi
        self.start()

    ZW_LIB_CONTROLLER_STATIC = 0x01
    ZW_LIB_CONTROLLER = 0x02
    ZW_LIB_SLAVE_ENHANCED = 0x03
    ZW_LIB_SLAVE = 0x04
    ZW_LIB_INSTALLER = 0x05
    ZW_LIB_SLAVE_ROUTING = 0x06
    ZW_LIB_CONTROLLER_BRIDGE = 0x07
    ZW_LIB_DUT = 0x08
    ZW_LIB_AVREMOTE = 0x0A
    ZW_LIB_AVDEVICE = 0x0B

    def run(self):
        res = self.ZWApi.ZW_Version()
        self.ZWApi.library_type = res['library_type']
        self.ZWApi.is_controller = (self.ZWApi.library_type == ZW_LIB_CONTROLLER or
                                    self.ZWApi.library_type == ZW_LIB_CONTROLLER_STATIC or
                                    self.ZWApi.library_type == ZW_LIB_CONTROLLER_BRIDGE)
        # if self.ZWApi.is_controller: print 'Controller-type Z-Wave library detected.'
        # else: print 'Slave-type Z-Wave library detected.'

#***************** test ***************************

class MyHandler(xml.sax.ContentHandler):

    def __init__(self):
        self._charBuffer = []
        self._result = []

    def parse(self, f):
        xml.sax.parse(f, self)
        return self._result



def RandomCB(status, random):
    print "Random Status" , status
    print map(ord(random))

def setDefaultDone(data):
    print "Reset verified"

def print_version(zw):
    print zw.SerialAPI_GetInitData()

def save_node_info_csv(data):
    filepath = open("/www/data/ima/node_info.csv", "w")
    for i in range(len(data)):
        for x in range(len(data[i])):
            filepath.write(str(data[i][x])+',')
        filepath.write("\n")

    filepath.close()

if __name__ == '__main__':
    zw = ZWapi("/dev/ttyS0")
    nodeByts = []
    size = zw.get_node_len()

    ni = zw.get_all_node_info(size)

    print ni
    save_node_info_csv(ni)


    zw.stop()
    print "Exit"
    #    print zw.SendFrameWithResponse(pack("B", FUNC_ID_ZW_GET_VERSION))
    #    print zw.SerialAPI_GetCapabilities()
    #    zw.ZW_AddNodeToNetwork(02, None)
    #    print zw.SerialAPI_GetRandom(10,RandomCB)
    #    print zw.ZW_SetDefault(setDefaultDone)
