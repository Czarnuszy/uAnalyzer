'''
Created on May 1, 2012

@author: aes
'''
import socket
from struct import *
import random
import array
import ipaddr
from pyzwave.ZW_classcmd import *
import sys
if(sys.platform=="win32"):
    from getifaddr_win32 import get_network_interfaces
    from getifaddr_win32 import get_intarface_by_name    
else:
    from getifaddr import get_network_interfaces
    from getifaddr import get_intarface_by_name    
    
import threading


ZIP_PACKET_FLAGS1_ZW_CMD_INCL = 0x40

def single_request(s,family):
    s.settimeout(1.0)
    
    ips = []
    seq =random.randint(0,0xff)        
    req = pack("12B",
                COMMAND_CLASS_ZIP,
                COMMAND_ZIP_PACKET,
                0,
                ZIP_PACKET_FLAGS1_ZW_CMD_INCL,
                seq & 0xFF,0,0,
                COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY,NODE_INFO_CACHED_GET,seq,0xF,0)

    if(family==socket.AF_INET6):
        s.sendto(req,('ff02::2',4123))
    else:        
        s.sendto(req,('<broadcast>',4123))

    l = []
    try:
        while True:
            (pkt,src) = s.recvfrom(80);    
            if(len(pkt) > 8 and ord(pkt[7])==COMMAND_CLASS_NETWORK_MANAGEMENT_PROXY and 
               ord(pkt[8])==NODE_INFO_CACHED_REPORT ):
                l.append(src[0])
    except:
        return l
        


def getServices(hostif,family=socket.AF_INET6):
    '''
    Get all Z-wave services assosiated with a given interface
    '''
    ni = get_intarface_by_name(hostif)
    ips = []
    if(family in ni.addresses):
        for a in ni.addresses[family]:
            ip = ipaddr.IPAddress(a);
            if (not ip.is_link_local and not ip.is_loopback):
                s = socket.socket(family,socket.SOCK_DGRAM)
                s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)            
                s.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST,1)
                if(sys.platform!="win32" and family==socket.AF_INET6):
                    ifn = pack("I", ni.index)
                    s.setsockopt(socket.IPPROTO_IPV6, socket.IPV6_MULTICAST_IF, ifn)
 
                s.bind((a,0))
                i = single_request(s,family)
                if (i):
                    ips = ips + i
                    return ips
                s.close()
    else:
        return []
    return ips
        

def get_all_services(families=[socket.AF_INET6, socket.AF_INET]):
    global l
    l=[]
    def add_single(name,family):
        global l
        s = getServices(name,family)
        #print s
        l = l + s
    
    ts = []
    for ni in get_network_interfaces():
        for f in families:
            t = threading.Thread(target=add_single, kwargs={"name":ni.name,"family":f})
            t.start()
            ts.append(t)
            import time
            time.sleep(0.25)        

    for t in ts:
        t.join(2)

    return list(set(l))

if __name__ == '__main__':
    print "Z-Wave services " ,get_all_services()
