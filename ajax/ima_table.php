
<html>
<head>
	<script src="../js/xml_parser.js"></script>

</head>
<body>

		<center>

            <div id="NodeInfoGridBody" style="width: 100%; height: 600px;"></div>
						<div id='testingDiv'></div>

		</center>
    <script>
    var config = {
        NodeInfoGrid: {
            name: 'NodeInfoGrid',
            show: {
            //    footer    : true,
              //  toolbar    : true,
                 lineNumbers  : true,
            },
            columns: [
							{ field: 'dev', caption: 'Device ID', size: '10%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },

        			{ field: 'basic', caption: 'Basic', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
              { field: 'generic', caption: 'Generic', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },

              { field: 'specific', caption: 'Specific', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
        //        { field: 'source', caption: 'Source', size: '10%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },

    		//{ field: 'test2', caption: 'test2', size: '100px', type: "text", sortable: true, searchable: 'text',  resizable: true },
    				 ],

        }
    }




    $(function () {
        // initialization
        $().w2grid(config.NodeInfoGrid);

         w2ui['NodeInfoGrid'].refresh();
         $('#NodeInfoGridBody').w2render('NodeInfoGrid');

				 xmlParser.start();

    });



    </script>
</body>
</html>
