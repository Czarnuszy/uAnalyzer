
<html>
<head>

</head>
<body>

		<center>

            <div id="NodeInfoGridBody" style="width: 100%; height: 400px;"></div>


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
          			{ field: 'basic', caption: 'basic', size: '33%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
                { field: 'generic', caption: 'generic', size: '33%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },

                { field: 'specific', caption: 'specific', size: '33%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
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


         $.ajax({
           url: 'ajax/read_xml.php',
           type: 'GET',
           dataType: 'json',
           success: function(data){
             $.each(data, function (i, record) {
               w2ui['NodeInfoGrid'].records.push({
                 basic: record.basic,
                 generic: record.generic,
                 specific: record.specific,
                });
             })
             w2ui['NodeInfoGrid'].refresh();

           }

         })

    });
    </script>
</body>
</html>
