#!/bin/python
import ssl,dtls,socket,sys
import dtls.openssl
from ctypes import *

SSL_ST_CONNECT                =  0x1000
SSL_ST_ACCEPT                 =  0x2000
SSL_ST_MASK                   =  0x0FFF
SSL_ST_INIT                   =  (SSL_ST_CONNECT|SSL_ST_ACCEPT)
SSL_ST_BEFORE                 =  0x4000
SSL_ST_OK                     =  0x03
SSL_ST_RENEGOTIATE            =  (0x04|SSL_ST_INIT)

SSL_CB_LOOP                   =  0x01
SSL_CB_EXIT                   =  0x02
SSL_CB_READ                   =  0x04
SSL_CB_WRITE                  =  0x08
SSL_CB_ALERT                  =  0x4000 #/* used in callback */
SSL_CB_READ_ALERT             =  (SSL_CB_ALERT|SSL_CB_READ)
SSL_CB_WRITE_ALERT            =  (SSL_CB_ALERT|SSL_CB_WRITE)
SSL_CB_ACCEPT_LOOP            =  (SSL_ST_ACCEPT|SSL_CB_LOOP)
SSL_CB_ACCEPT_EXIT            =  (SSL_ST_ACCEPT|SSL_CB_EXIT)
SSL_CB_CONNECT_LOOP           =  (SSL_ST_CONNECT|SSL_CB_LOOP)
SSL_CB_CONNECT_EXIT           =  (SSL_ST_CONNECT|SSL_CB_EXIT)
SSL_CB_HANDSHAKE_START        =  0x10
SSL_CB_HANDSHAKE_DONE         =  0x20


cbs = [] #Keep a reference to callback functions to keep them from being garbage collected

def create_dtls_psk_sock(addr,client_psk_cb,af = socket.AF_INET,server=False,keyfile=None,certfile=None):
	'''
	Create a DTLS socket, with PSK support.
	addr is the UDP address of the socket as a ('host',port) tuple
	client_psk_cb callback function which must return the tuple (psk,identity)
	af is the adderss family
	'''
	global cbs #keep function from being garbage collected
	
	def client_psk_cb_wrap(ssl,hint,identity,max_idenity_len,cpsk, max_psk_len):
		(iden,pypsk) = client_psk_cb(hint)
		l = len(pypsk)
		memmove(cpsk,pypsk,l)
		if(iden):
			memmove(identity,iden,len(iden))
		else:
			memset(identity,0,1)
		
		return l
		
	dtls.do_patch()
	#ciphers="PSK"
	
	if(server):
		sock = ssl.wrap_socket(socket.socket(af, socket.SOCK_DGRAM),
					   server_side=True,
					   certfile=certfile,
					   keyfile=keyfile,
					   do_handshake_on_connect = False,
					   ciphers="PSK"
					   )
		sock.bind(addr)
		sock.listen(5)
		sock.settimeout(2.0)
	else:
		sock = ssl.wrap_socket(socket.socket(af, socket.SOCK_DGRAM),do_handshake_on_connect=False,
							ciphers="PSK")
		sock.settimeout(2.0)
		sock.connect(addr)
	
	#Using ctypes we interface with the function SSL_set_psk_client_callback from OpenSSL
	proto = ("SSL_set_psk_client_callback", dtls.openssl.libssl, ((None, "ret"), (dtls.openssl.SSL, "ctx"), (c_void_p, "psk_client_cb")) )	
	dtls.openssl._make_function(*proto)

	proto = ("SSL_set_info_callback", dtls.openssl.libssl, ((None, "ret"), (dtls.openssl.SSL, "ctx"), (c_void_p, "callback")) )	
	dtls.openssl._make_function(*proto)

	CLIENTPSKFUNC = CFUNCTYPE(c_uint, c_void_p, c_char_p,POINTER(c_char),c_uint,POINTER(c_char),c_uint)
	cb_fun = CLIENTPSKFUNC(client_psk_cb_wrap)
	
	dtls.openssl.SSL_set_psk_client_callback(sock._sslobj._ssl.value,cb_fun)


	def client_cb(ssl,where,ret):
		from _ssl import CERT_NONE, CERT_OPTIONAL, CERT_REQUIRED
		from _ssl import PROTOCOL_SSLv3, PROTOCOL_SSLv23, PROTOCOL_TLSv1
		import _ssl
		if(where & SSL_CB_ALERT and sock._connected and not server):
			
			print "Client Alert", where,ret,sock._connected
			#sock.unwrap();
			sock._connected = True

			sock._sslobj = _ssl.sslwrap(sock._sock, False,
										None, None,CERT_NONE, PROTOCOL_SSLv23, None,None)

			sock.do_handshake()
			#try:
			#	dtls.openssl.SSL_shutdown(sock._sslobj._ssl.value);
			#except:
			#	pass
			#sock.shutdown(socket.SHUT_WR)

	CLIENTCBFUNC = CFUNCTYPE(None, c_void_p, c_int,c_int)
	cb_fun2 = CLIENTCBFUNC(client_cb)
	dtls.openssl.SSL_set_info_callback(sock._sslobj._ssl.value,cb_fun2)


	cbs.append(cb_fun)
	cbs.append(cb_fun2)

	if(not server):
		sock.do_handshake()
	sock.psk = client_psk_cb
	return sock


def dtls_set_server_psk(sock,psk):
	global cbs
	
	def server_psk_cb_wrap(ssl,hint,spsk, max_psk_len):
		print "psk set"
		l = len(psk)
		memmove(spsk,psk,l)
		return l

	#Using ctypes we interface with the function SSL_set_psk_client_callback from OpenSSL
	proto = ("SSL_CTX_set_psk_server_callback", dtls.openssl.libssl, ((None, "ret"), (dtls.openssl.SSL, "ctx"), (c_void_p, "psk_server_cb")) )	
	dtls.openssl._make_function(*proto)


	CLIENTPSKFUNC = CFUNCTYPE(c_uint, c_void_p, c_char_p,POINTER(c_char),c_uint)
	cb_fun = CLIENTPSKFUNC(server_psk_cb_wrap)
	
	print dtls.openssl.SSL_CTX_set_psk_server_callback(sock._sslobj._ssl.value,cb_fun)
	
	cbs.append(cb_fun)
	

def dtls_close(sock):
	sock._connected = False
	try:
		dtls.openssl.SSL_shutdown(sock._sslobj._ssl.value);
	except:
		pass
	
	sock.shutdown(socket.SHUT_WR)
	
	
if __name__ == '__main__':
	sock=create_dtls_psk_sock(('192.168.1.126', 41230),lambda x : (None,"MySecret"))
	
	sock.send('Hi there')
	try:
		print sock.read()
	finally:
		print "Closing"
		sock.unwrap()
		sock.close()
