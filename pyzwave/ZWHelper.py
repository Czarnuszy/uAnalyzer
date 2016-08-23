'''
Created on 21/06/2011

@author: aes
'''
import xml.dom.minidom
from struct import *
import sys
import os

class ZWHelper(object):
    '''
    classdocs
    '''
    def getByKey(self,n,key):
        for c in n.childNodes:
            if(c.nodeType==c.ELEMENT_NODE and key == int(c.getAttribute("key"),16) ):
                return c
        return None

    def getByName(self,n,name):
        for c in n.childNodes:
            if(c.nodeType==c.ELEMENT_NODE and name == c.getAttribute("name") ):
                return c
        return None


    def __init__(self):
        if getattr(sys, 'frozen', None):
            basedir = sys._MEIPASS
        else:
            basedir = os.path.dirname(__file__)

        xmlfile = os.path.join(basedir,"ZWave_custom_cmd_classes.xml")
        self.x = xml.dom.minidom.parse( xmlfile )
        
        self.classes = dict()
        for c in self.x.getElementsByTagName("cmd_class"):
            cls = int(c.getAttribute("key"),16)
            if (self.classes.has_key(cls)):
                if(int(c.getAttribute("version")) > int(self.classes[cls].getAttribute("version"))):
                    self.classes[cls] = c
            else:
                self.classes[cls] = c

        self.gentype = dict()
        for c in self.x.getElementsByTagName("gen_dev"):
            gen = int(c.getAttribute("key"),16)
            self.gentype[gen] = c


    def ZWToObj(self,zwcmd):
        '''
        Convert a zwave command to a python object, the object is a dictionary 
        with values encoded as hex
        TODO Class and command fields are currently translated into strings... 
        that is not quite in the sprit of this function, but ok for the ZWToStr function
        '''
        i=0
        d = []        
        
        try:
            cls = self.classes[ord(zwcmd[i])]
        except KeyError:
            cls = None

        d.append( ("Class", cls.getAttribute("name")  ) )

            
        if(not cls):
            d.append(("data", zwcmd[2:].encode("hex")))
            return d

        cmd = self.getByKey(cls, ord(zwcmd[i+1]))

        if(not cmd):
            d.append(("data", zwcmd[2:].encode("hex")))
            return d
       
        d.append( ("Command", cmd.getAttribute("name") ) )
 
       
        i=2

        try:                
            for pn in cmd.getElementsByTagName("param"):
                name = pn.getAttribute("name")
                tt = pn.getAttribute("type")
     
                length =0
                value = None
                if(tt in "BYTE" ):
                    length = 1
                elif(tt =="WORD"):
                    length = 2
                elif(tt =="BIT_24"):
                    length = 3
                elif(tt =="DWORD"):
                    length = 4
                elif(tt == "ARRAY"):
                    a = pn.getElementsByTagName("arrayattrib")
                    length = int(a[0].getAttribute("len"))
                    
                    if(length==255):
                        a = pn.getElementsByTagName("arraylen")[0]
                        off = int(a.getAttribute("paramoffs"),0)
                        mask = int(a.getAttribute("lenmask"),0)
                        shift = int(a.getAttribute("lenoffs"),0)                    
                        length = (ord(zwcmd[off]) & mask) >> shift
                    
                elif(tt == "CONST"):
                    length = 1
                elif(tt == "MARKER"):
                    length = 1
                elif(tt == "BITMASK"):
                    a = pn.getElementsByTagName("bitmask")[0]
                    off = int(a.getAttribute("paramoffs"),0)
                    mask = int(a.getAttribute("lenmask"),0)
                    shift = int(a.getAttribute("lenoffs"),0)                    
                    
                    if(off==255):
                        length = len(zwcmd) - i
                    else:
                        length = (ord(zwcmd[off]) & mask) >> shift
                    
                    value=[]
    
                   
                    for bit in range(0,length):
                        b = ord(zwcmd[i+(bit>>3)])
                        value.append( (b >> (bit & 7) & 1) > 0 )
    
                                                    
                elif(tt == "STRUCT_BYTE"):
                    length = 1
                    b = ord(zwcmd[i])
                    value = dict()
                    for e in pn.getElementsByTagName("bitfield"):
                        mask = int(e.getAttribute("fieldmask"),0)
                        shift = int(e.getAttribute("shifter"),0)
                        name = e.getAttribute("fieldname")
                        value[name] = chr((b & mask) > shift).encode("hex")
    
                    for e in pn.getElementsByTagName("bitflag"):
                        name = e.getAttribute("flagname")
                        mask = int(e.getAttribute("flagmask"),0)
                        value[name] = b & mask > 0
    
                elif(tt == "VARIANT" ): #A variant has no given length                
                    v = pn.getElementsByTagName("variant")[0]
                    off = int(v.getAttribute("paramoffs"))
                    
                    if(off == 255):                    
                        length = len(zwcmd) - i
                    else:
                        mask = int(v.getAttribute("sizemask"),0)
                        shift = int(v.getAttribute("sizeoffs"),0)
                        
                        length = (ord(zwcmd[off]) & mask) >> shift
                else:
                    raise Exception("Unsupported type "+  tt)
                
                if(i + length > len(zwcmd)):
                    raise IndexError
                
                if(value == None):
                    value = zwcmd[i:i+length].encode("hex")
                    
                d.append((name, value))
                i=i+length            
        except IndexError:
            d.append(("Package truncated","True"))
                
                #raise Exception("Package truncated")
        return d

    def getClassName(self,c):
        try:
            cls = self.classes[c]
            return cls.getAttribute("name")
        except KeyError:
            return "COMMAND_CLASS_Unknown (%02x)" %(c)

    def getTypeString(self,gen_type,spec_type):
        gen_name = "Unknown(%02x)" %(gen_type)
        spec_name = "Unknown(%02x)" %(spec_type)
        try:
            cls = self.gentype[gen_type]
            gen_name = cls.getAttribute("name")
            
            for c in cls.getElementsByTagName("spec_dev"):
                if(int(c.getAttribute("key"),0) == spec_type):
                    spec_name = c.getAttribute("name")
                    break
        except KeyError:
            pass
        
        return (gen_name,spec_name)
                
    def ZWToStr(self,zwcmd):
        s=""
        #try:
        #    o = self.ZWToObj(zwcmd)
        #except Exception as e:
        #    return str(e)
        o = self.ZWToObj(zwcmd)
        for (k,v) in o:
            s=s +  "%s : %s\n" %(k,v)
        return s
    
    def getClassNames(self):
        l=[]
        for (c,v) in self.classes.items():
            l.append(v.getAttribute("name"))
        return l


    def getCmdNames(self,clsName):
        for (c,v) in self.classes.items():
            if(clsName == v.getAttribute("name")):
                return  map(lambda x:x.getAttribute("name") ,v.getElementsByTagName("cmd")) 
        return None
    
    def getCmdNode(self,clsName,cmdName):
        for (c,v) in self.classes.items():
            if(clsName == v.getAttribute("name")):
                return self.getByName(v, cmdName)
        None

    def classCmdByName(self,clsName,cmdName):
        for (c,v) in self.classes.items():
            if(clsName == v.getAttribute("name")):
                clv = int(v.getAttribute("key"),0)                
                cmv = int(self.getByName(v, cmdName).getAttribute("key"),0)                
                return (clv,cmv) 
        None

    def printNodeInfo(self,nif):
        print "Generic: ", 
        
    def hexstr2int(self, seq):
        '''
        Take a hex string and convert to list of int.
        E.g. '5c4201' -> [0x5c, 0x42, 0x01]
        '''
        return map(lambda x: int(x, 16), chunker(seq, 2))
    
    

class ZWSendCmdGUI():
    def __init__(self, master):
        self.zwhelper = ZWHelper()
    
        frame = Frame(master)
        frame.pack()
                
        self.cls = StringVar(frame)
        self.cls.set("COMMAND_CLASS_BASIC") # initial value   
        self.cls.trace("w",self.classSelected)


        self.cmd = StringVar(frame)
        self.cmd.set("BASIC_GET") # initial value   
        self.cmd.trace("w",self.cmdSelected)
        
        option = OptionMenu(frame, self.cls,*self.zwhelper.getClassNames())
        option.pack()


        self.cmdO = OptionMenu(frame, self.cmd,*self.zwhelper.getCmdNames(self.cls.get()))
        self.cmdO.pack()
        
        self.zwhelper.getCmdNames("COMMAND_CLASS_BASIC")
        
        
    def classSelected(self,*args):
        self.cmdO["menu"].delete(0, END)
        
        names = self.zwhelper.getCmdNames(self.cls.get())
        for i in names: #do magic... http://www.prasannatech.net/2009/06/tkinter-optionmenu-changing-choices.html
            self.cmdO["menu"].add_command(label=i, command=lambda temp = i: self.cmdO.setvar(self.cmdO.cget("textvariable"), value = temp))
        self.cmd.set(names[0])
        
    def cmdSelected(self,*args):
        print "select" , self.cmd.get()
        
        print self.zwhelper.getCmdNode(self.cls.get(),self.cmd.get()).toxml()
        

def chunker(seq, size):
    return (seq[pos:pos + size] for pos in xrange(0, len(seq), size))
       
if __name__ == '__main__':
    zh=ZWHelper()
    
    
    
    n = zh.getCmdNode("COMMAND_CLASS_ZIP_PORTAL","GATEWAY_CONFIGURATION_SET")
    print zh.cmd2obj(n)
    #print zh.ZWToStr("5204000401000000d31601021402864f234d339834".decode("hex"))
    
    
    #root = Tk()
    #root.title("Z/IP client")
    #app = ZWSendCmdGUI(root)      
    #root.mainloop()
