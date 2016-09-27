<html>
<head>
<script type="text/javascript" src="../js/d3.min.js"></script>
</head>
<body>

<div id="chart"></div>


<script type="text/javascript">

</script>
</body>
</html>

<script>

$.ajax({
		url: '../data/ima/static_routing_info.csv',
		success: function (data) {

			devData = parse.CSVToArray(data);
			var devs = devData.length-1;
  		  renderGrid('#chart', 400,400, true);
					//console.log(devData);


function renderGrid(id, width, height, square)
{
    var calData = generateData(width, height, square);
    console.log(calData);
body = d3.select('body')
grid = body.append('svg').attr('height', 600)
.attr('width', 600)
.style("margin-left", 500 + "px")


  /*  var grid = d3.select(id).append("svg")
                    .attr("width", width)
                    .attr("height", height)
                    .attr("class", "chart");
*/
    var row = grid.selectAll("g")
                  .data(calData)
                .enter().append("svg:g");
               //   .attr("class", "row");

var painting = function(d){
		var  color = d3.select(this)[0][0].attributes.color.value;
					//console.log(color);
			return color
	}



    var col = row.selectAll("g")
                 .data(function (d) { return d; })
                 .enter().append("rect")
		//.enter().append("text")
                // .attr("class", "cell")
                 .attr("x", function(d) { return d.x; })
                 .attr("y", function(d) { return d.y; })
                 .attr("width", function(d) { return d.width; })
                 .attr("height", function(d) { return d.height; })
                 .attr("color", function(d) {  return d.color; })
          		   .attr("text-anchor", "middle")
        		     .style("font-size", "14px")
        		     .text('hello')
                 .on('mouseover', function() {
                    d3.select(this)
                        .style('fill', '#0F0');
                 })
                 .on('mouseout', function() {
                    d3.select(this)
                        .style('fill', painting);
                 })
                 .on('click', function() {
                    console.log(d3.select(this));

                 })
                 .style("fill", painting)
	               .style("fillText", 'ddd')
                 .style("stroke", '#555')


	console.log(row[0][0]);

for(x=0; x<1; x++){
var te3xt = row.selectAll("text")
               .data(calData)
           		.enter()
               .append("text");
}


var textLabel3s = te3xt
                 .attr("x", function(d) { return d[0].x+5; })
                 .attr("y", function(d) { return d[0].y+20; })
                 .text( function (d) { return d[0].value ; })
                 .attr("font-family", "sans-serif")
                 .attr("font-size", "20px")
                 .attr("fill", "black");
}

////////////////////////////////////////////////////////////////////////

function generateData(gridWidth, gridHeight, square)
{
    var clientWidth = document.getElementById('staticConnectionGridBody').clientWidth;
    console.log(clientWidth);
    var data = new Array();
    var gridItemWidth = clientWidth / devs;
    var gridItemHeight = (square) ? gridItemWidth : gridHeight / devs;
    var startX = gridItemWidth;
    var startY = gridItemHeight;
    var stepX = gridItemWidth;
    var stepY = gridItemHeight;
    var xpos = startX;
    var ypos = startY;
    var newValue = 0;
    var count = 0;

    for (var index_a = 0; index_a < devs-1; index_a++)
    {
        data.push(new Array());
        for (var index_b = 0; index_b < devs-1; index_b++)
        {
           // newValue = Math.round(Math.random() * (100 - 1) + 1);
		newValue = devData[index_a][index_b];
console.log(newValue);
		if (newValue == 1)
			Color = 'green';
		else if (newValue == 0)
			Color = 'red';
		else if (newValue == 2)
			Color = 'white';
		if(index_b < 1)
			Color = 'blue';
            data[index_a].push({
                                time: index_b,
                                value: newValue,
                                width: gridItemWidth,
                                height: gridItemHeight,
                                x: xpos,
                                y: ypos,
                                count: count,
				color: Color,
				text: 'dd'
                            });
            xpos += stepX;
            count += 1;
        }
        xpos = startX;
        ypos += stepY;
    }
    return data;
}

		}
})


</script>
