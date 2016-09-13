
<html>
<head>
	<script src="../js/xml_parser.js"></script>

</head>
<body>

		<center>

            <div id="testDevBody" style="width: 100%; height: 600px;"></div>

		</center>
    <script>
    var $testBtn = $('#testBtn');

    var record = 'none';
    var config = {
        testDevGrid: {
            name: 'testDevGrid',
            show: {
                 lineNumbers  : true,
            },
            columns: [
  							{ field: 'dev', caption: 'dev', size: '10%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },
                { field: 'specific', caption: 'specific', size: '30%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
                { field: 'result', caption: 'Result', size: '30%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },
    				 ],
             onClick: function (event) {
                 record = this.get(event.recid);
                 $testBtn.attr('disabled', false);

             }

        }
    }




    $(function () {
        $().w2grid(config.testDevGrid);
        w2ui['testDevGrid'].refresh();
        $('#testDevBody').w2render('testDevGrid');



    });



    </script>
</body>
</html>
