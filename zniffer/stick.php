<?php


#exec('sudo exec nohup setsid python zniffer.py --output_csv --region US -i /dev/ttyACM0 -o /tmp/#zniffer.zlf 2>&1', $output);
exec('sudo sudo /home/magni/public_html/zniffer/run', $output);
#exec('sudo ./stop');
#print_r($output);


#$info = system('./run');
#$info =+ system('./stop');

?>
