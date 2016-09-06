var spectrumAnalyzer = (function() {

    var sradioButton;
    var isSpectrumOn = false;

    //cache DOM
    var $el = $('#spectrumHeader');
    var $startBtn = $el.find('#play-a3');
    var $stopBtn = $el.find('#stop-a3');
    var $clearBtn = $el.find('#trash-a1')
    var $timeEl = $("#time-toolbar");
    var $startTime = $timeEl.find('#startTime');
    var $refreshTime = $timeEl.find('#refreshTime');
    var $spectrumBody = $("#spectrum-body");


    //binds events
    $startBtn.on('click', onStartClick);
    $stopBtn.on('click', onStopClick);
    $clearBtn.on('click', onClearClick);

    function _init() {
        read_time("read");
        read_time("readStartTime");

        loadGrid();
        spectrum_status(startusReq);

        setInterval(function() {
            spectrum_status(refreshStatus);
        }, 5000);

    }

    _init();



    //Buttons functions
    function onStartClick() {
        if (sradioButton == "stop") {
            save_start_time();
            var time = save_start_time();
            $startTime.html("Started: " + time);

            onClearClick();
            progrssInt = setInterval(function() {
                progressbar(pd);
            }, 800);
            start_spectrum();
            console.log("ds");
            myInterval = setInterval(load, 1000);

            $.smallBox({
                title: "Z-Wave Spectrum Analyzer",
                content: "<i class='fa fa-clock-o'></i> <i>Start</i>",
                color: "#659265",
                iconSmall: "fa fa-times fa-2x fadeInRight animated",
                timeout: 3000
            });
        } else {

        }
    }

    function onStopClick() {
        if (sradioButton == "start") {
            stop_spectrum();
            clearInterval(myInterval);
            myInterval = false;
            //	 clearTimeout(myset);
            $.smallBox({
                title: "Z-Wave Spectrum Analyzer",
                content: "<i class='fa fa-clock-o'></i> <i>Stop</i> REMEMBER ABOUT RESET",
                color: "#C46A69",
                iconSmall: "fa fa-times fa-2x fadeInRight animated",
                timeout: 3000
            });
        } else {
            stop_spectrum();
            //if(myInterval)
            clearInterval(myInterval);

        }
    }

    function onClearClick() {
        save_start_time();
        var time = save_start_time();
        $startTime.html("Started: " + time);

        $.ajax({
            type: 'POST',
            url: 'ajax/spectrum_data.php',
            dataType: "json",
            data: {
                clear: 1
            },
            success: function(response) {
                console.log(response);
                //	load();
                for (var i = 0; i < 98; i++) {
                    window.myLine.data.datasets[0].data[i] = response[0][i][1];
                    window.myLine.data.datasets[1].data[i] = response[1][i][1];
                }
                window.myLine.update();


            },
            error: errorFun
        });
    }

    ////end buttons functions

    function start_spectrum() {
        $.get("ajax/start_spectrum.php");
        return false;
    }

    function stop_spectrum() {
        $.get("ajax/stop_spectrum.php");
        return false;
    }

    ///////////////////////

    function load() {
        $.ajax({
            url: "ajax/spectrum_data.php",
            type: 'POST',
            data: {
                fileName: "d"
            },
            timeout: 5000,
            dataType: "json",
            //async: false,
            success: function(data) {
                if (data[0].length == 98 && data[1].length == 98) {
                    for (var i = 0; i < 98; i++) {
                        window.myLine.data.datasets[0].data[i] = data[0][i][1];
                        window.myLine.data.datasets[1].data[i] = data[1][i][1];
                    }
                    window.myLine.update();
                }

                read_time("read");
                read_time("readStartTime");

            },
            error: errorFun

        });

    }

    function loadGrid() {
        $spectrumBody = $("#spectrum-body");

        $spectrumBody.load("ajax/script_spectrum.php");
        read_time("read");

    }

    ///spectrum status functions
    function spectrum_status(spectrum_status) {
        $.ajax({
            url: 'ajax/spectrum_status.php',
            success: spectrum_status,
            error: errorFun
        })
    }

    function startusReq(response) {

        if (response == 1) {
            status = true;
            $("#play-a3").attr('class', 'btn btn-default btn-xs active');
            sradioButton = "start";
            //    load();
            myInterval = setInterval(load, 1000);
        } else if (response == 0) {
            status = false;
            $("#stop-a3").attr('class', 'btn btn-default btn-xs active');
            sradioButton = "stop";
            //    load();
        }
        console.log("spectrum status checkin");
        console.log(sradioButton);

    }


    function refreshStatus(response) {
        if (response == 1) {
            $startBtn.addClass('active');
            $stopBtn.removeClass('active');
            sradioButton = "start";
        } else if (response == 0) {
            sradioButton = "stop";
            $stopBtn.addClass('active');
            $startBtn.removeClass('active');
        }
    }


    ////////time functions

    function save_start_time() {
        var time = new Date($.now());
        var time2 = String(time).slice(4, 24);
        $.ajax({
            type: 'POST',
            url: 'ajax/sw_spectrum_time.php',
            data: {
                sw: "save",
                timedata: time2
            },
            success: function(rep) {
                console.log(rep);
            },

        });
        return time2;
    }

    function read_time(req) {
        var $timeEl = $("#time-toolbar");
        var $startTime = $timeEl.find('#startTime');
        var $refreshTime = $timeEl.find('#refreshTime');
        $.ajax({
            type: 'POST',
            url: 'ajax/sw_spectrum_time.php',
            data: {
                sw: req
            },
            success: function(time) {
                if (req == "read")
                    $refreshTime.html("Last: " + time);
                else if (req == "readStartTime")
                    $startTime.html("Started: " + time);
            },
            error: errorFun
        });
    }

    ////////////

    function myTimeoutFunction() {
        load();
        myset = setTimeout(myTimeoutFunction, 2000);
    }

    var pd = 0;

    function progressbar(x) {
        var progress = 0;
        progress += pd;
        var pr = progress + "%";
        var html = "Please Wait	<div class=" + "'progress progress-micro'" + ">	<div class=" +
            "'progress-bar progress-bar-primary'" + " role='progressbar'" + "style='width: " + pr + ";'" + ">" +
            "</div></div>"

        $spectrumBody.html(html);
        pd += 10;
        if (pd >= 100) {
            clearInterval(progrssInt);
            pd = 0;
            $spectrumBody.load("ajax/script_spectrum.php");
        }
    }


    function errorFun(xhr, status, error) {
        var err = eval("(" + xhr.responseText + ")");
        console.log(xhr + " " + status + " " + error);
    }

    return {
        load: load,
        loadGrid: loadGrid,
        init: _init,
    }

})();
