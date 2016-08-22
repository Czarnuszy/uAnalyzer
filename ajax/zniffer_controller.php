<?php

$process = new Process('/www/zniffer/run');

class Process
{
    private $pid;
    private $command;

    public function __construct($cl = false)
    {
        if ($cl != false) {
            $this->command = $cl;
          //  $this->runCom();
        }
    }
    private function runCom()
    {
        // openwrt does not have nohup
    // ((/www/zniffer/run)&)&
    //    $command = '(('.$this->command.')&)&';
    $command = '((/www/zniffer/run)&)&';
        exec($command, $op);
  //      echo (int)$op[0];
        $this->pid = 32088;
    }

    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function status()
    {
        $command = 'ps | grep /www/zniffer/zniffer.py';
        exec($command, $op);
        if (!isset($op[2])) {
            return false;
        } else {
            return true;
        }
    }

    public function showpid()
    {
        $command = 'ps | grep /www/zniffer/zniffer.py';
 //'ps | grep '.$this->pid;
      exec($command, $op);

        return $op;
    }

    public function start()
    {
        if ($this->command != '') {
            $this->runCom();
        } else {
            return true;
        }
    }

    public function stop()
    {
        $command = '/www/zniffer/stop';
      //  $command = 'pkill -f ../../analyzer/zniffer/zniffer.py'
        exec($command);
        if ($this->status() == false) {
            return true;
        } else {
            return false;
        }
    }
}
