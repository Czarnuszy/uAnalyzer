"""
Some routines for retrieving the addresses from the local
network config.
"""
  
import itertools
import inspect
import ctypes
from ctypes import Structure,POINTER
from ctypes.wintypes import DWORD, BYTE, WCHAR, BOOL,ULONG
from socket import AF_INET, AF_INET6
from ctypes.wintypes import UINT
# from mprapi.h
MAX_INTERFACE_NAME_LEN = 2**8
  
# from iprtrmib.h
MAXLEN_PHYSADDR = 2**3
MAXLEN_IFDESCR = 2**8
  
# from error.h
NO_ERROR = 0
ERROR_INSUFFICIENT_BUFFER = 122
ERROR_BUFFER_OVERFLOW = 111
ERROR_NO_DATA = 232
ERROR_INVALID_PARAMETER = 87
ERROR_NOT_SUPPORTED = 50
  
# from iptypes.h
MAX_ADAPTER_ADDRESS_LENGTH = 8
MAX_DHCPV6_DUID_LENGTH = 130
  
  
class SOCKADDR(ctypes.Structure):
    _fields_ = (
        ('family', ctypes.c_ushort),
        ('data', ctypes.c_byte*26),
        )
LPSOCKADDR = ctypes.POINTER(SOCKADDR)
  
class SOCKET_ADDRESS(ctypes.Structure):
    _fields_ = [
        ('address', LPSOCKADDR),
        ('length', ctypes.c_int),
        ]
  
class _IP_ADAPTER_ADDRESSES_METRIC(ctypes.Structure):
    _fields_ = [
        ('length', ctypes.c_ulong),
        ('interface_index', DWORD),
        ]
  
class _IP_ADAPTER_ADDRESSES_U1(ctypes.Union):
    _fields_ = [
        ('alignment', ctypes.c_ulonglong),
        ('metric', _IP_ADAPTER_ADDRESSES_METRIC),
        ]
  
class IP_ADAPTER_ADDRESSES(ctypes.Structure):
    pass#_anonymous_ = ('u',)
  
LP_IP_ADAPTER_ADDRESSES = ctypes.POINTER(IP_ADAPTER_ADDRESSES)

class IP_ADAPTER_XXX_ADDRESSES(Structure):
    pass#_anonymous_ = ('u',)

PIP_ADAPTER_XXX_ADDRESSES = ctypes.POINTER(IP_ADAPTER_XXX_ADDRESSES)

IP_ADAPTER_XXX_ADDRESSES._fields_ = [
        ('Length', ULONG),
        ('Flags', DWORD),        
        ('Next', PIP_ADAPTER_XXX_ADDRESSES),
        ('Address', SOCKET_ADDRESS),
        ('PrefixOrigin', UINT),
        ('SuffixOrigin', UINT),
        ('DadState', UINT),
        ('ValidLifetime', ULONG),
        ('PreferredLifetime', ULONG),
        ('LeaseLifetime', ULONG)]


# for now, just use void * for pointers to unused structures
#PIP_ADAPTER_UNICAST_ADDRESS = ctypes.c_void_p
PIP_ADAPTER_UNICAST_ADDRESS = PIP_ADAPTER_XXX_ADDRESSES
PIP_ADAPTER_ANYCAST_ADDRESS = ctypes.c_void_p
PIP_ADAPTER_MULTICAST_ADDRESS = ctypes.c_void_p
PIP_ADAPTER_DNS_SERVER_ADDRESS = ctypes.c_void_p
PIP_ADAPTER_PREFIX = ctypes.c_void_p
PIP_ADAPTER_WINS_SERVER_ADDRESS_LH = ctypes.c_void_p
PIP_ADAPTER_GATEWAY_ADDRESS_LH = ctypes.c_void_p
PIP_ADAPTER_DNS_SUFFIX = ctypes.c_void_p
  
IF_OPER_STATUS = ctypes.c_uint # this is an enum, consider http://code.activestate.com/recipes/576415/
IF_LUID = ctypes.c_uint64
  
NET_IF_COMPARTMENT_ID = ctypes.c_uint32
GUID = ctypes.c_byte*16
NET_IF_NETWORK_GUID = GUID
NET_IF_CONNECTION_TYPE = ctypes.c_uint # enum
TUNNEL_TYPE = ctypes.c_uint # enum
  
IP_ADAPTER_ADDRESSES._fields_ = [
    #('u', _IP_ADAPTER_ADDRESSES_U1),
        ('length', ctypes.c_ulong),
        ('interface_index', DWORD),
    ('next', LP_IP_ADAPTER_ADDRESSES),
    ('adapter_name', ctypes.c_char_p),
    ('first_unicast_address', PIP_ADAPTER_UNICAST_ADDRESS),
    ('first_anycast_address', PIP_ADAPTER_ANYCAST_ADDRESS),
    ('first_multicast_address', PIP_ADAPTER_MULTICAST_ADDRESS),
    ('first_dns_server_address', PIP_ADAPTER_DNS_SERVER_ADDRESS),
    ('dns_suffix', ctypes.c_wchar_p),
    ('description', ctypes.c_wchar_p),
    ('friendly_name', ctypes.c_wchar_p),
    ('byte', BYTE*MAX_ADAPTER_ADDRESS_LENGTH),
    ('physical_address_length', DWORD),
    ('flags', DWORD),
    ('mtu', DWORD),
    ('interface_type', DWORD),
    ('oper_status', IF_OPER_STATUS),
    ('ipv6_interface_index', DWORD),
    ('zone_indices', DWORD),
    ('first_prefix', PIP_ADAPTER_PREFIX),
    ('transmit_link_speed', ctypes.c_uint64),
    ('receive_link_speed', ctypes.c_uint64),
    ('first_wins_server_address', PIP_ADAPTER_WINS_SERVER_ADDRESS_LH),
    ('first_gateway_address', PIP_ADAPTER_GATEWAY_ADDRESS_LH),
    ('ipv4_metric', ctypes.c_ulong),
    ('ipv6_metric', ctypes.c_ulong),
    ('luid', IF_LUID),
    ('dhcpv4_server', SOCKET_ADDRESS),
    ('compartment_id', NET_IF_COMPARTMENT_ID),
    ('network_guid', NET_IF_NETWORK_GUID),
    ('connection_type', NET_IF_CONNECTION_TYPE),
    ('tunnel_type', TUNNEL_TYPE),
    ('dhcpv6_server', SOCKET_ADDRESS),
    ('dhcpv6_client_duid', ctypes.c_byte*MAX_DHCPV6_DUID_LENGTH),
    ('dhcpv6_client_duid_length', ctypes.c_ulong),
    ('dhcpv6_iaid', ctypes.c_ulong),
    ('first_dns_suffix', PIP_ADAPTER_DNS_SUFFIX),
    ]
  
# define some parameters to the API Functions
ip_helper = ctypes.windll.iphlpapi

ip_helper.GetAdaptersAddresses.argtypes = [
    ctypes.c_ulong,
    ctypes.c_ulong,
    ctypes.c_void_p,
    ctypes.POINTER(IP_ADAPTER_ADDRESSES),
    ctypes.POINTER(ctypes.c_ulong),
    ]
ip_helper.GetAdaptersAddresses.restype = ctypes.c_ulong
  
def GetAdaptersAddresses():
    size = ctypes.c_ulong()
    res = ip_helper.GetAdaptersAddresses(0,0,None, None,size)
    if res != ERROR_BUFFER_OVERFLOW:
        raise RuntimeError("Error getting structure length (%d)" % res)
    pointer_type = ctypes.POINTER(IP_ADAPTER_ADDRESSES)
    buffer = ctypes.create_string_buffer(size.value)
    struct_p = ctypes.cast(buffer, pointer_type)
    res = ip_helper.GetAdaptersAddresses(0,0,None, struct_p, size)
    if res != NO_ERROR:
        raise RuntimeError("Error retrieving table (%d)" % res)
    while struct_p:
        yield struct_p.contents
        struct_p = struct_p.contents.next



def inet_ntop(family,data):
    if(family== AF_INET): 
        return "%i.%i.%i.%i" % (data[0] & 0xFF,data[1]& 0xFF,data[2]& 0xFF,data[3]& 0xFF)
    elif(family== AF_INET6):
        skip=False
        s=""
        for i in range(0,16,2):
            a = ((data[i] & 0xFF) << 8) + (data[i + 1] & 0xFF);
            if(a==0):
                if(not skip and i>0):
                    s +=":"
                    skip=True
            else:
                if(i>0): s+=":"
                s+="%x" % (a)
        return s
    return ""



class NetworkInterface(object):
    def __init__(self, name,index):
        self.name = name
        self.index = index
        self.addresses = {AF_INET:[],AF_INET6:[]}

    def __str__(self):
        return "%s [index=%d, IPv4=%s, IPv6=%s]" % (
            self.name, self.index,
            self.addresses.get(AF_INET),
            self.addresses.get(AF_INET6))

def get_network_interfaces():
    retval = []

    for i in GetAdaptersAddresses():
        name = i.friendly_name
        ni = NetworkInterface(name,i.ipv6_interface_index)

        a = i.first_unicast_address
        while(a):
            addr = a.contents.Address.address.contents
            ##TODO does this also work on 64bit windows?
            if(addr.family == AF_INET):
                ni.addresses[addr.family].append(inet_ntop(addr.family, addr.data[2:]))
            if(addr.family == AF_INET6 and a.contents.ValidLifetime != 0):
                ni.addresses[addr.family].append(inet_ntop(addr.family, addr.data[6:]))
            a = a.contents.Next
        retval.append(ni)
    return retval

def get_host_ip_addresses(family):
    s = get_network_interfaces()
    l = []
    for x in s:
        l.extend(x.addresses[family])
    return l
    
def get_intarface_by_name(name):
    for ni in get_network_interfaces():
        if(ni.name == name):
            return ni
    return None



if __name__ == "__main__":
    for ni in  get_network_interfaces():
        print ni.addresses
