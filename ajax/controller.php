
<?php

    function readCSV($csvFile)
    {
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);

        return $line_of_text;
    }

    $csvFile = '../data/ima/routing_info.csv';

    $NetworkConnections = readCSV($csvFile);
    $max = count($NetworkConnections);
    // for ($i=1; $i < $max; $i++) {
    // 	echo $NetworkConnections[$i][1];
    // 	echo "\n";
		// 	echo "\n";
		// 	echo '</br>';
    // }
?>
<head>
<script src="../js/sigma.plugins.neighborhoods.js"></script>

<head>
<div id="container">
  <style>
    #graph-container {
      top: 5;
      bottom: 5;
      left: 0;
      right: 0;
			height: 600px;
    }
		.sigma-edge {
      stroke: #14191C;
    }
    .sigma-node {
      fill: green;
      stroke: #14191C;
      stroke-width: 2px;
    }
    .sigma-node:hover {
      fill: blue;
    }
    .muted {
      fill-opacity: 0.1;
      stroke-opacity: 0.1;
    }
  </style>
  <div id="graph-container"></div>
</div>
<script>


$.ajax({
		url: '../data/ima/routing_info.csv',
		success: function (data) {
			data = parse.CSVToArray(data);
		//	data = CSVToArray(data);

				console.log(Math.random());
				var i,
				    s,
				    g = {
				      nodes: [],
				      edges: []
				    };
				// Generate a random graph:
				for (i = 0; i < data.length-1; i++){
					add = 0.3;
				  g.nodes.push({
				    id: 'n' + data[i][0],
				    label: 'Dev ' + data[i][0],
				    x: i/10+0.1+0.001,
				    y: 1.8*Math.random()+add,
				    size: 3,
				    color: '#666'
				  });
					console.log(data[i][0] + ' '+ i/8 + ' ' + 1.8*Math.random());
					add+= 0.1;
				}

					for (i = 0; i < data.length-1; i++)
							for (var x = 0; x < data[i].length-2; x++)
								g.edges.push({
							    id: 'e' + [i]+[x+1],
							    source: 'n' + data[i][0],
							    target: 'n' + data[i][x+1],
							    size: Math.random(),
							    color: '#ccc',
									hover_color: '#000'
							//		type: ['line', 'curve', 'arrow', 'curvedArrow'][Math.random() * 4 | 0]
							  });
				// Instantiate sigma:
				// s = new sigma({
				//   graph: g,
				//   container: 'graph-container'
				// });
				// Instantiate sigma:
				s = new sigma({
				  graph: g,
				  settings: {
				    enableHovering: false,
						enableEdgeHovering: true,
					 edgeHoverColor: 'edge',
					 defaultEdgeHoverColor: '#000',
					 edgeHoverSizeRatio: 1,
					 edgeHoverExtremities: true,
				  }
				});

				s.addRenderer({
				  id: 'main',
				  type: 'svg',
				  container: document.getElementById('graph-container'),
				  freeStyle: true
				});

				s.refresh();

				// Binding silly interactions
				function mute(node) {
				  if (!~node.getAttribute('class').search(/muted/))
				    node.setAttributeNS(null, 'class', node.getAttribute('class') + ' muted');
				}

				function unmute(node) {
				  node.setAttributeNS(null, 'class', node.getAttribute('class').replace(/(\s|^)muted(\s|$)/g, '$2'));
				}

				$('.sigma-node').click(function() {

				  // Muting
				  $('.sigma-node, .sigma-edge').each(function() {
				    mute(this);
				  });

				  // Unmuting neighbors
				  var neighbors = s.graph.neighborhood($(this).attr('data-node-id'));
				  neighbors.nodes.forEach(function(node) {
				    unmute($('[data-node-id="' + node.id + '"]')[0]);
				  });

				  neighbors.edges.forEach(function(edge) {
				    unmute($('[data-edge-id="' + edge.id + '"]')[0]);
				  });
				});

				s.bind('clickStage', function() {
				  $('.sigma-node, .sigma-edge').each(function() {
				    unmute(this);
				  });
				});

		}
})



//  	var max = <?php echo $max ?>;
// 	console.log(max);
//  	var div = "";
// // 	//dynamic div
// 		for(i = 0; i < max*max; i++){
// 					var element = "sq" + i;
// 					div = div + '<div class ="sq" id="'+element+'"></div>';
// 				if((i+1)%max == 0)
// 					div = div +'<div style = "clear:both;"></div>';
//
// 			}
// //
// //
// //
// 	var data=[<?php
//         for ($i = 0; $i < $max; ++$i) {
//             echo $NetworkConnections[$i][1];
//             echo ',';
//         }
//
//                         ?>];
//
// 			//print
//
// 	$(function(){
//
// 			$("#controller").html(div);
//
// 			for(i = 0; i < max*max; i++){
// 				if(i <max || ((i+1)%max)==0 ){
// 					var k=i+1;
// 					$("#sq"+k).css({background: '#006699' });
// 			}
// 		}
// 	});
//
//
	//
	// $(function(){
	// 	var clientWidth = document.getElementById('controller').clientWidth;
	// 	var size = (clientWidth/max)-1;
	// 	for(i = 0; i < max*max; i++){
	// 		$("#sq"+i).css({width: size,
	// 						height: size})
	// 		painting();
	//
	// 	}
	// });

// function painting(){
//
// 	$(function(){
//
// 		for(i = 0; i < max*max; i++){
// 					//var k=i+1;
// 					$("#sq"+i).html(data[i]);
//
// 				if($("#sq"+i).html() == "NC")
// 					$("#sq"+i).css({background: 'white',
// 									fontSize: 0,
// 									opacity: 1});
// 				else if($("#sq"+i).html() == "C")
// 					$("#sq"+i).css({background: 'green',
// 									 fontSize: 0,
// 									 opacity: 1 });
// 				else if($("#sq"+i).html() == "")
// 						$("#sq"+i).css({background: 'red',
// 										 fontSize: 0,
// 										 opacity: 1 });
//
// 			}
//
// 			for(i = 0; i < max*max; i++){
// 				if(i <max || ((i+1)%max)==0 ){
// 					var k=i+1;
// 					$("#sq"+k).css({background: '#006699',
// 					opacity: 1 });
// 					}
// 				$("#sq0").css({background: '#006699',
// 				opacity: 1 });
// 			}
//
// 		});
// 	}
//
// 	$(".sq").on({
//     mouseenter: function () {
// 		var divID = $(this).attr('id');
// 		var divNB = divID.slice(2);
// 		var k = divNB%max;
//
//     			$('#'+divID).css({opacity: 0.7});
//
//     			for(i = (divNB-k); i < divNB; i++)
//     				$('#sq'+i).css({opacity: 0.7});
//     			for(i = k; i < divNB  ; i+=max)
//     				$('#sq'+i).css({opacity: 0.7});
//
//     		    },
//     mouseleave: function () {
//         //stuff to do on mouse leave
//         painting();
//
//
//     	}
// 	});




</script>
