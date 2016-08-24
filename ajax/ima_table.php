
<html>
<head>

</head>
<body>

		<center>

            <div id="NodeInfoGridBody" style="width: 100%; height: 600px;"></div>


		</center>
    <script>
    var config = {
        NodeInfoGrid: {
            name: 'NodeInfoGrid',
            show: {
                footer    : true,
                toolbar    : true,
                 lineNumbers  : true,
            },
            columns: [
          			{ field: 'basic', caption: 'basic', size: '50%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
                { field: 'specific', caption: 'specific', size: '50%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
        //        { field: 'source', caption: 'Source', size: '10%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },

    		//{ field: 'test2', caption: 'test2', size: '100px', type: "text", sortable: true, searchable: 'text',  resizable: true },
    				],

        }
    }
    $(function () {
        // initialization
        $().w2grid(config.NodeInfoGrid);

        w2ui['NodeInfoGrid'].records.push({
          basic: 'gfd',
          specific: 'fdfd',

         });

         w2ui['NodeInfoGrid'].refresh();
         $('#NodeInfoGridBody').w2render('NodeInfoGrid');

    });
    </script>
</body>
</html>
