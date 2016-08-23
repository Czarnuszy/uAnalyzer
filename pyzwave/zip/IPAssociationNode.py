from pyzwave.serial.ZWBasicNode import ZWBasicNode, AppHandler
from pyzwave.zip.ZIPClient import ZIPClient
from pyzwave.ZW_classcmd import *
from struct import pack
from ipaddr import IPAddress
from pyzwave.Waiter import Waiter
import ipaddr


class IPAssociationNode(ZWBasicNode):
    
    def __init__(self, port):
        ZWBasicNode.__init__(self, port)
        self.ip = None
        self.zip_client = None
    
    def setIpAddress(self, ip):
        '''
        The node cannot infer its own IP address. Instead, it must be set
        via this method.
        Remaining class methods will not work until IP is set.
        '''
        self.ip = ip
        self.zip_client = ZIPClient(self.ip)
    
    def get_ipv6_address(self, ip):
        '''
        Return an IPv6 address from string or IPAddress object.
        If the address is ipv4, return a 6to4 mapped address.
        '''
        try:
            if ip.version == 4:
                ip = IPAddress('::ffff:'+str(ip), 6)
        except AttributeError:
            return self.get_ipv6_address(IPAddress(ip))
        return ip
            
    
    def setIpAssociation(self, res_ip, grouping=0, endpoint=0, res_name=None, timeout=0, assoc_src_endpoint=0):
        #self.registerHandler(COMMAND_CLASS_IP_ASSOCIATION, CONTROLLER_CHANGE_STATUS, fun)
        assert(not res_name) # Symbolic names not supported yet
        if res_name: res_name_len = len(res_name)
        else: res_name_len = 0
        return self.zip_client.sendData(pack("3B16s2B", COMMAND_CLASS_IP_ASSOCIATION, IP_ASSOCIATION_SET, grouping,
                    self.get_ipv6_address(res_ip).packed, endpoint, res_name_len), 
                    endpoint=assoc_src_endpoint, max_time=timeout)
        
    def removeIpAssociation(self, res_ip, grouping=0, endpoint=0, assoc_src_endpoint=0):
        return self.zip_client.sendData(pack("3B16s2B", COMMAND_CLASS_IP_ASSOCIATION, IP_ASSOCIATION_REMOVE,
                                      grouping, self.get_ipv6_address(res_ip).packed, endpoint, 0), 
                                 endpoint=assoc_src_endpoint)
        
    def getIpAssociation(self, fun, grouping=0, index=0, endpoint=0):
        self.zip_client.registerHandler(COMMAND_CLASS_IP_ASSOCIATION, IP_ASSOCIATION_REPORT, fun)
        return self.zip_client.sendData(pack('4B', COMMAND_CLASS_IP_ASSOCIATION, IP_ASSOCIATION_GET,
                                      grouping, index), endpoint)
        
    def sendDataZIP(self, *args, **kwargs):
        return self.zip_client.sendData(*args, **kwargs)


        