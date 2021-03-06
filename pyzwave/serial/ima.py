from ZWapi import *
import argparse


zw = ZWapi("/dev/ttyS0")

class IMA:
    def __init__(self,args):
        self.endCallback = 'none'
        self. times = 0
        self.args = args
        self.data = ''


    def callback(self, a, b, c, **args):
        print "callback"
        print a
        print b
        print c
        print args
        if a == 5:
            self.endCallback=5

    def remove_callback(self, a, b, c, **args):
        print "callback"
        print a
        print b
        print c
        print args
        if a == 5:
            self.endCallback=5

    def reset_callback(self,  **args):
        print "callback"
        print args['data']
        if args['data'] == None:
            self.endCallback = 'reset'

    def learn_callback(self, a, b, c, **args):
        print "callback received"
        print a
        print b
        print c
#        print d
#        print e
#    def learn_callback(self, **args):
        print args
        if a == 1:
    	    print "Learn Mode Started"
    	    self.endCallback=1
    	if a == 7:
    	    print "Learn Mode Failed"
    	    self.endCallback=7
    	if a == 0x80:
    	    print "Controller Deleted"
    	    self.endCallback=0x80
        #Learn mode done
        if a == 6:
    	    print "Learn Node Done"
            self.endCallback=6

    def neigh_update_callback(self, a, **args):
        print a
        print args
        if a == 34 or a == 35:
            self.endCallback = a

    def status_callback(self, a,  **args):
        print "callback"
        print a
        print binascii.hexlify(args['data'])
        if a == 1 or a == 0:
            self.endCallback = a
            self.data = binascii.hexlify(args['data'])

    def add_device(self):
        print zw.ZW_AddNodeToNetwork(01, self.callback)
        while self.times<50:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 5:
                break

        print zw.ZW_AddNodeToNetwork(5, None)
        zw.stop()
        print "Exit"

    def remove_device(self):
        print zw.ZW_RemoveNodeFromNetwork(1, self.remove_callback)
        while self.times<300:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 5:
                break

        zw.ZW_RemoveNodeFromNetwork(5, None)
        zw.stop()
        print "Exit"

    def get_node_info(self):
        ID = zw.MemoryGetID()
        print ID['nodeid']
        nodeDic = zw.get_node_dic()
        print nodeDic['nodelist'];
        ni = zw.get_all_node_info(nodeDic['nodelist'])
        print ni
        save_node_info_csv(ni)
        #print zw.get_all_routing_info(nodeDic['nodelist'], ZW_GET_ROUTING_INFO_9600)


    def get_routing_info(self):
    #    try:
        nodeDic = zw.get_node_dic()
        print nodeDic['nodelist']
        d = zw.get_all_routing_info(nodeDic['nodelist'], ZW_GET_ROUTING_INFO_9600)
        infoTab = []
        tmptab = []
        print d
        for x in range(len(d)):
            tmptab.append(nodeDic['nodelist'][x])
        #    print d[x]
            tmptab.append(self.parse_hex_bin(str(d[x])))
            infoTab.append(tmptab)
        #    print tmptab
            tmptab = []
        self.save_routing_info_csv(infoTab)
        self.save_static_routing_info(infoTab)

        return infoTab
#    except:
#        print 'ups'

        #self.get_routing_info()

    def reset(self):
        zw.ZW_SetDefault(self.reset_callback)
        while self.times<100:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 'reset':
                break

        zw.stop()
        print "Exit"

    def learn(self):
        print zw.ZW_SetLearnMode(01, self.learn_callback)
        while self.times<1200:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 1:
        	self.times = 0
        	self.endCallback=0
        	print "extend timeout another 120 sec"
            elif self.endCallback == 6:
                break
            elif self.endCallback == 7:
                print 'ERROR'
                break
        print zw.ZW_SetLearnMode(0x00, None)
        zw.stop()
        print "Exit Learn mode"

    def get_status(self, dev):
        A= [0]
        B=''
        B = B.join(map(chr, A))
        print zw.ZW_SendData(int(dev), B, self.status_callback)
        while self.times<100:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 1 or self.endCallback == 0:
                break
        if self.endCallback == 'none' or self.endCallback == 1:
            self.endCallback = 'Fail'
        if self.endCallback == 0:
            self.endCallback = 'OK'
        if self.endCallback != 'none':
            self.save_dev_status(self.endCallback, self.data, dev)
        else:
            self.save_dev_noresponse_status()
        zw.stop()
    #    print "Exit"

    def neighborUpdate(self, nodeid):
        print nodeid
        print zw.ZW_RequestNodeNeighborUpdate(int(nodeid), self.neigh_update_callback)
        while self.times<300:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 35:
                print 'Conenction error'
                break
            elif self.endCallback == 34:
                break
        zw.stop()
        print "Exit"

    def update_all_neighbors(self):
        nodeDic = zw.get_node_dic()
        nodes = nodeDic['nodelist']
        print nodes
        for nid in nodes:
            self.neighborUpdate(nid)

    def reverse_hex_string(self, string):
        mylist = list(string)
        size = len(mylist)
        for x in xrange(0,size/2, 2):
            mylist[x], mylist[size-x-2] = mylist[size-x-2], mylist[x]
        for x in xrange(1,size/2, 2):
            mylist[x], mylist[size-x] = mylist[size-x], mylist[x]

        return ''.join(mylist)

    def parse_hex_bin(self, hexdata):
        my_hexdata = hexdata
    #    print my_hexdata
        scale = 16 ## equals to hexadecimal
        num_of_bits = 64
        my_hexdata = self.reverse_hex_string(my_hexdata)
        #print my_hexdata
        binData =  bin(int(my_hexdata, scale))[2:].zfill(num_of_bits)
        return binData

#     def save_routing_info_csv(self, data):
#         filepath = open("/www/data/ima/routing_info.csv", "w")
#         sometab = []
#         for i in range(len(data)):
#             sometab.append(data[i][0])
#
#         for i in range(len(data)):
#             tt = data[i][1]
#             tt = tt[::-1]
#             print tt
#             filepath.write(str(data[i][0])+',')
#             t=0
# 	    print "MAdexDebugStart"
#
#             for x in sometab:
#         	#print "debug"
#         #	print tt
#         	#print sometab
#         	#print t
# #        	print "end of debug"
#                 if int(tt[sometab[t] - 1]) == 1:
#                     filepath.write(str(sometab[t])+',')
#                 t += 1
#
#             filepath.write("\n")
# 	    print "Madex Debug Ends"
#         print ' o kurwa'
#         filepath.close()

    def save_routing_info_csv(self, data):
        filepath = open("/www/data/ima/routing_info.csv", "w")
        sometab = []
        l = len(data)
        for i in range(len(data)):
            sometab.append(data[i][0])
    #    print data[0]
        for i in range(len(data)):
            print data
            print 'data gehe'
            print len(data)
            print 'len data'

            tt = data[i][1]
            tt = tt[::-1]
            print tt
            filepath.write(str(data[i][0])+',')
            t=0
            print 'hhhhhhhhhhhhhhhhdfgdffffffffffffffffffffffffffffffffffffffffffffffffff'
            print len(tt)
            for x in sometab:
                # print x
                # print str(i) + ' dlugos len data'
            #    print tt[sometab[t] -1]
                try:
                    if int(tt[sometab[t] -2]) == 1:
                        filepath.write(str(sometab[t])+',')
                except:
                    break
                t += 1
            filepath.write("\n")
        filepath.close()

    def save_static_routing_info(self, data):
        print 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
        filepath = open("/www/data/ima/static_routing_info.csv", "w")
        sometab = []
        for i in range(len(data)):
            sometab.append(data[i][0])

        for i in range(len(data)):
            filepath.write(str(data[i][0])+',')

        filepath.write('\n')


        for i in range(len(data)):
            tt = data[i][1]
            tt = tt[::-1]

            filepath.write(str(data[i][0])+',')
            t=0
            #Sprawdzam kazdy wiersz, jesli jest rowny jeden = sasiad, zapisuje do pliku.
            for x in sometab:
                print str(data[i][0]) + ' ' + str(sometab[t]) + ' ' + str(t)
                try:
                    if(int(data[i][0]) == int(sometab[t])):
                        filepath.write(str(2)+',')
                    elif int(tt[sometab[t] - 1] ) == 1:
                        filepath.write(str(1)+',')
                    else:
                        filepath.write(str(0)+',')
                except:
                        filepath.write(str(0)+',')

                t += 1

            filepath.write("\n")

        filepath.close()

    def save_dev_status(self, data, dataArray, deviceid):
        time = self.dev_connection_time(dataArray)
        repeaters_amount = self.dev_repeaters_amount(dataArray)
        repeaters = self.dev_repeaters_info(dataArray, repeaters_amount, deviceid)
        filepath = open("/www/data/ima/device_status.csv", "w")
        filepath.write(str(data) + ',' + time  + ',' + repeaters_amount + ',' +repeaters + ',' + self.dev_rssi_info(dataArray))
        filepath.close()

    def save_dev_noresponse_status(self):
        filepath = open("/www/data/ima/device_status.csv", "w")
        filepath.write('Fail, No response, No response, No response')
        filepath.close()

    def dev_connection_time(self, data):
        return str(float(int(data[0:4], 16))/100) + ' sec'

    def dev_repeaters_amount(self, data):
        return str(int(data[4:6],16))

    def dev_repeaters_info(self, data, repeaters_amount, deviceid):
        repeaters = []
        toolboxid = self.get_toolbox_id()
        toolboxid = str(toolboxid['nodeid'])
        size = int(repeaters_amount)
        repeaters.append(str(int(data[22:24], 16)))
        repeaters.append(str(int(data[24:26], 16)))
        repeaters.append(str(int(data[26:28], 16)))
        repeaters.append(str(int(data[28:30], 16)))
        repeaters.append(str(int(data[30:32], 16)))
        a = toolboxid + ' -> '
        for x in range(size):
            a += repeaters[x] + ' -> '
        a += str(deviceid)
        return a
    def dev_rssi_info(self, data):
    #    return str(data[6:16])
        return str(int(data[6:8],16))

    def get_toolbox_id(self):
        return zw.MemoryGetID()

    def start(self):
        if args.add_device == True:
            self.add_device()
        elif args.remove_device == True:
            self.remove_device()
        elif args.node_info == True:
            self.get_node_info()
        elif args.reset == True:
            self.reset()
        elif args.learn == True:
            self.learn()
        elif args.routing == True:
            self.get_routing_info()
        elif args.status == True:#and args.device_id == True:
            self.get_status(args.device_id)
        elif args.neigh_update == True:
            self.neighborUpdate(args.device_id)
        zw.stop()
        print "Exit"


parser = argparse.ArgumentParser()

parser.add_argument('-a', '--add_device',\
                  help="Add device", action='store_true', default=False)
parser.add_argument('-rm', '--remove_device',\
                  help="Remove device", action='store_true', default=False)
parser.add_argument('-n', '--node_info',\
                  help="Node info", action='store_true', default=False)
parser.add_argument('-x', '--reset',\
                  help="Reset", action='store_true', default=False)
parser.add_argument('-l', '--learn',\
                  help="Learning mode", action='store_true', default=False)
parser.add_argument('-rg', '--routing',\
                  help="Routing info", action='store_true', default=False)
parser.add_argument('-s', '--status',\
                  help="Status info", action='store_true', default=False)
parser.add_argument('-dev', '--device_id',\
                  help="Device id",  default=False)
parser.add_argument('-nu', '--neigh_update',\
                  help="Routing info", action='store_true', default=False)

args = parser.parse_args()


ima = IMA(args)

ima.start()
