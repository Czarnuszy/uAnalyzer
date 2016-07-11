import os, sys, inspect
currentdir = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))
parentdir = os.path.dirname(currentdir)
common_lib_dir=parentdir+"/common"

sys.path.insert(0,common_lib_dir)

from y_dbglog import *

CRASHDBG=DISABLE
from string import upper
class StateMachine(object):
  def __init__(self,dbg=DISABLE):
      self.handlers = {}
      self.startState = None
      self.endStates = []
      self.CRASHDBG=(dbg, CRASHDBG)[CRASHDBG==ENABLE]


  def add_state(self, name, handler, end_state=0):
      dbglog(self.CRASHDBG,"")
      name = upper(name)
      self.handlers[name] = handler
      if end_state:
           self.endStates.append(name)

  def set_start(self, name):
      dbglog(self.CRASHDBG,"")
      self.startState = upper(name)

  def run(self, cargo):
      dbglog(self.CRASHDBG,"")
      try:
         handler = self.handlers[self.startState]

      except:
         raise "InitializationError", "must call .set_start() before .run()"
      
      if not self.endStates:
         raise  "InitializationError", "at least one state must be an end_state"
      
      while True:
         [newState, cargo] = handler(cargo)
         dbglog(self.CRASHDBG, newState)
         if upper(newState) in self.endStates:
            break 
         else:
            handler = self.handlers[upper(newState)]
