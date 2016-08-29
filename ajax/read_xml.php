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
    //      echo json_encode($this->amountDev);

        }
    }

    public function openXMLFile($file)
    {
        if (file_exists($file)) {
            $this->xml = simplexml_load_file($file);
         //print_r($xml);
        } else {
            echo json_encode('Failed to open file.');
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
                //    echo $m[$i][3].' '.$d[$x];
                    $this->byte4[] = $d[$x];
                  //  echo   $this->byte4;
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
                //  echo $m[$i][4].' '.$d[$x];
                  $this->byte5[] = $d[$x];
                  $this->readSpecDev($t[$x]);
                //  echo   $this->byte5;
              }
          }
      }

    }

    public function get_spec_dev(){

      $d = $this->spec_dev;
      $t = $this->spec_dev;
      $m = $this->devices;
      $s = count($t);

      for ($i = 0; $i < $this->amountDev; ++$i) {
          for ($x = 0; $x < $s; ++$x) {
              if (hexdec($m[$i][5]) == $t[$i][1][$x]) {
              //   echo $m[$i][5].' '.$d[$i][0][$x];
                 $this->byte6[] = $d[$i][0][$x];
              //   echo $this->byte6.'</br>';
              }
          }
      }

    }
    public function return_data(){
      $this->get_bas_dev();
      $this->get_gen_dev();
      $this->get_spec_dev();
      for ($i=0; $i < $this->amountDev  ; $i++) {
        $tmp = array(
          "basic" => (string)$this->byte4[$i],
          "generic" => (string)$this->byte5[$i],
          "specific" => (string)$this->byte6[$i]

        );
        $tab[] = $tmp;

      }

      $tmp = array(

        "basic" => 'ds',//(string)$this->byte4[$i],
        "generic" => 'ds',//(string)$this->byte5[$i],
        "specific" => 'ds',//(string)$this->byte6[$i]

      );


      echo json_encode($tab);

    }

}

$xmlp = '../pyzwave/ZWave_custom_cmd_classes.xml';


$obj = new NodeInfo($xmlp);

//echo phpinfo();
$obj->return_data();


?>
