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
        size = zw.get_node_len()

        ni = zw.get_all_node_info(size)

        print ni
        save_node_info_csv(ni)

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
        zw.stop()
        print "Exit"






parser = argparse.ArgumentParser()

parser.add_argument('-a', '--add_device',\
                  help="Add device", action='store_true', default=False)
parser.add_argument('-r', '--remove_device',\
                  help="Remove device", action='store_true', default=False)
parser.add_argument('-n', '--node_info',\
                  help="Node info", action='store_true', default=False)
parser.add_argument('-rt', '--reset',\
                  help="Resert", action='store_true', default=False)
parser.add_argument('-l', '--learn',\
                  help="Learning mode", action='store_true', default=False)


args = parser.parse_args()


ima = IMA(args)

ima.start()
