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

$('.filesButtons').on('click', function(){
		var atr = $(this).attr('data-fid');
		var atrh = $(this).attr('data-filehid');
		console.log(atr);

	$.ajax({
		url: "ajax/files_size.php",
		type: "POST",
		data: { DisplayedRecords: atr},
		success: function(response){
			NumberofLines= response-1;}
	});

	$.ajax({
		url: 'ajax/open_homeid.php',
		type: 'POST',
		data: { fileName: atrh},
		success: function(response){
  		home_id = response;
			}
	});

	$.ajax({
		url: 'ajax/open_file_data.php',
		type: 'POST',
		data: { data: atr},
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
