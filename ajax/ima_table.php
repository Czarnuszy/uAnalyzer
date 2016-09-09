
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
							{ field: 'dev', caption: 'dev', size: '10%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },

          			{ field: 'basic', caption: 'basic', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
                { field: 'generic', caption: 'generic', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },

                { field: 'specific', caption: 'specific', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
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


        //  $.ajax({
        //    url: 'ajax/read_xml.php',
        //    type: 'GET',
        //    dataType: 'json',
        //    success: function(data){
				// 		 $('#NodeInfoGridBody').html(data);
				// 		 w2ui.NodeInfoGrid.clear();
				//
        //      $.each(data, function (i, record) {
        //        w2ui['NodeInfoGrid'].records.push({
        //          basic: record.basic,
        //          generic: record.generic,
        //          specific: record.specific,
        //         });
        //      })
        //      w2ui['NodeInfoGrid'].refresh();
				//
        //    }
        //  })
				//
				//
				//  $.ajax({
				// 	 url: '../data/ima/routing_info.csv',
				// 	 type: 'GET',
				// 	 success: function(data){
				// 		data = parse.CSVToArray(data);
				// 		w2ui.NodeInfoGrid.clear();
				// 		 $.each(data, function (i, record) {
				// 			 w2ui['NodeInfoGrid'].records.push({
				// 				 	dev : 1,
				// 				});
				// 		 })
				// 		 w2ui['NodeInfoGrid'].refresh();
				 //
				// 	 },
				// 	 error: function () {
				// 	 		console.log('error');
				// 	 }
				//  })

			 xmlParser.start();
			
    });



    </script>
</body>
</html>
