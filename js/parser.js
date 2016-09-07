
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
