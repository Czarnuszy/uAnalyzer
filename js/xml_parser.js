var xmlParser = (function() {
    var nodeInfo;
    var routingInfo;
    var xml;
    var bas_dev_data = {};
    var gen_dev_data = {};
    var spec_dev_data = [];
    var d = [];
    var genDevKeys = [];

    var returnData = [];

    function getData(data) {
        nodeInfo = parse.CSVToArray(data);
        request('pyzwave/ZWave_custom_cmd_classes.xml', 'text', firstParse, errorFun);

    }

    function nowRoutingInfo(data) {
      routingInfo = parse.CSVToArray(data);
      tmp = [];

      for (var i = 0; i < routingInfo.length-1; i++) {
          tmp.push(routingInfo[i][0]);
      }

    //  console.log(tmp);
      returnData.push(tmp);
      fillNodeInfoGrid (returnData);
      fillTestDevGrid(returnData);
            //  returnData.push(routingInfo)
    }

    function firstParse(xml) {
        readBasDev(xml);
        readGenDev(xml);
        readSpecDev(xml);
        bas_dev(bas_dev_data, nodeInfo);
        gen_dev(gen_dev_data, nodeInfo);
        spec_dev(gen_dev_data, spec_dev_data, nodeInfo);

        request('data/ima/routing_info.csv', 'text', nowRoutingInfo, errorFun);
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
        var tmp = [];
        for (var i = 0; i < keys.length - 1; i++) {
            _key = parseInt(keys[i][3], 16);
            for (key in basData)
                if (_key == key) {
                    tmp.push(basData[key]);
                    break;
                }
        }
        returnData.push(tmp);
    }

    function gen_dev(genData, keys) {
        tmp = [];
        for (var i = 0; i < keys.length - 1; i++) {
            _key = parseInt(keys[i][4], 16);

            for (key in genData)
                if (_key == key) {
                    tmp.push(genData[key]);
                    break;
                }
        }
        returnData.push(tmp);

    }

    function spec_dev(genData, specData, keys) {
        tmp = [];
        for (var i = 0; i < keys.length - 1; i++) {
            _keyG = parseInt(keys[i][4], 16);
            _keyS = parseInt(keys[i][5], 16);
            for (key in genData)
                if (_keyG == key)
                    for (skey in specData[key])
                        if (_keyS == skey) {
                            tmp.push(specData[key][skey]);
                            break;
                        }
        }
        returnData.push(tmp);

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

    function nodeInfo() {
        start();
    }

    function fillNodeInfoGrid(data) {
      w2ui.NodeInfoGrid.clear();
    //  console.log(data[2]);
    //  console.log(data[0].length);
      for (var i = 0; i < data[0].length; i++) {
      //  console.log(data[3][i]);
        color = '';
        w2ui['NodeInfoGrid'].records.push({
          dev: data[3][i],
          basic: data[0][i],
          generic: data[1][i],
          specific: data[2][i],
          style: "background-color: " + color,

         });
      }

       w2ui['NodeInfoGrid'].refresh();

    }

    function fillTestDevGrid(data) {
      w2ui.testDevGrid.clear();
      for (var i = 0; i < data[0].length; i++) {
        color = '';
        w2ui['testDevGrid'].records.push({
          recid: i,
          dev: data[3][i],
          specific: data[2][i],
          result: 'test',
          style: "background-color: " + color,

         });
      }

       w2ui['testDevGrid'].refresh();
    }

    function renderRoutingTable(data){




    }



    function errorFun(xhr, status, error) {
        var err = eval("(" + xhr.responseText + ")");
        console.log(xhr.responseText);
        console.log(xhr + " " + status + " " + error);
    }

    return {
        start: start,
        fillTestGrid: fillTestDevGrid,
        data: returnData
    }
})();
