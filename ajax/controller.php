<head>
<script src="../js/sigma.plugins.neighborhoods.js"></script>
<script src="../js/sigma.plugins.dragNodes.js"></script>


<head>
<div id="container">
  <div id="graph-container"></div>
</div>
<script>

var selectedDevId = 'none';
var $neightUpdateBtn = $('#updateNeighbors');
$.ajax({
		url: '../data/ima/routing_info.csv',
		success: function (data) {
			data = parse.CSVToArray(data);
      console.log(data);

      sigma.classes.graph.addMethod('neighbors', function(nodeId) {
        var k,
            neighbors = {},
            index = this.allNeighborsIndex[nodeId] || {};

        for (k in index)
          neighbors[k] = this.nodesIndex[k];
          console.log(neighbors);
        return neighbors;
      });

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
				    id: data[i][0],
				    label: 'Dev ' + data[i][0],
				//		x: xT[i]/7,
				    x: i/10+0.1+0.001,
				    y: 1.8*Math.random()+add,
					//	y: yT[i]/7,
				    size: 3,
						type: types,
				    color: '#666',
            devid: data[i][0],
				  });
				//	console.log(data[i][0] + ' '+ i/8 + ' ' + 1.8*Math.random());
					add+= 0.1;
					_x+=0.2;
				}

			for (i = 0; i < data.length-1; i++)
					for (var x = 0; x < data[i].length-1; x++)
							g.edges.push({
  						    id: 'e' + data[i][0]+'to'+data[i][x+1],
  						    source:  data[i][0],
  						    target:  data[i][x],
  						    size: Math.random(),
  						    color: '#ccc',
  								hover_color: '#000'
						  });

				console.log(g.nodes[0].type);
				console.log(g.edges[0].color);
        console.log(g.nodes[0].id);

        s = new sigma({
        graph: g,
        container: 'graph-container',
        	  settings: {
                enableHovering: true,

        	  }
      });

            g.nodes.forEach(function(n) {
               n.originalColor = n.color;
             });
             g.edges.forEach(function(e) {
               e.originalColor = e.color;
             });

             // When a node is clicked, we check for each node
             // if it is a neighbor of the clicked one. If not,
             // we set its color as grey, and else, it takes its
             // original color.
             // We do the same for the edges, and we only keep
             // edges that have both extremities colored.
             s.bind('clickNode', function(e) {
               var nodeId = e.data.node.id,
                   toKeep = s.graph.neighbors(nodeId);
               toKeep[nodeId] = e.data.node;
            //   console.log(toKeep[0][0]);
               s.graph.nodes().forEach(function(n) {
              //   console.log(n);
                 if (toKeep[n.id])
                   n.color = n.originalColor;
                 else
                   n.color = '#eee';
               });

               selectedDevId = nodeId;
               console.log(selectedDevId);
               if (selectedDevId != 'none')
                   $neightUpdateBtn.attr('disabled', false);

               s.graph.edges().forEach(function(e) {
                 if (e.target == selectedDevId)
                   e.color = e.originalColor;
                 else
                   e.color = '#eee';

                //  if (toKeep[e.source] && toKeep[e.target])
                //    e.color = e.originalColor;
                //  else
                //    e.color = '#eee';
               });

               // Since the data has been modified, we need to
               // call the refresh method to make the colors
               // update effective.
               s.refresh();
             });

             // When the stage is clicked, we just color each
             // node and edge with its original color.
             s.bind('clickStage', function(e) {
               s.graph.nodes().forEach(function(n) {
                 n.color = n.originalColor;
               });

               s.graph.edges().forEach(function(e) {
                 e.color = e.originalColor;
               });
               selectedDevId = 'none';
               $neightUpdateBtn.attr('disabled', true);
               // Same as in the previous event:
               s.refresh();
             });




				s.refresh();


				var dragListener = sigma.plugins.dragNodes(s, s.renderers[0]);

				dragListener.bind('startdrag', function(event) {
				  //	console.log(event);
				});
				dragListener.bind('drag', function(event) {
				  //	console.log(event);
				});
				dragListener.bind('drop', function(event) {
				  //	console.log(event);
				});
				dragListener.bind('dragend', function(event) {
				  	//console.log(event);
				});

		}
})





</script>
