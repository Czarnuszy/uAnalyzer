<?php

class NodeInfo
{
    private $xml;
    private $bas_dev;
    private $gen_dev;
    private $spec_dev;
    private $byte1;
    private $byte4;
    private $byte5;
    private $byte6;
    private $devices;
    private $amountDev;

    public function __construct($cl = false)
    {
        if ($cl != false) {
            $filePath = $cl;
            $this->openXMLFile($cl);
            $this->readCSVdata();
            $this->readBasDev();
            $this->readGenDev();
        }
    }

    public function openXMLFile($file)
    {
        if (file_exists($file)) {
            $this->xml = simplexml_load_file($file);
         //print_r($xml);
        } else {
            exit('Failed to open file.');
        }
    }

    public function readBasDev()
    {
        foreach ($this->xml->bas_dev as $bas) {
            $A1[] = hexdec($bas['key']);
            $A2[] = $bas['help'];
        }
        $this->bas_dev[] = $A2;
        $this->bas_dev[] = $A1;

        return $this->bas_dev;
    }

    public function readGenDev()
    {
        foreach ($this->xml->gen_dev as $gen) {
            $a1[] = hexdec($gen['key']);
            $a2[] = $gen['help'];
        }
        $this->gen_dev[] = $a2;
        $this->gen_dev[] = $a1;

        return $this->gen_dev;
    }

    public function readSpecDev($xkey)
    {

        foreach ($this->xml->gen_dev as $gen) {
          if ($gen['key'] == $xkey) {
            foreach ($gen->spec_dev as $rec) {
                $a1[] = hexdec($rec['key']);
                $a2[] = $rec['help'];
            }
          }
        }
        $tab[] = $a2;
        $tab[] = $a1;
        $this->spec_dev[] = $tab;


        return $this->spec_dev;
    }

    public function readCSVdata()
    {
        $csvFile = '../data/ima/node_info.csv';
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 512);
        }
        fclose($file_handle);
        $this->devices = $line_of_text;
        $this->amountDev = count($line_of_text) - 1;
    }

    public function get_bas_dev()
    {
        $d = $this->bas_dev[0];
        $t = $this->bas_dev[1];
        $m = $this->devices;

        for ($i = 0; $i < $this->amountDev; ++$i) {
            for ($x = 0; $x < 4; ++$x) {
                if (hexdec($m[$i][3]) == $t[$x]) {
                    echo $m[$i][3].' '.$d[$x];
                    $this->byte4 = $m[$i][3];
                }
            }
        }
    }
    public function get_gen_dev(){
      $d = $this->gen_dev[0];
      $t = $this->gen_dev[1];
      $m = $this->devices;
      $s = count($t);
      for ($i = 0; $i < $this->amountDev; ++$i) {
          for ($x = 0; $x < $s; ++$x) {
              if (hexdec($m[$i][4]) == $t[$x]) {
                  echo $m[$i][4].' '.$d[$x];
                  $this->byte5 = $m[$i][4];
                  $this->readSpecDev($t[$x]);
              }
          }
      }

    }

    public function get_spec_dev(){

      $d = $this->spec_dev;
      $t = $this->spec_dev;
      $m = $this->devices;
      $s = count($t);
  //    print_r($this->spec_dev[1][1]);
//  echo
      //  echo 'dupa';// $d[0][1];
      for ($i = 0; $i < $this->amountDev; ++$i) {
          for ($x = 0; $x < $s; ++$x) {
              if (hexdec($m[$i][5]) == $t[$i][1][$x]) {
                 echo $m[$i][5].' '.$d[$i][0][$x];
                 $this->byte4 = $m[$i][5];
              //    echo $this->byte4.'</br>';
              }
          }
      }

    }

}

$xmlp = '../pyzwave/ZWave_custom_cmd_classes.xml';
//$xml=simplexml_load_file("books.xml") or die("Error: Cannot create object");
//echo $xm

$obj = new NodeInfo($xmlp);
$d = $obj->readBasDev();
//print_r($d[0][1]);
//print_r($t[0][0]);
//echo $d;
//var_dump(hexdec($d));
$obj->get_bas_dev();
echo '</br>';
$obj->get_gen_dev();
//print_r($d);
echo '</br>';
$obj->get_spec_dev();
//echo $d;

/*
if (file_exists($xmlp)) {



    $xml = simplexml_load_file($xmlp);
  //  print_r($xml);
    print_r( $xml->bas_dev[0]['key']);
    echo  $xml->bas_dev[0]['key'];
    echo '</br>';

    //key of specific device 6th byte/////////
    foreach ($xml->gen_dev as $gen){
        foreach ($gen->spec_dev as $rec) {
          echo $rec['key'].'</br>';
        }
        echo '</br>';

    }

    echo '</br>';
    echo '///////////////////////////////////////////////////';
    echo '</br>';
    //key of general device 5th byte
    foreach ($xml->gen_dev as $gen) {
      echo $gen['key'].'</br>';
    }

    echo '</br>';

    echo '///////////////////////////////////////////////////';

    echo '</br>';
    //key of basic device 4th byte

    echo '</br>';
    foreach ($xml->bas_dev as $bas) {
      echo $bas['key'].'</br>';
    }

} else {
    exit('Failed to open file.');
}
*/;
