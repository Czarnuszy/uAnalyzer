<?php
$process = new Process('/www/zniffer/run');

class Process{
    private $pid;
    private $command;

    public function __construct($cl=false){
        if ($cl != false){
            $this->command = $cl;
          //  $this->runCom();
        }
    }
    private function runCom(){
    // openwrt does not have nohup
    // ((/www/zniffer/run)&)&
    //    $command = '(('.$this->command.')&)&';
	$command = '((/www/zniffer/run)&)&';
        exec($command ,$op);
  //      echo (int)$op[0];
        $this->pid = (int)$op[0];
    }

    public function setPid($pid){
        $this->pid = $pid;
    }

    public function getPid(){
        return $this->pid;
    }

    public function status(){
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if (!isset($op[1]))return false;
        else return true;
    }

    public function start(){
        if ($this->command != '')$this->runCom();
        else return true;
    }

    public function stop(){
        $command = 'kill -9'.$this->pid;
      //  $command = 'pkill -f ../../analyzer/zniffer/zniffer.py'
        exec($command);
        if ($this->status() == false)return true;
        else return false;
    }
}



?>
