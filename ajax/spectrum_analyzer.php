<?php

    session_start();

    if (!isset($_SESSION['logged'])) {
        header('Location: login.php');
        exit();
    }

?>
<?php require_once 'inc/init.php'; ?>
<head>
  <script src="../js/spectrumAnalyzer.js"></script>
</head>

<!--
	The ID "widget-grid" will start to initialize all widgets below
	You do not need to use widgets if you dont want to. Simply remove
	the <section></section> and you can use wells or panels instead
	-->


  <section id="widget-grid" class="">

      <div class="row">

          <article class="col-sm-12">

              <div class="jarviswidget" id="wid-id-1" data-widget-deletebutton="false"
              data-widget-refresh="true" data-widget-editbutton="false" data-widget-collapsed="false"
               data-widget-togglebutton="false" data-widget-colorbutton="false" data-widget-sortable="false">


                  <header id='spectrumHeader'>

                      <h2> Analyzer </h2>





                      <div class="widget-toolbar" id="clearBTN">
                          <label class="btn btn-default btn-xs " id="trash-a1"> Clear
  											<i class="fa fa-trash-o"></i>
  										</label>
                      </div>
                      <div class="widget-toolbar">
                          <div class="btn-group" data-toggle="buttons">
                              <label class="btn btn-default btn-xs " id="play-a3">
  							          <input type="radio" name="specBtn" id="style-a1" value="start"> <i class="fa fa-play"></i> Capture
  							        </label>


                              <label class="btn btn-default btn-xs " id="stop-a3">
  							          <input type="radio" name="specBtn" id="style-a3" value="stop" checked= true> <i class="fa fa-stop"></i> Stop
  							        </label>


                          </div>
                      </div>

                      <div class="widget-toolbar" id='time-toolbar'>
                          <label id="startTime">Started: hh:mm:ss</label> ||
                          <label id="refreshTime"> Last: hh:mm:ss</label>

                      </div>



                  </header>



                  <div class="widget-body">

                      <div id="spectrum-body">
                          loading...
                      </div>

                  </div>

              </div>
          </article>

      </div>

  </section>

<script type="text/javascript">




// ../js/spectrumAnalyzer.js







///////////

var run_after_loaded = function () {
//  spectrumAnalyzer.init();
  // spectrumAnalyzer.loadGrid()
//   spectrumAnalyzer.load();
  // //$('#spectrum-body').load("ajax/script_spectrum.php");
}


var pagefunction = function() {

  //loadScript("js/spectrumAnalyzer.js", run_after_loaded);



}


	pageSetUp();


	pagefunction();



</script>
