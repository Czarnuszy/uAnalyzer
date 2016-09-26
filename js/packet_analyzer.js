var packetAnalyzer = (function() {

    var home_id = '';
    var is_zniffer_on = false;
    var radioButton = '';
    var checkStatusIntV;
    //cache DOM
    var $el = $('#zniffer_header');
    var $startBtn = $el.find('#play-a1');
    var $pauseBtn = $el.find('#pause-a1');
    var $stopBtn = $el.find('#stop-a1');
    var $openBtn = $el.find('#open');
    var $joinBtn = $el.find('#join-a1');
    var $saveBtn = $el.find('#save-a1');
    var $refreshBtn = $el.find('#refresh-a1');
    var $networkChbx = $el.find('#network_checkbox');
    var $widgetBody = $("#body-w");

    //binds events
    $startBtn.on('click', onStartClick);
    $pauseBtn.on('click', onPauseClick);
    $stopBtn.on('click', onStopClick);
    $openBtn.on('click', onOpenClick);
    $joinBtn.on('click', onJoinClick);
    $saveBtn.on('click', onSaveClick);
    $refreshBtn.on('click', onRefreshClick);
    $networkChbx.on('click', onNetworkSwitch);


    function _init() {
        load();
        znifferStatus(onloadZnifferStatus);
        console.log(checkStatusIntV);
        //clearInterval(checkStatusINt);

        checkStatusInt = setInterval(function() {
            znifferStatus(returnZnifferStatus);
        }, 1000);

    }


    _init();


    //Buttons functions

    function onStartClick() {
        if (radioButton == "stop") {
            start_analyzer();
            console.log("start after stop");
            w2ui.grid.clear();
            w2ui.grid.lock('Getting ready.', true);
            is_zniffer_on = true;
            setTimeout(refresh, 1000);
        } else if (radioButton == "pause") {
            console.log("start after pause");
            load();
            is_zniffer_on = true;
            setTimeout(refresh, 600);
        } else {
            console.log(radioButton);
            console.log('start button exception');
        }
    }

    function onPauseClick() {
        if (radioButton == 'start') {
            is_zniffer_on = false;
            console.log("pause after start");
        } else if (radioButton == 'stop') {
            is_zniffer_on = false;
            console.log("pause after stop");
        }
    }

    function onStopClick() {
        $.SmartMessageBox({
            title: "Z-Wave Packet Analyzer",
            content: "Are sure to stop? This will clear the trace",
            buttons: "[STOP][Cancel]",
        }, function(ButtonPress, Value) {
            if (ButtonPress === "STOP") {
                if (radioButton == "start") {
                    console.log("stop after start k");
                    is_zniffer_on = false
                    stop_analyzer();
                } else if (radioButton == "pause") {
                    console.log("stop after pause");
                    stop_analyzer();
                } else if (radioButton == "stop") {
                    console.log("stop after stop");
                    stop_analyzer();
                    is_zniffer_on = false;
                }
            } else if (ButtonPress === "Cancel") {
                if (is_zniffer_on) {
                    radioButton = "start";
                    setTimeout(refresh, 200);
                } else {
                    radioButton = "stop";
                }

            }
        });
    }

    function onOpenClick() {
        console.log('Open click');
        openPopup();
        $("#popupmain").load("ajax/open_file.php");
    }

    function onJoinClick() {
        console.log('Join click');
        $.SmartMessageBox({
            title: "Z-Wave Packet Analyzer",
            content: "Please put controller into Add Device mode",
            buttons: "[Continue][Cancel]",
            placeholder: "Enter filename"
        }, function(ButtonPress, Value) {

            if (ButtonPress === "Continue") {
                start_analyzer();
                w2ui.grid.lock('In progres', true);
                hid_interval = setInterval(function() {
                    findHomeID(onHomeID);
                }, 2000);
            }
            if (ButtonPress === "Cancel") {

            }
        });
    }

    function onSaveClick() {
        $.SmartMessageBox({
            title: "Z-Wave Packet Analyzer",
            content: "Please enter filename",
            buttons: "[Cancel][Save]",
            input: "text",
            placeholder: "Enter filename"
        }, function(ButtonPress, Value) {
            if (ButtonPress === "Save") {
                $widgetBody.load("ajax/savetrace.php?filename=" + Value);
                $.smallBox({
                    title: "Z-Wave Packet Analyzer",
                    content: "<i>Trace saved to file :</i>",
                    color: "#659265",
                    iconSmall: "fa fa-check fa-2x fadeInRight animated",
                    timeout: 2000
                });

            }
            if (ButtonPress === "Cancel") {
                $.smallBox({
                    title: "Z-Wave Packet Analyzer",
                    content: "<i class='fa fa-clock-o'></i> <i>Aborted...</i>",
                    color: "#C46A69",
                    iconSmall: "fa fa-times fa-2x fadeInRight animated",
                    timeout: 4000
                });
            }

        });

    }

    function onRefreshClick() {
        w2ui.grid.clear();
        w2ui.grid.lock('Getting ready.', true);
        $("#opened_filename").text("Actual File");
        load();
    }

    function onNetworkSwitch() {
        if (this.checked)
            w2ui['grid'].search('h_id', home_id);
        else
            w2ui['grid'].search('h_id', '');
    }


    /////End buttons binds /////////

    //TODO rewrite refresh function to do not use PHP///

    function refresh() {

        if (is_zniffer_on) {
            var grid_rec = w2ui.grid.records.length;
            var NumberofLines;
            //  w2ui.grid.lock('Getting ready.', true);

            if (w2ui.grid.records.length > 1)
                w2ui.grid.unlock();

            $.ajax({
                url: "ajax/jsGridData.php",
                type: 'POST',
                data: {
                    startline: grid_rec
                },
                dataType: "json",
                success: function(data) {
                    if (data == null) {
                        console.log("null");
                    } else {

                        NumberofLines = data.length;
                        console.log("records: " + NumberofLines);
                        var color = "";
                        var ZWCommandParsed = "";
                        var ZWparsedRoute = "";
                        var ZWparsedSource = "";
                        var ZWparsedDestination = "";

                        console.log("more");
                        grid_rec = w2ui.grid.records.length;
                        for (x = 0; x < NumberofLines - 1; x++) {

                            //color = "#AD3232";
                            color = "#FA8A8A"
                            if (data[x][2] != home_id) {
                                ZWparsedSource = '-';
                                ZWparsedDestination = '-';
                                ZWparsedRoute = '-';
                                ZWCommandParsed = "";
                            } else {
                                color = parse.sqNum(x, data);
                                ZWCommandParsed = parse.command(data[x]);
                                ZWparsedRoute = parse.route(data[x]);
                                ZWparsedSource = parseInt(data[x][3], 16);
                                ZWparsedDestination = parseInt(data[x][5], 16);
                            }

                            function add_rec() {
                                w2ui['grid'].add({
                                    recid: grid_rec,
                                    id: grid_rec,
                                    rssi: data[x][1],
                                    data: data[x][0],
                                    source: ZWparsedSource,
                                    route: ZWparsedRoute,
                                    destination: ZWparsedDestination,
                                    command: ZWCommandParsed,
                                    h_id: data[x][2],
                                    style: "background-color: " + color,
                                });
                            }
                            //	setTimeout(add_rec, 100);
                            add_rec();
                            grid_rec++;
                        }
                    }
                    setTimeout(refresh, 500);
                    //refresh();
                },
                error: function(err) {
                    console.log(err);
                }

            });
        }
    }

    function load() {
        $("#body-w").load("ajax/jsGrid.php");
    }

    function start_analyzer() {
        $.get("ajax/stick.php");
        return false;
    }

    function stop_analyzer() {
        $.get("ajax/stop_analyzer.php");
        return false;
    }

    function openPopup() {
        w2popup.open({
            title: 'Your Sniffers',
            width: 400,
            height: 400,
            showMax: true,
            body: '<div id="popupmain" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px;"></div>',
        });
    }

    function findHomeID(idfunction) {
        var NumberofLines;
        var file_dir = '../zniffer/data/zniffer.csv';
        $.ajax({
            url: "ajax/open_file_data.php",
            type: 'POST',
            data: {
                data: file_dir
            },
            dataType: "json",
            success: idfunction,
            error: errorFun
        });
    }

    function onHomeID(data) {
        NumberofLines = data.length - 1;
        for (x = 1; x < NumberofLines; x++) {
            if (data[x][7].startsWith("FF 01 08 01")) {
                home_id = data[x][2];
                console.log(home_id);
                w2ui['grid'].unlock();
                stop_analyzer();
                clearInterval(hid_interval);
                w2ui.grid.clear();
                load();

                $.ajax({
                    type: "POST",
                    url: "ajax/homeid_save.php",
                    data: {
                        homeid: home_id
                    },
                });

                $.smallBox({
                    title: "Z-Wave Packet Analyzer",
                    content: "<i class='fa fa-clock-o'></i> <i>Joined!</i>",
                    color: "#659265",
                    iconSmall: "fa fa-check fa-2x fadeInRight animated",
                    timeout: 3000
                });
                break;
            }
        }
    }

    function znifferStatus(setZnifferStatus) {
        $.ajax({
            url: 'ajax/zniffer_status.php',
            dataType: 'json',
            success: setZnifferStatus,
            error: errorFun,

        });
    }

    function returnZnifferStatus(response) {
        if (response == 1) {
            if (is_zniffer_on) {
                setActiveButton('start');
                console.log('zniffer on and zniffer status on');
            } else if (!is_zniffer_on) {
                setActiveButton('pause');
                console.log('znif on, zniffer status off');
            }
            console.log('Zniffer ON');
        } else if (response == 0) {
            if (is_zniffer_on) {
                setActiveButton('stop');
            } else if (!is_zniffer_on) {
                setActiveButton('stop');
            }
            console.log('Zniffer OFF');

        }
        return response;
    }

    function onloadZnifferStatus(response) {
        if (response == 1) {
            radioButton = "start";
            load();
            is_zniffer_on = true;
            setTimeout(refresh, 1000);
            console.log('znif on');
            setActiveButton('start');
        } else if (response == 0) {
            radioButton = "stop";
            setActiveButton('stop');
            console.log('znif oof');
            load();
            if (is_zniffer_on) {
                is_zniffer_on = false;
            }
        } else {
            console.log("zniffer status error");
        }
        console.log(radioButton);
        return response;
    }

    function setActiveButton(button) {
        switch (button) {
            case 'start':
                $stopBtn.removeClass('active');
                $pauseBtn.removeClass('active');
                $startBtn.addClass('active');
                radioButton = "start";
                break;
            case 'stop':
                $stopBtn.addClass('active');
                $pauseBtn.removeClass('active');
                $startBtn.removeClass('active');
                radioButton = "stop";
                break;
            case 'pause':
                $stopBtn.removeClass('active');
                $pauseBtn.addClass('active');
                $startBtn.removeClass('active');
                radioButton = "pause";
                break;
            default:
        }
    }

    function errorFun(xhr, status, error) {
        var err = eval("(" + xhr.responseText + ")");
        console.log(xhr + " " + status + " " + error);
    }

    function returnHI() {
        console.log(home_id);
    }


    function openFile(arg, atr2) {
        w2ui.grid.lock("Loading. Please wait.", true);

        $.ajax({
            url: "ajax/open_homeid.php",
            type: "POST",
            data: {
                DisplayedRecords: atr2
            },
            success: function(response) {
                home_id = response;
                $.ajax({
                    url: arg,
                    type: 'GET',
                    success: function(responseText) {
                        if (w2ui.grid.records.length > 0)
                            w2ui.grid.clear();

                        var color = "#AD3232";
                        var ZWCommandParsed = "";
                        var ZWparsedRoute = "";
                        var ZWparsedSource = "";
                        var ZWparsedDestination = "";

                        var data = parse.CSVToArray(responseText);

                        for (x = 1; x < data.length - 1; x++) {
                            //color = "#AD3232";
                            color = "#FA8A8A";
                            if (data[x][2] != home_id) {
                                ZWparsedSource = '-';
                                ZWparsedDestination = '-';
                                ZWparsedRoute = '-';
                                ZWCommandParsed = ""; // make it blank
                            } else {
                                color = parse.sqNum(x, data);
                                ZWCommandParsed = parse.command(data[x]);
                                ZWparsedRoute = parse.route(data[x]);
                                ZWparsedSource = parseInt(data[x][3], 16);
                                ZWparsedDestination = parseInt(data[x][5], 16);
                            }
                            w2ui['grid'].records.push({
                                recid: x + 1,
                                id: x + 1,
                                rssi: data[x][1],
                                data: data[x][0],
                                source: ZWparsedSource,
                                route: ZWparsedRoute,
                                destination: ZWparsedDestination,
                                command: ZWCommandParsed,
                                h_id: data[x][2],
                                style: "background-color: " + color
                            });
                        }

                        w2ui.grid.reload();
                        if (w2ui.grid.records.length > 0) {
                            w2ui.grid.unlock();
                        }

                    },

                    error: errorFun
                });
            },
            error: errorFun
        });
    }

    return {
        homeid: returnHI,
        openFile: openFile,
        load: load,
    };

})();
