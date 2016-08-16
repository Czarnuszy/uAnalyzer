
$("#wifi").hide();
$("#opt").click(function(){
				$("#wifi").toggle();
				});

$("#option2").click(function(){
				$("#wifi").hide();
			});

$("#option3").click(function(){
				$("#wifi").hide();
			});

$("#option4").click(function(){
				$("#wifi").hide();
			});

$("#setTimeOption").click(function(){
				$("#wifi").hide();
				openTimePopup();
			});

$("#pwdOptions").click(function(){
				$("#wifi").hide();
				openPasswordPopup();
			});


function openTimePopup() {
    w2popup.open({
        title   : 'Time Settings',
        width   : 400,
        height  : 400,
        showMax : true,
        body    : '<div id="popuptimemain" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px;"></div>',
        onOpen  : function (event) {
            event.onComplete = function () {
							$( "#popuptimemain" ).load( "ajax/time_options.php" );
					//		$("#timeZoneSelect").val("-3").change();

            }
        },
        onToggle: function (event) {
            event.onComplete = function () {
							w2ui.grid.refresh();

            }
        }

    });

}

function openPasswordPopup() {
    w2popup.open({
        title   : 'Password Settings',
        width   : 400,
        height  : 400,
        showMax : true,
        body    : '<div id="popupPWDmain" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px;"></div>',
        onOpen  : function (event) {
            event.onComplete = function () {
							$( "#popupPWDmain" ).load( "ajax/password_options.php" );
					//		$("#timeZoneSelect").val("-3").change();

            }
        },
        onToggle: function (event) {
            event.onComplete = function () {
							w2ui.grid.refresh();

            }
        }

    });

}
