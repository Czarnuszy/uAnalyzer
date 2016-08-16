<?php
$directory = '../data/Saves';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
$amount_files = count($scanned_directory);
$fi = "../data/Saves/".$scanned_directory[2];

date_default_timezone_set('America/New_York');

if (file_exists($fi))
    $t = date("F d Y H:i:s.", filectime($fi));
?>

<html>
<?php
for($i = 2; $i <= $amount_files; $i+=3){
		$f = $scanned_directory[$i];
		$fn =  substr($f, 0, -4);
		$d = $scanned_directory[$i+1];
		$fi = "../data/Saves/".$scanned_directory[$i];
    $t = date("F d Y H:i:s.", filectime($fi));
    echo  '<button class='."filesButtons".' data-fid ='."$f".' data-filehid ='."$d".' ><p>'.$fn.'<br>'.$t.'</p>'.
		'</button>'.'<button class='.'"delFilButtons" data-id ='."$fn".'> <p><i class="fa fa-trash-o"></i><br> Delete'.'</button>'.
		'</button>'.'<button class='.'"renameFileBtn" data-id ='."$fn".'> <p><i class="fa fa-file-text-o"></i><br> Rename'.'</button>'.
		'</br></div>';
	}
  ?>
</html>

<script type="text/javascript">

var pd = 0;

function progressbar(x){
	var progress = 0 ;
	progress += pd;
	var pr = progress + "%";
	var html = "Please Wait	<div class=" + "'progress progress-micro'"+">	<div class="+
	"'progress-bar bg-color-blueLight'"+" role='progressbar'" + "style='width: "+pr+";'"+">" +
	 "</div></div>"

	$( "#progresZniffer" ).html( html );
	pd += 10;
  var gridlenght =w2ui.grid.records.length;
  if (pd >= 100  ){
		clearInterval(progrssInt);
		pd =0;
		$( "#progresZniffer" ).html( " " );
	}

}


$('.filesButtons').on('click', function(){
    var atr = '../data/Saves/' + $(this).attr('data-fid');
		var atrh = $(this).attr('data-filehid');
    var NumberofLines = 0;
		progrssInt = setInterval(function() {progressbar(pd);}, 200);

    open_file(atr);
		$("#opened_filename").text("Opened file: " + atrh.slice(0, -4));
/*	$.ajax({
		url: 'ajax/open_homeid.php',
		type: 'POST',
		data: { fileName: atrh},
		success: function(response){
  		home_id = response;
      console.log("homeId" + response);
			}
	});*/

w2popup.close();

});

$('.delFilButtons').on('click', function () {
	var t = '../data/Saves/' + $(this).attr('data-id');

	$.ajax({
		url: 'ajax/delete_file.php',
		type: 'POST',
		data: { fileName: t},
		success: function(response){

			console.log("deleted");
			$( "#popupmain" ).load( "ajax/open_file.php" );

			$.smallBox({
				title : "Z-Wave Packet Analyzer",
				content : "<i class='fa fa-clock-o'></i> <i>File " + t + " deleted.</i>",
				color : "#659265",
				iconSmall : "fa fa-check fa-2x fadeInRight animated",
				timeout : 1500
					});
			}
	});

})

$('.renameFileBtn').on('click', function () {
	var t = '../data/Saves/' + $(this).attr('data-id');

	$.SmartMessageBox({
    title : "Z-Wave Packet Analyzer",
    content : "Please enter new filename",
    buttons : "[Cancel][Save]",
    input : "text",
    placeholder : "Enter new filename"
	}, function(ButtonPress, Value) {

	if (ButtonPress === "Save") {
		Value = '../data/Saves/' + Value;
		$.ajax({
			url: 'ajax/delete_file.php',
			type: 'POST',
			data: { fileName: t, todo: 1, newName: Value},
			success: function(response){
				$( "#popupmain" ).load( "ajax/open_file.php" );
				$.smallBox({
					title : "Z-Wave Packet Analyzer",
					content : "<i>Trace saved to file :</i>",
					color : "#659265",
					iconSmall : "fa fa-check fa-2x fadeInRight animated",
					timeout : 2000
				});


				}
		});

}
if (ButtonPress=== "Cancel") {
		$.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Aborted...</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 2000
		});
}

	});

})

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
	width: 70%;
	height: 50px;
	line-height: 20px;
	padding: 1px;
}
.delFilButtons{
	width: 15%;
	height: 50px;
	line-height: 20px;
	padding: 1px;
}
.renameFileBtn{
	width: 15%;
	height: 50px;
	line-height: 20px;
	padding: 1px;
}
</style>
