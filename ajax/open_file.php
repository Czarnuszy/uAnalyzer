<?php
$directory = '../data/Saves';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
$amount_files = count($scanned_directory);
$fi = "../data/Saves/".$scanned_directory[2];
if (file_exists($fi))
    $t = date("F d Y H:i:s.", filectime($fi));

?>

<html>
<?php

for($i = 2; $i <= $amount_files; $i+=3){
		$f = $scanned_directory[$i];
		$fn = $f; // substr($f, 0, -4);
		$d = $scanned_directory[$i+1];
		$fi = "../data/Saves/".$scanned_directory[$i];
    $t = date("F d Y H:i:s.", filectime($fi));
    echo  '<button class='."filesButtons".' data-fid ='."$f".' data-filehid ='."$d".' ><p>'.$fn.'<br>'.$t.'</p></br></div>';

	}
  ?>
</html>

<script type="text/javascript">
//check amount if lines in file
//if > 1000 load only 1k to memory,
// amount lines / 1000 -> parse to int
// fe 2345/1000 = 2
// for(0; 2; ++)
$('.filesButtons').on('click', function(){
    var atr = '../data/Saves/' + $(this).attr('data-fid');
		var atrh = $(this).attr('data-filehid');
    var NumberofLines = 0;

	$.ajax({
		url: "ajax/files_size.php",
		type: "POST",
		data: { DisplayedRecords: atr},
		success: function(response){
			NumberofLines= response-1;

      if(NumberofLines > 1000){
        console.log("over 10000");

        if(w2ui.grid.records.length > 0)
          w2ui.grid.clear();

        var reclen = w2ui.grid.records.length;
        var i = NumberofLines / 2000;
        i = parseInt(i);

        var val = [0];
        for (var x = 1; x < i; x++) {
          val.push(x * 2000);
        }
        val.push(val[val.length-1] + 2000); //NumberofLines-i*1000
        console.log(val);
        val.forEach(function(value, i){
          console.log("val" + value);

          $.ajax({
            url: 'ajax/open_file_data.php',
            type: 'POST',
            async: false,
            data: { data: atr, fsize: NumberofLines, tim: value+2000 , gridLen: value},
            dataType: 'json',
            success: function(data){
          //    reclen = w2ui.grid.records.length;
              console.log("gridlen" + w2ui.grid.records.length);
              var color = "red";
              console.log("dl" + data.length);
        			for(x=0; x< data.length	; x++){
                reclen = w2ui.grid.records.length;
                color = "red";
        				if (data[x][2] != home_id){
        						data[x][3] = '."'-'".';
        						data[x][5] = '."'-';".';
                    data[x][12] = '-';
        					}else {
        						color = parse_sqnum(x, data);
        					}

          				w2ui['grid'].records.push({
          					recid : reclen+1,
          					id: reclen+1,
          					rssi: data[x][1],
          					data: data[x][0],
          					source: data[x][3],
          					route: data[x][12],
          					destination: data[x][5],
          				 	command: data[x][7],
          				 	h_id: data[x][2],
          				 	style: "background-color: " + color

          				 });
          		}
          		w2ui.grid.reload();


              }
            });
          });
    //       }
    $.smallBox({
      title : "Z-Wave Packet Analyzer",
      content : "<i>File opened.</i>",
      color : "#659265",
      iconSmall : "fa fa-check fa-2x fadeInRight animated",
      timeout : 1000
    });
      }  else{

    	$.ajax({
    		url: 'ajax/open_file_data.php',
    		type: 'POST',
    		data: { data: atr, fsize: NumberofLines},
    		dataType: 'json',
    		success: function(data){
    			if(w2ui.grid.records.length > 0)
    				w2ui.grid.clear();

    			var color = "red";
    			for(x=0; x<	NumberofLines; x++){
            color = "red";
    				if (data[x][2] != home_id){
    						data[x][3] = '."'-'".';
    						data[x][5] = '."'-';".';
                data[x][12] = '-';
    					}else {
    						color = parse_sqnum(x, data);
    					}
    				w2ui['grid'].records.push({
    					recid : x+1,
    					id: x+1,
    					rssi: data[x][1],
    					data: data[x][0],
    					source: data[x][3],
    					route: data[x][12],
    					destination: data[x][5],
    				 	command: data[x][7],
    				 	h_id: data[x][2],
    				 	style: "background-color: " + color

    				 });
    		}
    		w2ui.grid.reload();
    		$.smallBox({
    			title : "Z-Wave Packet Analyzer",
    			content : "<i>File opened.</i>",
    			color : "#659265",
    			iconSmall : "fa fa-check fa-2x fadeInRight animated",
    			timeout : 1000
    		});
    	}


    	});
    }

      }
	});

	$.ajax({
		url: 'ajax/open_homeid.php',
		type: 'POST',
		data: { fileName: atrh},
		success: function(response){
  		home_id = response;
			}
	});
  console.log("num" + NumberofLines);


});


	pageSetUp();



	var pagefunction = function() {
		// clears the variable if left blank
	};

	// end pagefunction

	// run pagefunction
	pagefunction();

</script>

<style>
.filesButtons{
	width: 100%;
	height: 50px;
	line-height: 20px;
	padding: 1px;



}
</style>
