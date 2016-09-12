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

    });

var devicesStatus = (function () {

	$startBtn = $('#getStatusBtn');

	$startBtn.on('click', start);

	$.ajax({
    url: 'data/ima/routing_info.csv',
    success: function (data) {
			w2ui['connectionGrid'].clear();

      routingInfo = parse.CSVToArray(data);
      for (var i = 0; i < routingInfo.length-1; i++){
          w2ui['connectionGrid'].records.push({
          		recid: i,
          		dev: routingInfo[i][0],
          		status: "Pending",
          		time: "Pending",
          		route: "Pending",
          });
      }
      w2ui['connectionGrid'].refresh();

    }
  })


	function start() {
		w2ui['connectionGrid'].lock("Please wait.", true);


		$.ajax({
				url: 'data/ima/routing_info.csv',
				success: function (data) {
					routingInfo = parse.CSVToArray(data);
					devices = [];

					for (var i = 0; i < routingInfo.length-1; i++){
							devices.push(routingInfo[i][0]);

					}


			//		console.log(allrec);
					w2ui['connectionGrid'].refresh();


			var current = 0;
			var i = 0;
			get_dev_status();

				function get_dev_status() {

					if (current < devices.length) {
						dev = devices[current];
					//	console.log();
							$.ajax({
									url: 'ajax/send_dev_req.php',
									type: 'POST',
									data: {dev: dev},
									success: function (resp) {
									//	console.log(resp);
										if (resp == 'done') {
									//		console.log(dev);
											devid = dev;
											$.ajax({
													url: 'data/ima/device_status.csv',
													success: function (stat) {
														size = w2ui['connectionGrid'].records.length;
														allrec = [];
														for (var x = 0; x < size; x++)
															allrec.push(w2ui['connectionGrid'].get(x));

															console.log(stat);
															stat = parse.CSVToArray(stat);
															console.log(current);
															allrec[current].status = stat[0][0];
															allrec[current].time = stat[0][1];
															allrec[current].route = stat[0][3];

													
															w2ui['connectionGrid'].clear();
															for (var i = 0; i < size; i++) {
																if (allrec[i].status == 'Fail') {
																	color = "#FF4C4C"
																}else {
																	color = '';
																}
																w2ui['connectionGrid'].records.push({
																		recid: i,
																		dev: allrec[i].dev,
																		status: allrec[i].status,
																		time: allrec[i].time,
															//			repeaters: stat[0][2],
																		route: allrec[i].route,
																		style: "background-color: " + color,
																		});

															}


														// 	w2ui['connectionGrid'].records.push({
														// 			recid: i,
														// 			dev: devid,
														// 			status: stat[0][0],
														// 			time: stat[0][1],
														// //			repeaters: stat[0][2],
														// 			route: stat[0][3],
														// 			style: "background-color: " + color,
														//
														// 			});
																w2ui['connectionGrid'].unlock();

															 	w2ui['connectionGrid'].refresh();
																current++;
																i++;
																get_dev_status();
													}
											})
										}

									},
									error: function () {
											console.log('error');
									}
							})
							}
						}
			//		})

				},
				error: function () {
					console.log(error);
				}
		})

	}




//	request('data/ima/routing_info.csv', 'text', nowRoutingInfo, errorFun);

	function request(_url, _dataType, _onSuccess, _onError) {
			$.ajax({
					url: _url,
					dataType: _dataType,
					success: _onSuccess,
					error: _onError
			})
	}

	return {
		start: start,
	}
})();




    </script>
</body>
</html>
