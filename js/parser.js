var parse = (function() {

    function sqnum(x, data) {
        var color = "";
        if (data[x][8] == "01")
            color = "#f0f0f0";
        else if (data[x][8] === "15")
            color = "#E8E8E8";
        else if (data[x][8] == "02")
            color = "#E0E0E0";
        else if (data[x][8] == "14")
            color = "#D8D8D8";
        else if (data[x][8] == "03")
            color = "#D0D0D0";
        else if (data[x][8] == "13")
            color = "#C8C8C8";
        else if (data[x][8] == "04")
            color = "#C0C0C0";
        else if (data[x][8] == "12")
            color = "#B8B8B8";
        else if (data[x][8] == "05")
            color = "#B0B0B0";
        else if (data[x][8] == "11")
            color = "#A8A8A8";
        else if (data[x][8] == "06")
            color = "#A0A0A0";
        else if (data[x][8] == "10")
            color = "#989898";
        else if (data[x][8] == "07")
            color = "#909090";
        else if (data[x][8] == "09")
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
        var frame_type = all_data[6];
        var source = parseInt(all_data[3], 16);
        var destination = parseInt(all_data[5], 16);;

        //000-000-000-000
        var hop1 = all_data[4];
        hop1 = hop1.slice(0, 2);
        hop1 = parseInt(hop1, 16);
        var hop2 = all_data[4];
        hop2 = hop2.slice(3, 5);
        hop2 = parseInt(hop2, 16);
        var hop3 = all_data[4];
        hop3 = hop3.slice(6, 8);
        hop3 = parseInt(hop3, 16);
        var hop4 = all_data[4];
        hop4 = hop4.slice(9, 11);
        hop4 = parseInt(hop4, 16);

        if (frame_type.includes("Explorer")) {
            var ehop1 = all_data[7];
            ehop1 = ehop1.slice(15, 17);
            ehop1 = parseInt(ehop1, 16);
            var ehop2 = all_data[7];
            ehop2 = ehop2.slice(18, 20);
            ehop2 = parseInt(ehop2, 16);
            var ehop3 = all_data[7];
            ehop3 = ehop3.slice(21, 23);
            ehop3 = parseInt(ehop3, 16);
            var ehop4 = all_data[7];
            ehop4 = ehop4.slice(24, 26);
            ehop4 = parseInt(ehop4, 16);

            // explorer frame
            route = "[" + source + "]" + "*";
            if (ehop1 != 0) route += ehop1 + "*";
            if (ehop2 != 0) route += ehop2 + "*";
            if (ehop3 != 0) route += ehop3 + "*";
            if (ehop4 != 0) route += ehop4 + "*";
            //+all_data[7].slice(15,17)+"*"+all_data[7].slice(18,20)+"*"+all_data[7].slice(21,23)+"*"+all_data[7].slice(24,26);
            route += "[" + destination + "]";
            //if (all_data[4].slice(15,17))
            // + ">" + destination;

        } else if (hop.includes("-1")) {
            //route = source + ">" + destination;
            if (all_data[6].includes("Ack")) route = "Ack";
            else route = "(" + source + ")" + ">" + "(" + destination + ")";
            //route = "Ack";
        } else {
            route = "(" + source + ")";

            if (header & 0x01) {
                // reverse direction
                route = "(" + destination + ")";
                if (hop.includes("01")) {
                    if (count == "0F") {
                        route += "<";
                    } else route += "-";
                    route += hop1;
                    if (count == 0) {
                        route += "<";
                    } else route += "-";
                    //        route +=  source;
                } else if (hop.includes("02")) {
                    if (count == "0F") {
                        route += "<";

                    } else route += "-";
                    route += hop1;
                    if (count == 1) {
                        route += "<";
                    } else route += "-";
                    route += hop2;
                    if (count == 2) {
                        route += "<";

                    } else route += "-";
                    //        route +=  source;
                } else if (hop.includes("03")) {
                    if (count == "0F") {
                        route += "<";
                    } else route += "-";
                    route += hop1;
                    if (count == 0) {
                        route += "<";

                    } else route += "-";
                    route += hop2;
                    if (count == 1) {
                        route += "<";

                    } else route += "-";
                    route += hop3;
                    if (count == 2) {
                        route += "<";

                    } else route += "-";
                    //          route +=  source;

                } else if (hop.includes("04")) {
                    if (count == "0F") {
                        route += "<";
                    } else route += "-";
                    route += hop1;
                    if (count == 0) {
                        route += "<";

                    } else route += "-";
                    route += hop2;
                    if (count == 1) {
                        route += "<";

                    } else route += "-";
                    route += hop3;
                    if (count == 2) {
                        route += "<";

                    } else route += "-";
                    route += hop4;
                    if (count == 3) {
                        route += "<";

                    } else route += "-";

                    //            route +=  source;

                }
                route += "(" + source + ")";

            } else {
                //Normal
                route = "(" + source + ")";
                if (hop.includes("01")) {
                    if (count == 0) {
                        route += ">";
                    } else route += "-";
                    route += hop1;
                    if (count == 1) {
                        route += ">";
                    } else route += "-";
                } else if (hop.includes("02")) {
                    if (count == 0) {
                        route += ">";

                    } else route += "-";
                    route += hop1;
                    if (count == 1) {
                        route += ">";
                    } else route += "-";
                    route += hop2;
                    if (count == 2) {
                        route += ">";

                    } else route += "-";
                } else if (hop.includes("03")) {
                    if (count == 0) {
                        route += ">";
                    } else route += "-";
                    route += hop1;
                    if (count == 1) {
                        route += ">";

                    } else route += "-";
                    route += hop2;
                    if (count == 2) {
                        route += ">";

                    } else route += "-";
                    route += hop3;
                    if (count == 3) {
                        route += ">";

                    } else route += "-";


                } else if (hop.includes("04")) {
                    if (count == 0) {
                        route += ">";
                    } else route += "-";
                    route += hop1;
                    if (count == 1) {
                        route += ">";

                    } else route += "-";
                    route += hop2;
                    if (count == 2) {
                        route += ">";

                    } else route += "-";
                    route += hop3;
                    if (count == 3) {
                        route += ">";

                    } else route += "-";
                    route += hop4;
                    if (count == 4) {
                        route += ">";

                    } else route += "-";


                }
                route += "(" + destination + ")";

            }
            /*   else if (hop.includes("04"))
                    {
                       if (count == 0)
                           {
                               route+= ">";
                           }
                       else route+= "-";
                       route += hop1;
                       if (count == 1)
                           {
                               route+= ">";
                           }
                       else route+= "-";
                       route +=  hop2;
                       if (count == 2)
                           {
                               route+= ">";
                           }
                       else route+= "-";
                       route +=  hop3;
                       if (count == 3)
                           {
                               route+= ">";
                           }
                       else route+= "-";
                       route +=  hop4;
                       if (count == 4)
                           {
                               route+= ">";
                           }
                       else route+= "-";
                    */
            //route +=  destination;
        }



        /*else if (hop.includes("01"))
 {
    route =source + "-"+ hop1+ "-" + destination;
 }
else if (hop.includes("02"))
 {
    route =source + "-"+ hop1+ "-"+hop2+"-" + destination;
 }
 else if (hop.includes("03"))
 {
    route =source + "-"+ hop1+ "-"+hop2+"-" +hop3+"-"+ destination;
 }
 else if (hop.includes("04"))
 {
    route =source + "-"+ hop1+ "-"+hop2+"-" +hop3+"-"+ hop4+"-"+destination;
 }


/*
 else if (hop.includes("01"))
 {
      if ((count.includes("000")) && (header.includes("000")))
      {
        route =source + ">"+hop1+"-" + destination;
      }
      if ((count.includes("000")) && (header.includes("003")))
      {
        route =destination + "-"+ hop1+ "<" + source;
      }
      else if ((count.includes("001")) && (header.includes("000")))
      {
        route =source + "-"+ hop1+">" + destination;
      }
      else if ((count.includes("001") )&& (header.includes("003")))
      {
        route =destination + "<"+hop1+"-" + source;

      }
      else if ((count.includes("015")) && (header.includes("003")))
      {
        route =destination + "<"+hop1+"-" + source;
      }
      else if ((count.includes("015")) && (header.includes("021")))
      {
        route =source + "X"+hop1+">" + destination;
      }

 }
 else if (hop.includes("02"))
 {
    if ((count.includes("000")) && (header.includes("000")))
    {
	route =source + ">"+ hop1+ "-"+hop2+"-" + destination;
    }

    if ((count.includes("001")) && (header.includes("000")))
    {
	route =source + "-"+ hop1+ ">"+hop2+"-" + destination;
    }

    else if ((count.includes("002")) && (header.includes("000")))
    {
	route =source + "-"+ hop1+ "-"+hop2+">" + destination;
    }
    else if ((count.includes("001")) && (header.includes("003")))
    {
	route =source + ">"+ hop1+ "-"+hop2+"-" + destination;
    }
    else if ((count.includes("000")) && (header.includes("003")))
    {
	route =source + "-"+ hop1+ ">"+hop2+"-" + destination;
    }
    else if ((count.includes("015")) && (header.includes("003")))
    {
	route =source + "-"+ hop1+ "-"+hop2+">" + destination;
    }
    else if ((count.includes("015")) && (header.includes("021")))
    {
	route =source + "-"+ hop1+ "-"+hop2+"X" + destination;
    }
    else if ((count.includes("000")) && (header.includes("037")))
    {
	route =source + "X"+ hop2+ ">"+hop1+"-" + destination;
    }
    else if ((count.includes("015")) && (header.includes("037")))
    {
	route =source + "X"+ hop1+ "-"+hop2+">" + destination;
    }

 }
else if (hop.includes("03"))
 {
    if ((count.includes("000")) && (header.includes("000")))
    {
    route =source + ">"+ hop1+ "-"+hop2+"-" + hop3+"-" +destination;
    }

    if ((count.includes("001")) && (header.includes("000")))
    {
    route =source + "-"+ hop1+ ">"+hop2+"-" + hop3+"-" + destination;
    }

    else if ((count.includes("002")) && (header.includes("000")))
    {
    route =source + "-"+ hop1+ "-"+hop2+">" + hop3+"-" + destination;
    }
    else if ((count.includes("003")) && (header.includes("000")))
    {
    route =source + "-"+ hop1+ "-"+hop2+"-" + hop3+">" + destination;
    }
    else if ((count.includes("001")) && (header.includes("003")))
    {
    route =source + ">"+ hop1+ "-"+hop2+"-" + hop3+"-" + destination;
    }
    else if ((count.includes("000")) && (header.includes("003")))
    {
    route =source + "-"+ hop1+ ">"+hop2+"-" + hop3+"-" + destination;
    }
    else if ((count.includes("015")) && (header.includes("003")))
    {
    route =source + "-"+ hop1+ "-"+hop2+">" + hop3+"-" + destination;
    }
    else if ((count.includes("015")) && (header.includes("021")))
    {
    route =source + "-"+ hop1+ "-"+hop2+"X" + hop3+"-" + destination;
    }
    else if ((count.includes("000")) && (header.includes("037")))
    {
    route =source + "X"+ hop2+ ">"+hop1+"-" + hop3+"-" + destination;
    }
    else if ((count.includes("015")) && (header.includes("037")))
    {
    route =source + "X"+ hop1+ "-"+hop2+">" + hop3+"-" + destination;
    }

 }
 */

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
        //var ZWack = all_data[13];
        var ZWack = "";
        var count = all_data[10];
        var header = all_data[11];
        var hop = all_data[9];
        var destination = parseInt(all_data[5], 16);

        //if (ZWpayload.length>6)
        {

            if (ZWpackettype.includes("Explorer")) {
                ZWCommandClass = RawCommand.slice(27, 29); //3-5
                ZWCommand = RawCommand.slice(30, 32); //6-8
                ZWCommandPayload1 = RawCommand.slice(33, 35) //9-11
                ZWCommandDescription = "Explorer: " + ZWCommandClass + ":" + ZWCommand + ":" + ZWCommandPayload1 + ":";

            } else ZWCommandDescription = "Unrecognized Command: " + RawCommand;
            //ZWCommandDescription = "Command:" +ZWCommandClass;

            // other network
            if ((header & 2) && (header != "-1")) {
                // routed ack
                ZWCommandDescription = "";
            } else if ((header & 4) && (header != "-1")) {
                // routed error
                ZWCommandDescription = "";
            } else if (ZWack.includes("Ack") || ZWpackettype.includes("Ack")) {
                //if(ZWack.includes("")) ZWCommandDescription = "Ack";
                //else ZWCommandDescription = ZWack;
                ZWCommandDescription = "";
            } else if ((count.includes("000")) && (header.includes("003"))) {
                ZWCommandDescription = "Ack";

            } else if ((ZWCommandClass.includes("20")) || (ZWCommandClass.includes("25")) || (ZWCommandClass.includes("26"))) {
                ZWCommandDescription = "Basic Command Class(" + ZWCommandClass + "," + ZWCommand + "," + ZWCommandPayload1 + ")";
                //it is Basic CC
                if (ZWCommand.includes("01")) {
                    if (ZWCommandPayload1 == "00")
                        ZWCommandDescription = "Turn OFF" + " Device";
                    else if (ZWCommandPayload1 == "FF")
                        ZWCommandDescription = "Turn ON" + " Device";
                    else if (ZWCommandPayload1 < 64) {
                        ZWCommandDescription = "Dimm to: " + parseInt(ZWCommandPayload1, 16) + "%";
                    }
                } else if (ZWCommand.includes("02")) {
                    ZWCommandDescription = "is Device On/Off?";
                } else if (ZWCommand.includes("03")) {
                    ZWCommandDescription = "Basic Report";
                    if (ZWCommandPayload1 == "00")
                        ZWCommandDescription = "Device is OFF";
                    else if (ZWCommandPayload1 == "FF")
                        ZWCommandDescription = "Device is ON";
                    else if (ZWCommandPayload1 < 64) {
                        ZWCommandDescription = "Device Dimm level = " + parseInt(ZWCommandPayload1, 16) + "%";
                    }
                }


            } else if (ZWCommandClass.includes("01") || ZWCommandClass.includes("10")) {
                // Security CC
                ZWCommandDescription = "Z-Wave Protocol Command" + ZWCommand;
                if (ZWCommand.includes("04")) {
                    ZWCommandDescription = "Find Nodes in range";
                } else if (ZWCommand.includes("05")) {
                    ZWCommandDescription = "Get Nodes in Range";
                } else if (ZWCommand.includes("07")) {
                    ZWCommandDescription = "Find Nodes Completed";
                } else if (ZWCommand.includes("06")) {
                    ZWCommandDescription = "Node Range info";
                } else if (ZWCommand.includes("18")) {
                    ZWCommandDescription = "NOP Power";
                } else if (ZWCommand.includes("0C")) {
                    ZWCommandDescription = "Assign Return Route";
                } else if (ZWCommand.includes("40")) {
                    ZWCommandDescription = "NOP";
                }
            } else if (ZWCommandClass.includes("98")) {
                // Security CC
                ZWCommandDescription = "Encrypted Command";
            } else if (ZWCommandClass.includes("30")) {
                // Security CC
                ZWCommandDescription = "Binary Command";
                if (ZWCommand.includes("03")) {
                    ZWCommandDescription = "Binary Report";
                    if (ZWCommandPayload1 == "00")
                        ZWCommandDescription = "Sensor is OFF";
                    else if (ZWCommandPayload1 == "FF")
                        ZWCommandDescription = "Sensor is ON";

                }
            } else if (ZWCommandClass.includes("31")) {

                ZWCommandDescription = "Multilevel Sensor Command";
                if (ZWCommand.includes("05")) {
                    ZWCommandDescription = "Multilevel Sensor Report Command";
                } else if (ZWCommand.includes("03")) {
                    ZWCommandDescription = "Multilevel Sensor Get Command";
                }
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
            } else if (ZWCommandClass.includes("00")) {
                // Security CC
                ZWCommandDescription = "Test Command (NOP)";
            } else if (ZWCommandClass.includes("84")) {
                // Security CC
                ZWCommandDescription = "WakeUp Command";
                if (ZWCommand.includes("07")) {
                    ZWCommandDescription = "WakeUp Notification";
                }
            }
        }

        //ZWCommandDescription= ZWCommandDescription + " C:"+count +" H:"+header+ " N:"+hop;
        //  ZWCommandDescription= ZWCommandDescription +ZWpayload;
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
