<?php
$directory = '../data/Saves';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
//$scanned_directory = array_diff($scanned_directory, array('zlfFiles', 'txtFiles'));
$amount_files = count($scanned_directory);
$fi = '../data/Saves/'.$scanned_directory[2];
echo json_encode($scanned_directory);
date_default_timezone_set('America/New_York');

if (file_exists($fi)) {
    $t = date('F d Y H:i:s.', filectime($fi));
}
?>

<html>
<?php

function getSymbolByQuantity($bytes)
{
    $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    $exp = floor(log($bytes) / log(1024));

    return sprintf('%.2f '.$symbol[$exp], ($bytes / pow(1024, floor($exp))));
}
//$disk_space = disk_free_space("/") ;

  $total_disk_space = disk_total_space('/');
  $bytes = disk_free_space('/');
  $si_prefix = array('B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB');
  $base = 1024;
  $class = min((int) log($bytes, $base), count($si_prefix) - 1);
  $total = min((int) log($total_disk_space, $base), count($si_prefix) - 1);

  echo sprintf('<div id="freespace"><li class="list-group-item">Free space: '.'%1.2f', $bytes / pow($base, $class)).' / '.
  getSymbolByQuantity($total_disk_space).'  '.$si_prefix[$class].'</li></div>';

  for ($i = 2; $i <= $amount_files+1;  ++$i) {
      $filesize = filesize('../data/Saves/'.$scanned_directory[$i]);
      $filesize = getSymbolByQuantity($filesize);
      $f = $scanned_directory[$i];
      $fn = substr($f, 0, -4);
      $d = $scanned_directory[$i];
      $fi = '../data/Saves/'.$scanned_directory[$i];

  //    $t = date("F d Y H:i:s.", filectime($fi));
      echo     '<button class='.'filesButtons'.' data-fid ='."$f".' data-filehid ='."$d".' ><p>'.$fn.' '.$filesize.'<br>'.$t.'</p>'.
          '</button>'.'<button class='.'"delFilButtons" data-id ='."$fn".'> <p><i class="fa fa-trash-o"></i><br> Delete'.'</button>'.
          '</button>'.'<button class='.'"renameFileBtn" data-id ='."$fn".'> <p><i class="fa fa-file-text-o"></i><br> Rename'.'</button>'.
      '</button>'.'<button class='.'"sendFileBtn" data-id ='."$fn".'> <p><i class="fa fa-paper-plane"></i><br> Send'.'</button>'.
          '</br></div>';
  }
  ?>
</html>

<script type="text/javascript">


function progressbar(x, max){
	var progress = 0 ;
	progress = parseInt(x / max * 100);
	var pr = progress + "%";
	var html = "Please Wait "+ pr	+"<div class=" + "'progress progress-micro'"+">	<div class="+
	"'progress-bar bg-color-blueLight'"+" role='progressbar'" + "style='width: "+pr+";'"+">" +
	 "</div></div>"
   console.log("progressbar");
	$( "#progresZniffer" ).html( html );

  var gridlenght =w2ui.grid.records.length;
  if (progress >= 100  ){
		$( "#progresZniffer" ).html( " " );
	}

}


$('.filesButtons').on('click', function(){
    var atr = '../data/Saves/' + $(this).attr('data-fid');
		var atrh = $(this).attr('data-filehid');
    var NumberofLines = 0;
    atrh = atrh.slice(0, -4);
    var atr2 = '../data/SaveData/' + atrh + '.txt';
    open_file(atr,atr2 );
		$("#opened_filename").text("Opened file: " + atrh.slice(0, -4));


w2popup.close();

});

$('.delFilButtons').on('click', function () {
	var t = $(this).attr('data-id');

        $.SmartMessageBox({
          title: "Z-Wave Packet Analyzer",
          content: "Are you sure?",
          buttons : "[No][Yes]",
        }, function(ButtonPress, Value){
          if (ButtonPress == "Yes"){
            $.ajax({
          		url: 'ajax/delete_file.php',
          		type: 'POST',
          		data: { fileName: t},
          		success: function(response){
          			console.log("deleted");
          			$( "#popupmain" ).load( "ajax/open_file.php" );
              }
          });
        }
        if (ButtonPress == "No"){

        }
        })

})

$('.renameFileBtn').on('click', function () {
	var t =  $(this).attr('data-id');

	$.SmartMessageBox({
    title : "Z-Wave Packet Analyzer",
    content : "Please enter new filename",
    buttons : "[Cancel][Save]",
    input : "text",
    placeholder : "Enter new filename"
	}, function(ButtonPress, Value) {

	if (ButtonPress === "Save") {
		$.ajax({
			url: 'ajax/delete_file.php',
			type: 'POST',
			data: { fileName: t, todo: 1, newName: Value},
			success: function(response){
				$( "#popupmain" ).load( "ajax/open_file.php" );
				}
		});

}
if (ButtonPress=== "Cancel") {

}

	});

})

$('.sendFileBtn').on('click', function () {
  var atr = '../data/Saves/' + $(this).attr('data-id');

  var send_files_window_html = '<textarea rows="5" placeholder="Put your message here." name="message" id="send_files_msg"></textarea>' +
  '<button id="sendFileBtn2"> Send </button>';

	$( "#popupmain" ).html(send_files_window_html);

    $('#sendFileBtn2').on('click', function(){
      $.ajax({
        url: 'ajax/emailcontacts.php',
        type: 'POST',
        data: { fileName: atr, request: "send2zwave"},
        success: function(response){
          console.log(response);
          if (response == "NOINTERNET")
            $( "#popupmain" ).html("Error! Check your internet connection!");
          else if (response == "NOEMAIL") {
            $( "#popupmain" ).html("Error! Set your email in settings!");
          }else {
            $( "#popupmain" ).html("Done!");
          }
        },
        error: function () {
          $( "#popupmain" ).html("Error!");

        }

      });
    });

});


/////////////////////////////////////////////


	pageSetUp();
	var pagefunction = function() {
		// clears the variable if left blank
	};
	// end pagefunction
	// run pagefunction
	pagefunction();
</script>
