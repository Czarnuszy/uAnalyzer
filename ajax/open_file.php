<?php
$directory = '../data/Saves';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
$amount_files = count($scanned_directory);
$fi = "../data/Saves/".$scanned_directory[2];
//if (file_exists($fi))
  //  $t = date("F d Y H:i:s.", filectime($fi));
?>

<html>
<?php
for($i = 2; $i <= $amount_files; $i+=3){
		$f = $scanned_directory[$i];
		$fn = $f; // substr($f, 0, -4);
		$d = $scanned_directory[$i+1];
		$fi = "../data/Saves/".$scanned_directory[$i];
  //  $t = date("F d Y H:i:s.", filectime($fi));
    echo  '<button class='."filesButtons".' data-fid ='."$f".' data-filehid ='."$d".' ><p>'.$fn.'<br>'.$f.'</p></br></div>';
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

    open_file(atr);

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
