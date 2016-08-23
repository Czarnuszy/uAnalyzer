'''
Created on Sep 27, 2012
# -*- coding: utf-8 -*-
@author: aes
'''
import select
import sys
import socket
import pybonjour
import threading
import time
from pyzwave.ZW_classcmd import *
from pyzwave.ZWHelper import ZWHelper
from struct import unpack

hlp = ZWHelper();

class Enum(set):
    def __getattr__(self, name):
        if name in self:
            return name
        raise AttributeError

class Node:
    Flags = Enum(["SUPPORT_UNSEC", "CONTROL_UNSEC", "SUPPORT_SEC", "CONTROL_SEC"])
    Modes = Enum(["MODE_PROBING", "MODE_NONLISTENING", "MODE_ALWAYSLISTENING", "MODE_FREQUENTLYLISTENING","MODE_MAILBOX"])

    def classadd(self,cls,cls_sub_type,control,secure):
        try:
            flag = self.classes[cls][1]
        except KeyError:
            flag = set()

        if(secure and control):
            flag.add(self.Flags.CONTROL_SEC)
        elif(secure and not control):
            flag.add(self.Flags.SUPPORT_SEC)
        if(not secure and control):
            flag.add(self.Flags.CONTROL_UNSEC)
        elif(not secure and not control):
            flag.add(self.Flags.SUPPORT_UNSEC)

        self.classes[cls] = (cls_sub_type, flag)

    def isFailing(self):
        '''
        Returns true if the node is marked as failing
        '''
        return self.mode & 0x0200 != 0

    def isDeleted(self):
        '''
        Returns true if the node is marked as failing
        '''
        return self.mode & 0x0100 != 0

    def hasLowBat(self):
        '''
        Returns true if the node is marked as failing
        '''
        return self.mode & 0x0400 != 0
    
    def getHomeID(self):
        return int(self.hostname[2:10],16)
    
    def getNodeID(self):
        return int(self.hostname[10:12],16)

    def getMode(self):
        '''
        returns a Node.Modes enum value
        '''
        m = (self.mode & 0xFF)
        if(m==0):
            return self.Modes.MODE_PROBING
        elif(m==1):
            return self.Modes.MODE_NONLISTENING
        elif(m==2):
            return self.Modes.MODE_ALWAYSLISTENING
        elif(m==3):
            return self.Modes.MODE_FREQUENTLYLISTENING
        elif(m==4):
            return self.Modes.MODE_MAILBOX
        else:
            return None            
    
    def __str__(self):
        (gen,spec) = hlp.getTypeString(self.generic,self.specific)        
        s = "Name     : " + self.name +"\n"        
        s+= "Hostname : " + self.hostname +"\n"
        s+= "IP : " + self.ip +"\n"
        s+= "Endpoint : " + str(self.epid) +"\n"
        s+= "Generic  : " + gen +"\n"
        s+= "Specific : " + spec +"\n"
        s+= "mode     : " + str(self.getMode()) +"\n"
        s+= "inst.icon: " + hex(self.installer_icon) +"\n"
        s+= "user.icon: " + hex(self.user_icon) +"\n"

        s+= "Failing  : " + str(self.isFailing()) + "\n"
        s+= "Deleted  : " + str(self.isDeleted()) + "\n"
        s+= "Classes  : \n"

        for v,c in self.classes.iteritems():            
            s+= "  %s sub type %02x flags %s\n" %(hlp.getClassName(v),c[0],str(c[1]))
        return s
        
    def __init__(self,_name,_hostname,_info):
        self.name = _name;
        self.hostname = _hostname
        self.mode=None
        self.ip = None
        self.epid = None
        self.classes = dict()
        control = False
        secure = False
        self.installer_icon = 0
        self.user_icon = 0
        
        if(len(_info) < 2):
            self.generic = 0
            self.specific = 0
            return
        else:
            self.generic = ord(_info[0])
            self.specific = ord(_info[1])
        
        i=2;        
        while(i  < len(_info)):
            cls = ord(_info[i])
            if(cls >=0xF0 and i < len(_info)-1 ):
                i=i+1
                cls = (cls << 8) | ord(_info[i]) 
                        
            cls_sub_type = 0        
            if(cls in [COMMAND_CLASS_ALARM,COMMAND_CLASS_SENSOR_ALARM,COMMAND_CLASS_METER,COMMAND_CLASS_SENSOR_MULTILEVEL]):
                i=i+1
                cls_sub_type = ord(_info[i])
            elif(cls == 0xEF):
                control = True
            elif(cls == 0xF100):
                secure = True
                control = False
            else:
                self.classadd(cls,cls_sub_type,control,secure)
            i=i+1            


class ZWMdnsListener(threading.Thread):
    '''
    classdocs
    '''


    def __init__(self,nodeAddCallback=None, nodeRemoveCallback=None):
        '''
        Constructor
        '''
        threading.Thread.__init__(self)
        
        self.browse_sdRef = pybonjour.DNSServiceBrowse(regtype = "_z-wave._udp",
                                          callBack = self.browse_callback)
        
        self.running = True        
        self.start()
        self.nodeAddCallback = nodeAddCallback
        self.nodeRemoveCallback = nodeRemoveCallback
        self.resolved=[]
        self.queried= []
        self.ips = dict()

    def parse_txt(self,txt):
        tags=dict()
        i=0        
        while i < len(txt): #RFC1035            
            length=ord(txt[i])
            if(length==0):
                break
            label = txt[i+1:i+1+length]
            i=i+length+1
            q = label.split('=');
            tags[q[0]] = q[1]
        return tags
    
    
    def query_record_callback(self,sdRef, flags, interfaceIndex, errorCode, fullname,
                          rrtype, rrclass, rdata, ttl):
        if errorCode == pybonjour.kDNSServiceErr_NoError:
            if (fullname[-1]!="."):
                fullname = fullname+"."
            #print "querry callback ",fullname    
            self.ips[fullname] = socket.inet_ntoa(rdata)
        else:
            print "query_record_callback, Error",errorCode
        
        if errorCode == pybonjour.kDNSServiceErr_NoError:           
            self.ips[fullname] = socket.inet_ntoa(rdata)

        if(len(self.queried)):
            self.queried.pop()

    def unEscapeName(self,escname):
        '''
        return the un-escaped service name
        RFC4343, section 2.1        
        '''
        i=0;
        name=""
        while i < len(escname):
            c = escname[i]
            if(c =='.'):
                break;
            i=i+1                
            if(c == '\\' and i+1 < len(escname) ):
                try:                
                    v = int(escname[i+1:i+1+2])
                    name = name + chr(v)
                    i=i+3
                except ValueError:
                    c =escname[i+1]
                    if(not (c=='\\' or c=='.')) :
                        c='?' #Undefined....
                    name = name + c
                    i=i+1
            else:
                name = name + c
        return name
    
    def resolve_callback(self,sdRef, flags, interfaceIndex, errorCode, fullname,
                         hosttarget, port, txtRecord):
                       
            txt = self.parse_txt(txtRecord)            
            n = Node(self.unEscapeName(fullname),hosttarget,txt["info"])
            (n.mode,) = unpack("!H",txt["mode"])   
            (n.epid,) = unpack("!B",txt["epid"])
            (n.installer_icon,) = unpack("!H",txt["icon"][0:2] )
            (n.user_icon,) = unpack("!H",txt["icon"][2:4])

            #print "resolve_callback",fullname,hosttarget
            
            #If we don't know the IP of this host then do a A query, and wait for the result
            #TODO maybe we should also do a AAAA query?
            if( not hosttarget in self.ips):
                #print "Query A ",hosttarget
                query_sdRef = \
                    pybonjour.DNSServiceQueryRecord(interfaceIndex = interfaceIndex,
                                            fullname = hosttarget,
                                            rrtype = pybonjour.kDNSServiceType_A,
                                            callBack = self.query_record_callback)

    
                self.queried.append(hosttarget)
                try:
                    while len(self.queried)>0:
                        ready = select.select([query_sdRef], [], [], 1.0)
                        if query_sdRef not in ready[0]:
                            print 'Query record timed out'
                            self.queried.pop()
                        pybonjour.DNSServiceProcessResult(query_sdRef)
                finally:
                    query_sdRef.close()


            #print "Reply",hosttarget,self.ips
            if(hosttarget in self.ips):
                n.ip = self.ips[hosttarget]
            #else:                
            #    print "Warning no IP"
            
                
            if(self.nodeAddCallback):
                self.nodeAddCallback(n)                     

            self.resolved.append(True)            
        

    def browse_callback(self,sdRef, flags, interfaceIndex, errorCode, serviceName,
                        regtype, replyDomain):
        
        #print "browse_callback",errorCode,serviceName
        if errorCode != pybonjour.kDNSServiceErr_NoError:
            return
    
        if not (flags & pybonjour.kDNSServiceFlagsAdd):
            if(self.nodeRemoveCallback):
                self.nodeRemoveCallback(serviceName)
            return
    
        print 'Service added; resolving,', serviceName
        self.resolved = []    
        resolve_sdRef = pybonjour.DNSServiceResolve(0,
                                                    interfaceIndex,
                                                    serviceName,
                                                    regtype,
                                                    replyDomain,
                                                    self.resolve_callback)
            
        try:
            while not self.resolved:
                ready = select.select([resolve_sdRef], [], [], 1.0)
                if resolve_sdRef in ready[0]:                                
                    pybonjour.DNSServiceProcessResult(resolve_sdRef)
                else:
                    #print 'Resolve timed out'
                    break
            else:
                self.resolved.pop()

        finally:
            resolve_sdRef.close()


    def run(self):
        self.exit_ev =  threading.Event()
        while True:
            ready = select.select([self.browse_sdRef], [], [],0.5)
            
            if self.browse_sdRef in ready[0]:                
                pybonjour.DNSServiceProcessResult(self.browse_sdRef)
            if(self.exit_ev.isSet()):
                return;
     

    def stop(self):        
        self.exit_ev.set()
        self.join()

def nodeAddedCallback(node):
    print "Added: " + str(node)

def nodeRemovedCallback(node):
    print "Removed: " + str(node)
    
class MDNS_Resolver:
    '''
    One-shot mDNS resolver for queries like _4d._z-wave._udp.
    Results are reported in user-supplied callbacks.
    '''
    
    def resolve(self, request, resolve_callback=None, reply_count=1,
                timeout=5.0):
        '''
        This is the main function for using MDNS_Resolver.
        
        Wait for reply_count replies, but abort if the next reply does not arrive
        before timeout. Use reply_count = None to accept replies until timeout occurs.
        
        Example usage:
            resolver = MDNS_ResolveR()
            resolver.resolve('_4d._zwave._udp', my_res_cb, my_browse_cb)
            ... (wait for callbacks and process them)
            ...
                    
        '''
        self.resolve_callback = resolve_callback
        self.resolved = []
        self.browse_sdRef = pybonjour.DNSServiceBrowse(regtype = request,
                                          callBack = self.default_browse_callback)
        self.timeout = timeout

        try:
            while True:
                ready = select.select([self.browse_sdRef], [], [], self.timeout)
                if self.browse_sdRef in ready[0]:
                    pybonjour.DNSServiceProcessResult(self.browse_sdRef)
                    if reply_count is not None:
                        reply_count = reply_count - 1
                        if reply_count == 0:
                            break # enough replies received
                else:
                    break # timeout
        except KeyboardInterrupt:
            pass
        #print '----------------------done------------------------------'

    def default_browse_callback(self, sdRef, flags, interfaceIndex, errorCode, serviceName,
                    regtype, replyDomain):
        if errorCode != pybonjour.kDNSServiceErr_NoError:
            return
    
        if not (flags & pybonjour.kDNSServiceFlagsAdd):
            print 'Service removed'
            return
    
        #print 'Service added; resolving'
    
        resolve_sdRef = pybonjour.DNSServiceResolve(0,
                                                    interfaceIndex,
                                                    serviceName,
                                                    regtype,
                                                    replyDomain,
                                                    self.default_resolve_callback)
    
        try:
            while not self.resolved:
                ready = select.select([resolve_sdRef], [], [], self.timeout)
                if resolve_sdRef not in ready[0]:
                    print 'Resolve timed out'
                    break
                pybonjour.DNSServiceProcessResult(resolve_sdRef)
            else:
                self.resolved.pop()
        finally:
            resolve_sdRef.close()
            
    def default_resolve_callback(self, sdRef, flags, interfaceIndex, errorCode, fullname,
                     hosttarget, port, txtRecord):
        if errorCode == pybonjour.kDNSServiceErr_NoError:
#            print 'Resolved service:'
#            print '  fullname   =', fullname
#            print '  hosttarget =', hosttarget
#            print '  port       =', port
            if self.resolve_callback: 
                self.resolve_callback(sdRef, flags, interfaceIndex, errorCode, fullname,
                     hosttarget, port, txtRecord)
            self.resolved.append(True)
        else:
            print 'default_resolve_callback: errorCode=', errorCode

if __name__ == '__main__':
    mdns = ZWMdnsListener(nodeAddedCallback, nodeRemovedCallback);
    
    while(True):
        try:
            time.sleep(.1)
        except KeyboardInterrupt:
            break;
    print "Exited"
    mdns.stop()
    
