'''
Created on Feb 20, 2012

@author: aes
'''
#/* ZW_GetProtocolStatus retrun value masks*/

ZW_PROTOCOL_STATUS_ROUTING = 0x01
ZW_PROTOCOL_STATUS_SUC     = 0x02

# ZW_LIBRARY_TYPEs one of these defines are returned when requesting */
# Library type */
ZW_LIB_CONTROLLER_STATIC = 0x01
ZW_LIB_CONTROLLER        = 0x02
ZW_LIB_SLAVE_ENHANCED    = 0x03
ZW_LIB_SLAVE             = 0x04
ZW_LIB_INSTALLER         = 0x05
ZW_LIB_SLAVE_ROUTING     = 0x06
ZW_LIB_CONTROLLER_BRIDGE = 0x07
ZW_LIB_DUT               = 0x08
ZW_LIB_AVREMOTE          = 0x0A
ZW_LIB_AVDEVICE          = 0x0B

APPLICATION_NODEINFO_NOT_LISTENING           = 0x00
APPLICATION_NODEINFO_LISTENING               = 0x01
APPLICATION_NODEINFO_OPTIONAL_FUNCTIONALITY  = 0x02
APPLICATION_FREQ_LISTENING_MODE_1000ms       = 0x10
APPLICATION_FREQ_LISTENING_MODE_250ms        = 0x20
