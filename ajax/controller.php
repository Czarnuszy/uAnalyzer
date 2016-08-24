
<?php
	function readCSV($csvFile){
		$file_handle = fopen($csvFile, 'r');
			while (!feof($file_handle) ) {
				$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle);
	return $line_of_text;
}

	$csvFile = 'NetworkConnections.csv';

	$NetworkConnections = readCSV($csvFile);
	$max = count($NetworkConnections);
?>

<script>


	var max = <?php echo $max ?>;
	var div = "";
	//dynamic div
		for(i = 0; i < max*max; i++){
					var element = "sq" + i;
					div = div + '<div class ="sq" id="'+element+'"></div>';
				if((i+1)%max == 0)
					div = div +'<div style = "clear:both;"></div>';

			}



	var data=[<?php
				for ($i=0; $i < $max; $i++){
					for ($k=0; $k < $max; $k++)
						echo  json_encode($NetworkConnections[$i][$k] ).',';
					}
				    	?>];

			//print

	$(function(){

			$("#controller").html(div);

			for(i = 0; i < max*max; i++){
				if(i <max || ((i+1)%max)==0 ){
					var k=i+1;
					$("#sq"+k).css({background: '#006699' });
			}
		}
	});



	$(function(){
		var clientWidth = document.getElementById('controller').clientWidth;
		var size = (clientWidth/max)-1;
		for(i = 0; i < max*max; i++){
			$("#sq"+i).css({width: size,
							height: size})
			painting();

		}
	});

function painting(){

	$(function(){

		for(i = 0; i < max*max; i++){
					//var k=i+1;
					$("#sq"+i).html(data[i]);

				if($("#sq"+i).html() == "NC")
					$("#sq"+i).css({background: 'white',
									fontSize: 0,
									opacity: 1});
				else if($("#sq"+i).html() == "C")
					$("#sq"+i).css({background: 'green',
									 fontSize: 0,
									 opacity: 1 });
				else if($("#sq"+i).html() == "")
						$("#sq"+i).css({background: 'red',
										 fontSize: 0,
										 opacity: 1 });

			}

			for(i = 0; i < max*max; i++){
				if(i <max || ((i+1)%max)==0 ){
					var k=i+1;
					$("#sq"+k).css({background: '#006699',
					opacity: 1 });
					}
				$("#sq0").css({background: '#006699',
				opacity: 1 });
			}

		});
	}

	$(".sq").on({
    mouseenter: function () {
		var divID = $(this).attr('id');
		var divNB = divID.slice(2);
		var k = divNB%max;

    			$('#'+divID).css({opacity: 0.7});

    			for(i = (divNB-k); i < divNB; i++)
    				$('#sq'+i).css({opacity: 0.7});
    			for(i = k; i < divNB  ; i+=max)
    				$('#sq'+i).css({opacity: 0.7});

    		    },
    mouseleave: function () {
        //stuff to do on mouse leave
        painting();


    	}
	});




</script>


<body>

	<div id="conteiner">
		<div id="controller"></div>
	</div>


</body>
