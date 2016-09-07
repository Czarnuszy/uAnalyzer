from ZWapi import *
import argparse


zw = ZWapi("/dev/ttyS0")

class IMA:
    def __init__(self,args):
        self.endCallback = 'none'
        self. times = 0
        self.args = args


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
        print args

    def learn_callback(self, a, b,  **args):
        print "callback"
        print a
        print b
        print args
        if a == 5:
            self.endCallback=5

    def add_device(self):
        print zw.ZW_AddNodeToNetwork(01, self.callback)
        while self.times<300:
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
        nodeDic = zw.get_node_dic()
        print nodeDic
        ni = zw.get_all_node_info(nodeDic['nodelist'])
        print ni
        save_node_info_csv(ni)
        print zw.get_all_routing_info(nodeDic['nodelist'], ZW_GET_ROUTING_INFO_9600)


    def get_routing_info(self):
        nodeDic = zw.get_node_dic()
        d = zw.get_all_routing_info(nodeDic['nodelist'], ZW_GET_ROUTING_INFO_9600)
        infoTab = []
        tmptab = []
        for x in range(len(d)):
            tmptab.append(nodeDic['nodelist'][x])
            tmptab.append(self.parse_hex_bin(str(d[x])))
            infoTab.append(tmptab)
            tmptab = []
        self.save_routing_info_csv(infoTab)
        print infoTab
        return infoTab


    def reset(self):
        zw.ZW_SetDefault(self.reset_callback)
        while self.times<100:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times

        zw.stop()
        print "Exit"

    def learn(self):
        print zw.ZW_SetLearnMode(01, self.learn_callback)
        while self.times<300:
            self.times += 1
            time.sleep(.1)
            print 'xxx' + str(self.endCallback)
            print self.times
            if self.endCallback == 5:
                break

        print zw.ZW_SetLearnMode(5, None)
        zw.stop()
        print "Exit"

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

    def save_routing_info_csv(self, data):
        filepath = open("/www/data/ima/routing_info.csv", "w")
        for i in range(len(data)):
            filepath.write(str(data[i][0])+',')

        filepath.write("\n")
        # tt = data[4][1]
        # print tt[::-1]
        sometab = []
        for i in range(len(data)):
            sometab.append(data[i][0])

        print sometab
        for i in range(len(data)):
            tt = data[i][1]
            tt = tt[::-1]
            print tt
            #for x in range(len(data[i])):
            filepath.write(str(data[i][0])+',')
            for x in sometab:
                filepath.write(str(tt[x-1]))

            #print data[i][0]
        #    for z in data[i]:
            #    print tt[z]
        #    for y in range(len(tt)):
            #    print tt.index(str(data[i][0]))
            #    print data[i][0]
                #if tt[y] == data[i][0]:

                    #filepath.write(tt[y])

            filepath.write("\n")

        filepath.close()


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
                  help="Resert", action='store_true', default=False)
parser.add_argument('-l', '--learn',\
                  help="Learning mode", action='store_true', default=False)
parser.add_argument('-rg', '--routing',\
                  help="Routing info", action='store_true', default=False)


args = parser.parse_args()


ima = IMA(args)

ima.start()
