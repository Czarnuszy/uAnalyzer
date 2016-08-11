<?php


#exec('sudo exec nohup setsid python zniffer.py --output_csv --region US -i /dev/ttyACM0 -o /tmp/#zniffer.zlf 2>&1', $output);
exec('python /www/zniffer/zniffer.py --output_csv --region US -i /dev/ttyS0 -o /www/zniffer/data/zniffer.zlf'. " > /dev/null &");
#exec('sudo ./stop');
#print_r($output);


#$info = system('./run');
#$info =+ system('./stop');

?>
