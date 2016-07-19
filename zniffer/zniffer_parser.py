import os, sys, inspect
import mysql.connector
from mysql.connector import errorcode
import json

currentdir = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))
parentdir = os.path.dirname(currentdir)
common_lib_dir="common"

sys.path.insert(0,common_lib_dir)

from y_bintools import *
from y_timestamp import *
from zniffer_globals import  *
from state_machine import *
from y_dbglog import * 

import binascii

CRASHDBG=DISABLE #overrides

CSV_DELIM=","

def empty():
    return



class ZnifferFrame(object):

    def __init__(self):
        self.rssi=INVALID
        self.len=INVALID
        self.time=INVALID
        self.wu_beam_counter=INVALID
        self.channel=INVALID
        self.speed=INVALID
        self.frequency=INVALID
        self.home_id = INVALID
        self.source_id = INVALID
        self.destination_id = INVALID
        self.data_length=INVALID
        self.cmd_length=INVALID
        self.ts=INVALID
        self.raw_payload=""
        self.transport_payload_idx= INVALID
        self.zwave_ts=ZWaveTimeStamp(datetime.datetime.utcnow()) #FIXME: this is zniffer app's timestamp. should we use self.ts from uzb zniffer instead?
        self.routed = INVALID
        self.ack = INVALID
        self.low_power = INVALID
        self.speed_modified= 0
        self.header_type=INVALID
        self.suc_present=INVALID
        self.wsb1000=INVALID
        self.swb250=INVALID
        self.reserved_2=INVALID
        self.properties_3=INVALID
        self.sequence_number=INVALID
        self.hops_count=INVALID
        self.repeaters_count=INVALID
        self.hops_nodes=[]
        self.route = INVALID
        self.command = INVALID



    def get_csv_header(self):
        s=""

        return s

    def header_type_tostring(self):
        s=""
        if self.low_power !=0:
            s+="Low Power "
        if self.routed !=0:
            s+="Routed "
        if self.header_type == HEADER_TYPE_SINGLECAST:
            s+="Singlecast"
        elif self.header_type == HEADER_TYPE_ACK:
            s+="Ack"
        elif self.header_type == HEADER_TYPE_EXPLORER:
            s+="Explorer"
        else:
            s+=" unsupported yet: %02d"%self.header_type
        return s

    def to_csv(self):
        #MM Changes to CSV file
        s=""
        route_comunicats_tab = ["Ack", "No Ack from: "]
        if self.transport_payload_idx !=INVALID:
            hop = "%02d" % self.hops_count
            count = "%03d" % self.repeaters_count
            header = "%03d" % self.properties_3
            repeater = "%s" % '-'.join(map(str, self.hops_nodes))
            source = "%03d" % self.source_id

            s+=self.zwave_ts.get_utc_timestamp().strftime('%Y-%m-%d %H:%M:%S.%f')[:-3] +CSV_DELIM
            s+=("%02d"% self.rssi) +CSV_DELIM
            s+=("%08X"% self.home_id)+CSV_DELIM
            s+=("%03d"% self.source_id)+CSV_DELIM
            s+=("%s"% '-'.join(map(str, self.hops_nodes)))+CSV_DELIM
            s+=("%03d"% self.destination_id)+CSV_DELIM
            s+=("%s"%self.header_type_tostring())+CSV_DELIM
            s+=bytes_array_to_hex_string(bin_to_array(self.raw_payload[self.transport_payload_idx:]))+CSV_DELIM
            s+=("%02d"% self.sequence_number)+CSV_DELIM
            s+=("%02d"% self.hops_count)+CSV_DELIM
            s+=("%03d"% self.repeaters_count)+CSV_DELIM
            s+=("%03d"% self.properties_3)+CSV_DELIM

            #parsing
            if hop == "-1":
                self.route = ">"
                self.command = " "

            elif hop == "01":
                if count == "000" and header == "000":
                    self.route = "> {} -".format(repeater)
                    self.command = " "

                elif count == "001" and header == "000":
                    self.route = "-" + repeater + ">"
                    self.command = " "

                elif count == "000" and header == "003":
                    self.route = ">" + repeater + "-"
                    self.command = route_comunicats_tab[0]

                elif count == "015" and header == "003":
                    self.route = "-" + repeater + ">"
                    self.command = route_comunicats_tab[0]

                elif count == "015" and header == "021":
                    self.route = "x" + repeater + ">"
                    self.command = route_comunicats_tab[1] + source
                else:
                    self.route = " "
                    self.command = " "

            elif hop == "02":
                if count == "000" and header == "000":
                    self.route = ">" + repeater + "-"
                    self.command = " "

                elif count == "01" and header == "000":
                    self.route = "-" + repeater[0:-4] + ">" + repeater[-3:]
                    self.command = " "

                elif count == "002" and header == "000":
                    self.route = "-" + repeater + ">"
                    self.command = " "

                elif count == "001" and header == "003":
                    self.route = ">" + repeater[-3:] + "-" + repeater[0:-4] + "-"
                    self.command = route_comunicats_tab[0]

                elif count == "000" and header == "003":
                    self.route = "-" + repeater[-3:] + ">" + repeater[0:-4] + "-"
                    self.command = route_comunicats_tab[0]

                elif count == "015" and header == "003":
                    self.route = "-" + repeater[-3:] + "-" + repeater[0:-4] + ">"
                    self.command = route_comunicats_tab[0]

                elif count == "015" and header == "021":
                    self.route = "-" + repeater[-3:] + "-" + repeater[0:-4] + "x"
                    self.command = route_comunicats_tab[1] + source

                elif count == "000" and header == "037":
                    self.route = "x" + repeater[-3:] + "-" + repeater[0:-4] + "-"
                    self.command = route_comunicats_tab[1] + source

                elif count == "015" and header == "037":
                    self.route = "x" + repeater[-3:] + "-" + repeater[0:-4] + ">"
                    self.command = route_comunicats_tab[1] + source
                else:
                    self.route = " "
                    self.command = " "

            elif hop == "03":
                self.route = self.route
                self.command = " "

            elif hop == "04":
                self.route = self.route
                self.command = " "

            s += str(self.route) + CSV_DELIM
            s += str(self.command) + CSV_DELIM
            s+="\n"

        return s


class ZnifferSerialDataParser(object):
    def clean(self):
        self.serial_frames=[] 
        self.current_serial_frame=ZnifferFrame() 
        self.parsed_frames=[]

    def __init__(self, dbg=DISABLE):
        self.clean()
        self.m = StateMachine(dbg)
        self.m_is_inited=False
        self.CRASHDBG=(dbg, CRASHDBG)[CRASHDBG==ENABLE]




    def nibble_byte(self, serial_data):
        dbglog(self.CRASHDBG,"entering")
        b=None
        if len(serial_data) != 0:
            b=char_to_int(serial_data[0])
            dbglog(self.CRASHDBG, str(hex(b)))
            self.current_serial_frame.raw_payload+=serial_data[0]
            serial_data= serial_data[1:]
        return [b, serial_data]
        #dbglog(self.CRASHDBG, self.current_serial_frame)

    def save(self):
        dbglog(self.CRASHDBG,"")
        if len(self.current_serial_frame.raw_payload)!=0:
            self.serial_frames.append(self.current_serial_frame)
            self.current_serial_frame=ZnifferFrame()
            #print self.serial_frames
        
    def zw_4x_sof_hunt(self, serial_data):
        dbglog(self.CRASHDBG,"")
        self.save()
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.cmd_length=0
        if b == ZNIFFER_4X_DATA_FRAME_SOF:
            #dbglog(self.CRASHDBG, "DATA SOF")
            return ["TYPE", serial_data]
        elif b == ZNIFFER_4X_COMMAND_FRAME_SOF:
            #dbglog(self.CRASHDBG, "COMMAND SOF")
            return ["CMD_TYPE", serial_data]
        else:
            return ["SOF_HUNT", serial_data]
        
    def zw_4x_type(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]

        if b == ZNIFFER_4X_IS_SOF_MARKER:
            return ["TIME_STAMP_1", serial_data]
        elif b == ZNIFFER_4X_IS_SOF_WAKEUP_START_MARKER:
            return ["WAKE_UP_START_TIME_STAMP_1", serial_data]
        elif b == ZNIFFER_4X_IS_SOF_WAKEUP_STOP_MARKER:
            return ["WAKE_UP_STOP_TIME_STAMP_1", serial_data]
        else:
            #fixme
            return ["SOF_HUNT",serial_data]

    def zw_4x_ts_1(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.ts = (b <<8)
        return ["TIME_STAMP_2", serial_data]

    def zw_4x_wu_start_ts_1(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        
        self.current_serial_frame.ts = (b <<8)
        return ["WAKE_UP_START_TIME_STAMP_2", serial_data]

    def zw_4x_wu_stop_ts_1(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]

        self.current_serial_frame.ts = (b <<8)
        return ["WAKE_UP_STOP_TIME_STAMP_2", serial_data]

    def zw_4x_ts_2(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]

        self.current_serial_frame.ts |= (b)
        return ["SPEED_CHANNEL", serial_data]

    def zw_4x_wu_start_ts_2(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        
        self.current_serial_frame.ts = (b <<8)
        return ["WAKE_UP_START_SPEED_CHANNEL", serial_data]

    def zw_4x_wu_stop_ts_2(self, serial_data):     
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]

        self.current_serial_frame.ts = (b <<8)
        return ["WAKE_UP_STOP_RSSI", serial_data]

    def zw_4x_wu_stop_rssi(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]

        self.current_serial_frame.rssi=b
        return ["WAKE_UP_STOP_COUNT_1", serial_data]

    def zw_4x_wu_stop_count_1(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.wu_beam_counter=b<<8
        return ["WAKE_UP_STOP_COUNT_2", serial_data]

    def zw_4x_wu_stop_count_2(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.wu_beam_counter+=b
        return ["SOF_HUNT", serial_data]

    def zw_4x_speed_channel(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.channel= b>>ZNIFFER_4X_DATA_FRAME_CHANNEL_SHIFT
        self.current_serial_frame.speed = b & ZNIFFER_4X_DATA_FRAME_SPEED_MASK
        return ["CURRENT_FREQUENCY", serial_data]

    def zw_4x_wu_start_speed_channel(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.channel= b>>ZNIFFER_4X_DATA_FRAME_CHANNEL_SHIFT
        self.current_serial_frame.speed = b & ZNIFFER_4X_DATA_FRAME_SPEED_MASK
        return ["WAKE_UP_START_CURRENT_FREQUENCY", serial_data]

    def zw_4x_current_freq(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.frequency=b
        return ["RSSI", serial_data]

    def zw_4x_wu_start_current_freq(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.frequency=b
        return ["WAKE_UP_START_RSSI", serial_data]

    def zw_4x_rssi(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.rssi = b
        return ["DATA_MARKER", serial_data]

    def zw_4x_wu_start_rssi(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.rssi = b
        return ["WAKE_UP_START_BEAM_TYPE", serial_data]

    def zw_4x_data_marker(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        if b == ZNIFFER_4X_DATA_FRAME_SOF:
            return ["SOF_DATA", serial_data]
        elif self.current_serial_frame.rssi == ZNIFFER_4X_DATA_FRAME_SOF and b ==ZNIFFER_4X_IS_DATA_MARKER: # missing rssi
            self.current_serial_frame.rssi = 0
            return ["DATA_MARKER", serial_data] #FIXME
        elif self.current_serial_frame.rssi == ZNIFFER_4X_DATA_FRAME_SOF and b == ZNIFFER_4X_IS_WAKEUP_DATA_MARKER: # missing rssi
            self.current_serial_frame.rssi = 0
            return ["DATA_MARKER", serial_data] #FIXME
        else:
            return ["SOF_HUNT", serial_data]

    def zw_4x_sof_data(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        if b == ZNIFFER_4X_IS_DATA_MARKER:
            return ["DATA_LENGTH", serial_data]
        elif b == ZNIFFER_4X_IS_WAKEUP_DATA_MARKER:
            self.current_serial_frame.wu_beam_counter = 1
            return ["BEAM_TYPE", serial_data]
        else:
            return ["SOF_HUNT", serial_data]

    def zw_4x_data_length(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        if b == 0:
            return ["SOF_HUNT", serial_data]
        else:
            if self.current_serial_frame.data_length == INVALID:
                self.current_serial_frame.data_length = b
                dbglog(self.CRASHDBG, "new data_length!")
            elif b != self.current_serial_frame.data_length: #something wrong! transfer layer length differs from zniffer layer length, they should be equal!
                dbglog(self.CRASHDBG, "WARNING: transfer layer data length ="+str(b)+" differs from data length previously advertised by zniffer=", str(self.current_serial_frame.data_length))
                
            dbglog(self.CRASHDBG, "data_length: "+str(b))
            return ["DATA", serial_data]

    def zw_4x_data(self, serial_data): #getting to the transfer layer
        dbglog(self.CRASHDBG,"")
        if self.current_serial_frame.home_id == INVALID:
            return ["HOME_ID", serial_data]
        else: #already been there, parsed properties1 and frame header bits, routed bits, so now get destination id and so on
            return ["DESTINATION_ID", serial_data] 

    def zw_4x_destination_id(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.destination_id=b
        if self.current_serial_frame.routed != 0:
            return [ "ROUTING_PROPERTIES_3", serial_data]
        else: 
            return [ "TRANSPORT_PAYLOAD", serial_data]

    def zw_4x_routing_properties_3(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.properties_3 = b
        return [ "ROUTING_HOPS_REPEATERS_COUNT", serial_data]

    def zw_4x_routing_hops_repeaters_count(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.hops_count=(b & 0xf0)>>4
        self.current_serial_frame.repeaters_count=(b & 0x0f)
        return [ "ROUTING_HOPS_LIST", serial_data ]

    def zw_4x_routing_hops_list(self,serial_data):
        dbglog(self.CRASHDBG,"")
        for i in range (self.current_serial_frame.hops_count):
            [b, serial_data] = self.nibble_byte(serial_data)
            if b == None:
                return ["NO_MORE_PAYLOAD", serial_data]
            self.current_serial_frame.hops_nodes.append(b)
        return [ "TRANSPORT_PAYLOAD", serial_data]
            
    def zw_4x_transport_payload(self,serial_data): #consume blindly the transport payload
        dbglog(self.CRASHDBG,"")
        self.current_serial_frame.transport_payload_idx=len(self.current_serial_frame.raw_payload)-1
        read_len=self.current_serial_frame.data_length -(HOMEID_SZ + SRC_ID_SZ + P1_SZ + P2_SZ + DATA_LENGTH_SZ + DST_ID_SZ)
        if self.current_serial_frame.routed !=0:
            read_len-= (P3_SZ +HOPS_REPEATER_COUNT_SZ+ self.current_serial_frame.hops_count)

        for i in range (read_len):
            [b, serial_data] = self.nibble_byte(serial_data)

        return [ "SOF_HUNT", serial_data ]

    def zw_4x_home_id(self, serial_data): #getting to the transfer layer
        dbglog(self.CRASHDBG,"")
        self.current_serial_frame.home_id = 0 #required init, so below shifting works
        for i in range(HOMEID_SZ):
            [b, serial_data] = self.nibble_byte(serial_data)
            if b == None:
                return ["NO_MORE_PAYLOAD", serial_data]
            self.current_serial_frame.home_id = (self.current_serial_frame.home_id <<8) | b
        return ["SOURCE_ID", serial_data ]

    def zw_4x_source_id(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.source_id=b
        return [ "PROPERTIES_1", serial_data]

    def zw_4x_properties_1(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.routed=(b & 0x80)>>7
        self.current_serial_frame.ack=(b & 0x40)>>6
        self.current_serial_frame.low_power=(b & 0x20)>>5
        self.current_serial_frame.speed_modified=(b & 0x10) >> 4
        self.current_serial_frame.header_type=(b & 0x0f)
        return [ "PROPERTIES_2", serial_data]

    def zw_4x_properties_2(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]

        self.current_serial_frame.suc_present=(b & 0x80)>>7
        self.current_serial_frame.wsb1000=(b & 0x40)>>6
        self.current_serial_frame.swb250=(b & 0x20)>>5
        self.current_serial_frame.reserved_2=(b & 0x10) >> 4
        self.current_serial_frame.sequence_number=(b & 0xf)
        return [ "DATA_LENGTH", serial_data]

    def zw_4x_beam_type(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        return ["BEAM_DESTINATION", serial_data]

    def zw_4x_wu_start_beam_type(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        return ["WAKE_UP_START_BEAM_DESTINATION", serial_data]

    def zw_4x_beam_destination(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        for i in range (self.current_serial_frame.cmd_length):
            self.current_parsed_frame+=chr(b) #FIXME
        return ["SOF_HUNT", serial_data]

    def zw_4x_wu_start_beam_destination(self,serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        [b, serial_data] = self.nibble_byte(serial_data)
        return ["WAKE_UP_START_BEAM_VERSION", serial_data]

    def zw_4x_wu_start_beam_version(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        if b == 1 :        
            return ["WAKE_UP_START_HOME_ID_HASH", serial_data]
        else:
            for i in range(self.current_serial_frame.cmd_length):
                self.current_parsed_frame+=chr(b) #FIXME
            return ["SOF_HUNT", serial_data]

    def zw_4x_wu_start_home_id_hash(self, serial_data):
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        for i in range(self.current_serial_frame.cmd_length):
            self.current_parsed_frame+=chr(b) #FIXME
        return ["SOF_HUNT", serial_data]

    def zw_4x_cmd_type(self, serial_data): #zniffer command type. this is to configure zniffer / pure zniffer replies
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        if b in ZnifferCommandTypesEnum:
            return ["CMD_LENGTH", serial_data]
        else:         
            return ["SOF_HUNT", serial_data]

    def zw_4x_cmd_length(self,serial_data): #zniffer command length. this is to configure zniffer / pure zniffer replies
        dbglog(self.CRASHDBG,"")
        [b, serial_data] = self.nibble_byte(serial_data)
        if b == None:
            return ["NO_MORE_PAYLOAD", serial_data]
        self.current_serial_frame.cmd_length = b
        dbglog(self.CRASHDBG, "cmd_length "+str(self.current_serial_frame.cmd_length))
        if self.current_serial_frame.cmd_length == 0:
            return ["SOF_HUNT", serial_data]
        else:
            return ["CMD_DATA", serial_data]

    def zw_4x_cmd_data(self, serial_data): #zniffer command data. example: "zniffer, give me your list of supported regions"
        dbglog(self.CRASHDBG,"")
        for i in range(self.current_serial_frame.cmd_length):
            [b, serial_data] = self.nibble_byte(serial_data)
            if b == None:
                return ["NO_MORE_PAYLOAD", serial_data]
                #FIXME #add parsing of zniffer command data, such as supported regions (minor feature)
        return ["SOF_HUNT", serial_data]

    def zw_no_more_payload(self, serial_data):
        dbglog(self.CRASHDBG,"")

    def parse(self, serial_data):
        dbglog(self.CRASHDBG,"")
        if self.m_is_inited == False:
            self.m.add_state("SOF_HUNT", self.zw_4x_sof_hunt)
            self.m.add_state("NO_MORE_PAYLOAD", self.zw_no_more_payload, end_state=1)
            self.m.add_state("TYPE", self.zw_4x_type)
            self.m.add_state("TIME_STAMP_1", self.zw_4x_ts_1)
            self.m.add_state("TIME_STAMP_2", self.zw_4x_ts_2)
            self.m.add_state("WAKE_UP_START_TIME_STAMP_1", self.zw_4x_wu_start_ts_1)
            self.m.add_state("WAKE_UP_START_TIME_STAMP_2", self.zw_4x_wu_start_ts_2)
            self.m.add_state("WAKE_UP_STOP_TIME_STAMP_1", self.zw_4x_wu_stop_ts_1)
            self.m.add_state("WAKE_UP_STOP_TIME_STAMP_2", self.zw_4x_wu_stop_ts_2)
            self.m.add_state("SPEED_CHANNEL", self.zw_4x_speed_channel)
            self.m.add_state("WAKE_UP_START_SPEED_CHANNEL", self.zw_4x_wu_start_speed_channel)
            self.m.add_state("CURRENT_FREQUENCY", self.zw_4x_current_freq)
            self.m.add_state("WAKE_UP_START_CURRENT_FREQUENCY", self.zw_4x_wu_start_current_freq)
            self.m.add_state("RSSI", self.zw_4x_rssi)
            self.m.add_state("WAKE_UP_START_RSSI", self.zw_4x_wu_start_rssi)
            self.m.add_state("DATA_MARKER", self.zw_4x_data_marker)
            self.m.add_state("SOF_DATA", self.zw_4x_sof_data)
            self.m.add_state("DATA_LENGTH", self.zw_4x_data_length)
            self.m.add_state("DATA", self.zw_4x_data)
            self.m.add_state("HOME_ID", self.zw_4x_home_id)
            self.m.add_state("PROPERTIES_1", self.zw_4x_properties_1)
            self.m.add_state("PROPERTIES_2", self.zw_4x_properties_2)
            self.m.add_state("SOURCE_ID", self.zw_4x_source_id)
            self.m.add_state("DESTINATION_ID", self.zw_4x_destination_id)
            self.m.add_state("ROUTING_PROPERTIES_3", self.zw_4x_routing_properties_3)
            self.m.add_state("ROUTING_HOPS_REPEATERS_COUNT", self.zw_4x_routing_hops_repeaters_count)
            self.m.add_state("ROUTING_HOPS_LIST", self.zw_4x_routing_hops_list)
            self.m.add_state("TRANSPORT_PAYLOAD", self.zw_4x_transport_payload)
            self.m.add_state("BEAM_TYPE", self.zw_4x_beam_type)
            self.m.add_state("WAKE_UP_START_BEAM_TYPE", self.zw_4x_wu_start_beam_type )
            self.m.add_state("BEAM_DESTINATION", self.zw_4x_beam_destination )
            self.m.add_state("WAKE_UP_START_BEAM_DESTINATION", self.zw_4x_wu_start_beam_destination )
            self.m.add_state("WAKE_UP_START_BEAM_VERSION", self.zw_4x_wu_start_beam_version )
            self.m.add_state("WAKE_UP_START_HOME_ID_HASH", self.zw_4x_wu_start_home_id_hash )
            self.m.add_state("WAKE_UP_STOP_RSSI", self.zw_4x_wu_stop_rssi ) 
            self.m.add_state("WAKE_UP_STOP_COUNT_1", self.zw_4x_wu_stop_count_1 )
            self.m.add_state("WAKE_UP_STOP_COUNT_2", self.zw_4x_wu_stop_count_2 )
            self.m.add_state("CMD_TYPE", self.zw_4x_cmd_type )
            self.m.add_state("CMD_LENGTH", self.zw_4x_cmd_length ) 
            self.m.add_state("CMD_DATA", self.zw_4x_cmd_data )
            self.m_is_inited=True

        self.m.set_start("SOF_HUNT")
        self.m.run(serial_data)
        return self.serial_frames
