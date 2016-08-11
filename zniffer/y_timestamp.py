import datetime
import time
from y_dbglog import *
from y_bintools import *
CRASHDBG=DISABLE
class ZWaveTimeStamp(object):
    def __init__(self, date_time_utc=None):
        
        self.date_time_utc = date_time_utc

    def utc2local (self,utc):
        epoch = time.mktime(utc.timetuple())
        offset = datetime.datetime.fromtimestamp (epoch) - datetime.datetime.utcfromtimestamp (epoch)
        return utc + offset
    def set_csharp_timestamp(self, csharp_timestamp):
        kind = (csharp_timestamp>>62) 
        #print hex(ts)
        now_csharpref_hns= csharp_timestamp & ((1<<62)-1)
        now_csharpref_us=now_csharpref_hns / 10
        #print kind, now_csharpref_us
        ref_datetime_csharp=datetime.datetime(1,1,1)
        td=datetime.timedelta(microseconds=now_csharpref_us) 
        utc_date=ref_datetime_csharp+td
        #print td
        #print utc_date
        local_date=self.utc2local(utc_date)
        #print local_date
        self.date_time_utc = local_date

    def get_csharp_timestamp(self):
        dbglog(CRASHDBG, 'entering')
        ref_datetime_csharp=datetime.datetime(1,1,1)
        now_csharpref_us=int((self.date_time_utc - ref_datetime_csharp).total_seconds()*1000000) # seconds since 1/1/1, useconds in float part
        now_csharpref_hns=now_csharpref_us*10 #->0.1us->hundreds of nanos
        
    #http://referencesource.microsoft.com/#mscorlib/system/datetime.cs
        UNSPECIFIED_TIME_FLAG=0
        UTC_TIME_FLAG=4
        LOCAL_TIME_FLAG=8
        kind=LOCAL_TIME_FLAG &0x3 #kind is 2 bits
        now_csharp_bin=(kind<<62)| now_csharpref_hns
        
        v = BinaryItem("datetime_csharp_with_kind", 8, now_csharp_bin)
       #print (v.get_value())
        dbglog(CRASHDBG, 'exiting')
        return v


    def set_utc_timestamp(self,date_time_utc):
        self.date_time_utc = date_time_utc

    def get_utc_timestamp(self,):
        return self.date_time_utc 


