


<?php

class Process{
    private $pid;
    private $command;

    public function __construct($cl=false){
        if ($cl != false){
            $this->command = $cl;
            $this->runCom();
        }
    }
    private function runCom(){
        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
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
        $command = 'kill '.$this->pid;
        exec($command);
        if ($this->status() == false)return true;
        else return false;
    }
}

//We know now how we can fork a process in linux with the & operator.
//And by using command: nohup MY_COMMAND > /dev/null 2>&1 & echo $! we can return the pid of the process.

//This small class is made so you can keep in track of your created processes ( meaning start/stop/status ).

//You may use it to start a process or join an exisiting PID process.


    // You may use status(), start(), and stop(). notice that start() method gets called automatically one time.
    $process = new Process('sudo sudo ../../analyzer/zniffer/run');

    // or if you got the pid, however here only the status() metod will work.
  //  $process = new Process();
  //  $process.setPid(my_pid);
    // Then you can start/stop/ check status of the job.
    $process.start();
    $process.stop();



    if ($process.status()){
        echo "The process is currently running";
    }else{
        echo "The process is not running.";
    }


/* An easy way to keep in track of external processes.
* Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ? Well.. This is a way of doing it.
* @compability: Linux only. (Windows does not work).
* @author: Peec
*/

?>


<html>

<div id="maybeThis">
  <?php if ($process.status()){
    echo "The process is currently running";
}else{
    echo "The process is not running.";
}

?> </div>
</html>
