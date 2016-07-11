import binascii
import array
import struct



def int_to_bin(val):
    v='0x%0*x' % (2, val)
    return hex_to_bin(v)


def bin_to_array(data):
    bytes_array=map(ord,data)
    return bytes_array

def char_to_int(char):
    return ord(char)
def hex_to_bin(val,swap=False, size=1):
    s=str(val)
    #print s
    b = binascii.unhexlify(s[2:])
    if swap == True:
            #print binascii.hexlify(b)
            sb_array=array.array('B', b) #split binary value into array of bytes
            #print sb_array
            sb_array = sb_array[::-1] # reverse the array of bytes 
            #print sb_array
            sb=struct.pack('B'*size,*sb_array) #repack to bytes, yeehaa.
            final_bin=sb
    else:
            final_bin=b
    return final_bin

def pad_trail_zeros(string, size):
    return pad(string, size, "\0")

def pad(string, size, pad):
    while len(string)<size:
        string+=pad
    return string


class BinaryItem():
    def __init__(self, name, size_bytes, value=0):
        self.name=name
        self.size_bytes=size_bytes
        self.value=value
        #print self.value, isinstance(self.value,str)
    def get_size(self):
        return self.size_bytes
    def set_value(self, value):
        max_sz = (1<<(self.size_bytes*8))
        val = 0
        if isinstance(value, str):
            val=len(value)
        else:
            val = value
        #print val, max_sz
        if val > max_sz: 

            raise Exception("can't set value " + str(value) +" as it it fits on more than " + str(self.size_bytes) +" bytes")
        self.value=value
    def get_value(self):
        return self.value

    def tocommon(self):        
        if isinstance(self.value, str):
            return self.value
        else:
            if self.value < 0:
                vprime = (1<<(self.size_bytes*8)) + self.value
            else:
                vprime = self.value
            v='0x%0*x' % (self.size_bytes*2, vprime)
            return v

    def tobin(self,swap=False):
        t=self.tocommon()
        if isinstance(self.value,str):
            return t
        else:
            return hex_to_bin(t, swap, self.size_bytes)
    
    def __repr__(self):
        return self.tostring()
    def tostring(self):
        t=self.tocommon()
        return str(int(t,16))

