<head>
<script src="../js/sigma.plugins.neighborhoods.js"></script>
<script src="../js/sigma.plugins.dragNodes.js"></script>


<head>
<div id="container">
  <div id="graph-container"></div>
</div>
<script>


$.ajax({
		url: '../data/ima/routing_info.csv',
		success: function (data) {
			data = parse.CSVToArray(data);
      console.log(data);
		//	data = CSVToArray(data);
				var xT = [1,2,2,4,4,6,6,7];
				var yT = [4,2,6,1,7,6,2,4];
				var i,
				types = 'dd',
				    s,
				    g = {
				      nodes: [],
				      edges: []
				    };
						_x = 0;

				for (i = 0; i < data.length-1; i++){
					add = 0.3;
			//		console.log(data[i][0] + ' ' +_x);
				  g.nodes.push({
				    id: 'n' + data[i][0],
				    label: 'Dev ' + data[i][0],
				//		x: xT[i]/7,
				    x: i/10+0.1+0.001,
				    y: 1.8*Math.random()+add,
					//	y: yT[i]/7,
				    size: 3,
						type: types,
				    color: '#666'
				  });
				//	console.log(data[i][0] + ' '+ i/8 + ' ' + 1.8*Math.random());
					add+= 0.1;
					_x+=0.2;
				}

			for (i = 0; i < data.length-1; i++)
					for (var x = 0; x < data[i].length-1; x++)
							g.edges.push({
						    id: 'e' + data[i][0]+'to'+data[i][x+1],
						    source: 'n' + data[i][0],
						    target: 'n' + data[i][x],
						    size: Math.random(),
						    color: '#ccc',
								hover_color: '#000'
						  });

				console.log(g.nodes[0].type);
				console.log(g.edges[0].color);

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
				  freeStyle: true,
				});

				s.refresh();

				// Binding silly interactions
				function mute(node) {
				  if (!~node.getAttribute('class').search(/muted/))
				    node.setAttributeNS(null, 'class', node.getAttribute('class') + ' muted');
					//	g.edges[0].
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

				// var dragListener = sigma.plugins.dragNodes(s, s.renderers[0]);
				//
				// dragListener.bind('startdrag', function(event) {
				//   	console.log(event);
				// });
				// dragListener.bind('drag', function(event) {
				//   	console.log(event);
				// });
				// dragListener.bind('drop', function(event) {
				//   	console.log(event);
				// });
				// dragListener.bind('dragend', function(event) {
				//   	console.log(event);
				// });

		}
})





</script>
