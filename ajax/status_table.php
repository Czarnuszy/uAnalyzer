<html>
<head>

</head>
<body>

		<center>

            <div id="connectionGridBody" style="width: 100%; height: 600px;"></div>
						<div id='testingDiv'></div>

		</center>
    <script>
    var config = {
        connectionGrid: {
            name: 'connectionGrid',
            show: {
            //    footer    : true,
              //  toolbar    : true,
                 lineNumbers  : true,
            },
            columns: [
							{ field: 'dev', caption: 'Device ID', size: '20%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },
              { field: 'status', caption: 'Status', size: '20%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
              { field: 'time', caption: 'Time', size: '20%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },
						//	{ field: 'repeaters', caption: 'Repeaters', size: '40%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },
    					{ field: 'route', caption: 'Route', size: '40%', type: "text", sortable: true, searchable: 'text',  resizable: true, attr: "align=center" },
    				 ],

        }
    }




    $(function () {
        // initialization
        $().w2grid(config.connectionGrid);

         $('#connectionGridBody').w2render('connectionGrid');


				w2ui['connectionGrid'].refresh();

		//	 xmlParser.start();
			statusTable.fillGrid();
    });




    </script>
</body>
</html>
