var xmlParser = (function() {
    var nodeInfo;
    var xml;
    var bas_dev_data = {};
    var gen_dev_data = {};
    var spec_dev_data = [];
    var d = [];
    var genDevKeys = [];

    function getData(data) {
        nodeInfo = parse.CSVToArray(data);
        request('pyzwave/ZWave_custom_cmd_classes.xml', 'text', firstParse, errorFun);
    }

    function firstParse(xml) {
        readBasDev(xml);
        readGenDev(xml);
        readSpecDev(xml);
        bas_dev(bas_dev_data, nodeInfo);
        $("<h3></h3>").html('now gen').appendTo("#testingDiv");
        gen_dev(gen_dev_data, nodeInfo);
        $("<h3></h3>").html('now spec').appendTo("#testingDiv");
        spec_dev(gen_dev_data, spec_dev_data, nodeInfo);

    }

    function readBasDev(xml) {
        $(xml).find('bas_dev').each(function() {
            k = parseInt($(this).attr('key'), 16);
            bas_dev_data[k] = $(this).attr('help');
        });
    }

    function readGenDev(xml) {
        $(xml).find('gen_dev').each(function() {
            k = parseInt($(this).attr('key'), 16);
            gen_dev_data[k] = $(this).attr('help');
        });
    }

    function readSpecDev(xml) {
        $(xml).find('gen_dev').each(function() {
            genDevKeys.push($(this).attr('key'));
            tmp = [];
            $(this).find('spec_dev').each(function() {
                tmp.push($(this).attr('help'));
            })

            k = parseInt($(this).attr('key'), 16);
            spec_dev_data[k] = tmp;
        });
    }

    function bas_dev(basData, keys) {
        for (var i = 0; i < keys.length - 1; i++) {
            _key = parseInt(keys[i][3], 16);
            for (key in basData)
                if (_key == key) {
                    $("<li></li>").html(basData[key] + ", ").appendTo("#testingDiv");
                    break;
                }
        }

    }

    function gen_dev(genData, keys) {
        for (var i = 0; i < keys.length - 1; i++) {
            _key = parseInt(keys[i][4], 16);

            for (key in genData)
                if (_key == key) {
                    $("<li></li>").html(genData[key] + ", ").appendTo("#testingDiv");
                    break;
                }
        }
    }

    function spec_dev(genData, specData, keys) {
        console.log(specData);
        for (var i = 0; i < keys.length - 1; i++) {
            _keyG = parseInt(keys[i][4], 16);
            _keyS = parseInt(keys[i][5], 16);
            for (key in genData)
                if (_keyG == key)
                    for (skey in specData[key])
                        if (_keyS == skey) {
                            $("<li></li>").html(specData[key][skey] + ", ").appendTo("#testingDiv");
                            break;
                        }
        }
    }


    function start() {
        request('data/ima/node_info.csv', 'text', getData, errorFun);
    }


    function request(_url, _dataType, _onSuccess, _onError) {
        $.ajax({
            url: _url,
            dataType: _dataType,
            success: _onSuccess,
            error: _onError
        })
    }

    function errorFun(xhr, status, error) {
        var err = eval("(" + xhr.responseText + ")");
        console.log(xhr + " " + status + " " + error);
    }

    return {
        start: start,
    }
})();
