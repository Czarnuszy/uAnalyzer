//Modular version of packet Analyzer.


var packetAnalyzer = (function() {

    var home_id = '';
    var is_zniffer_on = false;
    var radioButton = '';

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

        checkStatusINt = setInterval(function() {
            znifferStatus(returnZnifferStatus);
        }, 1000);
    }

    _init();
    // function _init() {
    //     this.cacheDom();
    //     this.bindEvents();
    // }

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
                $("#body-w").load("ajax/savetrace.php?filename=" + Value);
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
        if (this.checked) {
            w2ui['grid'].search('h_id', home_id);
            console.log('Newtwork switch');

        } else
            w2ui['grid'].search('h_id', '');

    }


    /////End buttons binds /////////

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

                            color = "#AD3232";
                            if (data[x][2] != home_id) {
                                ZWparsedSource = '-';
                                ZWparsedDestination = '-';
                                ZWparsedRoute = '-';
                            } else {
                                color = parse_sqnum(x, data);
                                ZWCommandParsed = parseCommand(data[x]);
                                ZWparsedRoute = parseRoute(data[x]);
                                ZWparsedSource = parseInt(data[x][3], 10);
                                ZWparsedDestination = parseInt(data[x][5], 10);
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
                //home_id = data[x][2];
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
                            color = "#AD3232";

                            if (data[x][2] != home_id) {
                                ZWparsedSource = '-';
                                ZWparsedDestination = '-';
                                ZWparsedRoute = '-';
                            } else {
                                color = parse.sqNum(x, data);
                                //color = parse_sqnum(x, data);
                                ZWCommandParsed = parse.command(data[x]);
                                ZWparsedRoute = parse.route(data[x]);
                                ZWparsedSource = parseInt(data[x][3], 10);
                                ZWparsedDestination = parseInt(data[x][5], 10);
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


var parse = (function() {
    function sqnum(x, data) {
        var color = "";
        if (data[x][8] == "01")
            color = "#f0f0f0";
        else if (data[x][8] === "02")
            color = "#E8E8E8";
        else if (data[x][8] == "03")
            color = "#E0E0E0";
        else if (data[x][8] == "04")
            color = "#D8D8D8";
        else if (data[x][8] == "05")
            color = "#D0D0D0";
        else if (data[x][8] == "06")
            color = "#C8C8C8";
        else if (data[x][8] == "07")
            color = "#C0C0C0";
        else if (data[x][8] == "08")
            color = "#B8B8B8";
        else if (data[x][8] == "09")
            color = "#B0B0B0";
        else if (data[x][8] == "10")
            color = "#A8A8A8";
        else if (data[x][8] == "11")
            color = "#A0A0A0";
        else if (data[x][8] == "12")
            color = "#989898";
        else if (data[x][8] == "13")
            color = "#909090";
        else if (data[x][8] == "14")
            color = "#888888";
        else if (data[x][8] == "15")
            color = "#808080";

        return color;
    }

    function parseRoute(all_data) {
        var hop = all_data[9];
        var count = all_data[10];
        var header = all_data[11];
        var route = "";
        var source = parseInt(all_data[3], 10);
        var destination = parseInt(all_data[5], 10);;


        var hop1 = all_data[4];
        hop1 = hop1.slice(0, 2);
        hop1 = parseInt(hop1, 16);
        var hop2 = all_data[4];
        hop2 = hop2.slice(3, 5);
        hop2 = parseInt(hop2, 16);

        if (hop.includes("-1")) route = source + ">" + destination;
        else if (hop.includes("01")) {
            if ((count.includes("000")) && (header.includes("000"))) {
                route = source + ">" + all_data[4] + "-" + destination;
            } else if ((count.includes("001")) && (header.includes("000"))) {
                route = source + "-" + all_data[4] + ">" + destination;
            } else if ((count.includes("001")) && (header.includes("003"))) {
                route = source + ">" + all_data[4] + "-" + destination;

            } else if ((count.includes("015")) && (header.includes("003"))) {
                route = source + "-" + all_data[4] + ">" + destination;
            } else if ((count.includes("015")) && (header.includes("021"))) {
                route = source + "X" + all_data[4] + ">" + destination;
            }
        } else if (hop.includes("02")) {
            if ((count.includes("000")) && (header.includes("000"))) {
                route = source + ">" + hop1 + "-" + hop2 + "-" + destination;
            }

            if ((count.includes("001")) && (header.includes("000"))) {
                route = source + "-" + hop1 + ">" + hop2 + "-" + destination;
            } else if ((count.includes("002")) && (header.includes("000"))) {
                route = source + "-" + hop1 + "-" + hop2 + ">" + destination;
            } else if ((count.includes("001")) && (header.includes("003"))) {
                route = source + ">" + hop1 + "-" + hop2 + "-" + destination;
            } else if ((count.includes("000")) && (header.includes("003"))) {
                route = source + "-" + hop1 + ">" + hop2 + "-" + destination;
            } else if ((count.includes("015")) && (header.includes("003"))) {
                route = source + "-" + hop1 + "-" + hop2 + ">" + destination;
            } else if ((count.includes("015")) && (header.includes("021"))) {
                route = source + "-" + hop1 + "-" + hop2 + "X" + destination;
            } else if ((count.includes("000")) && (header.includes("037"))) {
                route = source + "X" + hop2 + ">" + hop1 + "-" + destination;
            } else if ((count.includes("015")) && (header.includes("037"))) {
                route = source + "X" + hop1 + "-" + hop2 + ">" + destination;
            }

        }

        //route = "cool";
        return (route);

    }


    function parseCommand(all_data) {
        var RawCommand = all_data[7];
        var ZWCommandClass = RawCommand.slice(3, 5);
        var ZWCommand = RawCommand.slice(6, 8);
        var ZWCommandPayload1 = RawCommand.slice(9, 11)
        var ZWCommandDescription = "";
        var ZWpayload = all_data[7];
        var ZWpackettype = all_data[6];
        var ssource = all_data[3];
        var ZWack = all_data[13];


        ZWCommandDescription = "Unrecognized Command: " + RawCommand;

        // other network
        if (ZWack.includes("Ack") || ZWpackettype.includes("Ack")) {
            //if(ZWack.includes("")) ZWCommandDescription = "Ack";
            //else ZWCommandDescription = ZWack;
            ZWCommandDescription = ZWack;
        } else if ((ZWCommandClass.includes("20")) || (ZWCommandClass.includes("25")) || (ZWCommandClass.includes("27"))) {
            ZWCommandDescription = "Basic Command Class(" + ZWCommandClass + "," + ZWCommand + "," + ZWCommandPayload1 + ")";
            //it is Basic CC
            if (ZWCommand.includes("01")) {
                if (ZWCommandPayload1 == "00")
                    ZWCommandDescription = "Turn OFF";
                else if (ZWCommandPayload1 == "FF")
                    ZWCommandDescription = "Turn ON";
                else if (ZWCommandPayload1 < 64) {
                    ZWCommandDescription = "Dimm to:" + ZWCommandPayload1;
                }
            } else if (ZWCommand.includes("02")) {
                ZWCommandDescription = "is Device On? Off?";
            } else if (ZWCommand.includes("03")) {
                ZWCommandDescription = "Basic Report";
                if (ZWCommandPayload1 == "00")
                    ZWCommandDescription = "Device is OFF";
                else if (ZWCommandPayload1 == "FF")
                    ZWCommandDescription = "Device is ON";
                else if (ZWCommandPayload1 < 64) {
                    ZWCommandDescription = "Dimm level = " + ZWCommandPayload1;
                }
            }

        } else if (ZWCommandClass.includes("98")) {
            // Security CC
            ZWCommandDescription = "Encrypted Command";
        } else if (ZWCommandClass.includes("71")) {
            // Security CC
            ZWCommandDescription = "Notification/Alarm  Command";
        } else if (ZWCommandClass.includes("22")) {
            // Security CC
            ZWCommandDescription = "Application Status";
        } else if (ZWCommandClass.includes("9B")) {
            // Security CC
            ZWCommandDescription = "Configuration Association Command";
        } else if (ZWCommandClass.includes("85")) {
            // Security CC
            ZWCommandDescription = "Association Command";
        } else if (ZWCommandClass.includes("95") || ZWCommandClass.includes("96") || ZWCommandClass.includes("97")) {
            // Security CC
            ZWCommandDescription = "A/V Command";
        } else if (ZWCommandClass.includes("36")) {
            // Security CC
            ZWCommandDescription = "Basic Tariff info Command";
        } else if (ZWCommandClass.includes("50")) {
            // Security CC
            ZWCommandDescription = "Window Covering Comand";
        } else if (ZWCommandClass.includes("80")) {
            // Security CC
            ZWCommandDescription = "Battery Command";
        } else if (ZWCommandClass.includes("2A")) {
            // Security CC
            ZWCommandDescription = "Chimney Fan Command";
        } else if (ZWCommandClass.includes("46")) {
            // Security CC
            ZWCommandDescription = "Climate Control Schedule Command";
        } else if (ZWCommandClass.includes("81")) {
            // Security CC
            ZWCommandDescription = "Clock Command";
        } else if (ZWCommandClass.includes("70")) {
            // Security CC
            ZWCommandDescription = "Configuration Command";
        } else if (ZWCommandClass.includes("21")) {
            // Security CC
            ZWCommandDescription = "Controller Replication Command";
        } else if (ZWCommandClass.includes("56")) {
            // Security CC
            ZWCommandDescription = "CRC16 Command";
        } else if (ZWCommandClass.includes("3A") || ZWCommandClass.includes("3B")) {
            // Security CC
            ZWCommandDescription = "DCP command";
        } else if (ZWCommandClass.includes("4C")) {
            // Security CC
            ZWCommandDescription = "Door Lock Logging Command";
        } else if (ZWCommandClass.includes("32")) {
            // Security CC
            ZWCommandDescription = "Meter Command";
        } else if (ZWCommandClass.includes("60")) {
            // Security CC
            ZWCommandDescription = "Multichanel Command";
        } else if (ZWCommandClass.includes("2B")) {
            // Security CC
            ZWCommandDescription = "Scene Activation Command";
        }

        //ZWCommandDescription= ZWCommandDescription + " P:"+RawCommand;

        // end of payload
        return (ZWCommandDescription);

    }

    function CSVToArray(strData, strDelimiter) {

        strDelimiter = (strDelimiter || ",");

        var objPattern = new RegExp(
            (
                "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

                "([^\"\\" + strDelimiter + "\\r\\n]*))"
            ),
            "gi"
        );


        var arrData = [
            []
        ];

        var arrMatches = null;

        while (arrMatches = objPattern.exec(strData)) {

            var strMatchedDelimiter = arrMatches[1];

            if (
                strMatchedDelimiter.length &&
                strMatchedDelimiter !== strDelimiter
            ) {

                arrData.push([]);

            }

            var strMatchedValue;

            if (arrMatches[2]) {

                strMatchedValue = arrMatches[2].replace(
                    new RegExp("\"\"", "g"),
                    "\""
                );

            } else {

                strMatchedValue = arrMatches[3];
            }

            arrData[arrData.length - 1].push(strMatchedValue);
        }

        return (arrData);
    }

    return {
        sqNum: sqnum,
        route: parseRoute,
        command: parseCommand,
        CSVToArray: CSVToArray,
    }
})();
