<?php
$output = shell_exec('python /home/magni/Pulpit/zniffer/zniffer.py --output_csv --region US -i /dev/ttyACM0 -o /tmp/zniffer.zlf');
echo "<pre>$output</pre>";
?>
