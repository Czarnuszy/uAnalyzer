
<section id="widget-grid" class="">
	<div class="row">
		<article class="col-sm-12">
			<!-- a blank row to get started -->
			<!-- your contents here -->
			<div class="jarviswidget" id="wid-id-6"
					data-widget-editbutton="false"
					data-widget-custombutton="false"
					data-widget-colorbutton="false"
					data-widget-togglebutton="false"
					data-widget-deletebutton="false"
					data-widget-fullscreenbutton="false"
			  >

				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Contacts form </h2>

				</header>

				<!-- widget div-->
				<div>
					<div class="jarviswidget-editbox">
						<!-- This area used as dropdown edit box -->

					</div>

					<!-- widget content -->
					<div class="widget-body no-padding">

						<form  id="contact-form" class="smart-form">

							<header>
									<h1>
									<font face="Futura PT 300" color="#3c608b">	<b>CONTACT US</b></font>  </br>
										</h1>
										<span>
											<font face="Futura PT 300">Please fill out the form below and we will respond to your inquiry.
										</span>

							</header>


							<fieldset>
								<div class="row">
									<section class="col col-6">
										<label class="label">Name</label>
										<label class="input"> <i class="icon-prepend fa fa-user"></i>
											<input type="text" name="fname" id="fname" placeholder="First name" required>
										</label>
									</section>
									<section class="col col-6">
										<label class="label">Last Name</label>
										<label class="input"> <i class="icon-prepend fa fa-user"></i>
											<input type="text" name="lname" id="lname" placeholder="Last name" required>
										</label>
									</section>
								</div>
								</fieldset>

								<fieldset>
								<div class="row">
									<section class="col col-4">
										<label class="label">Cell Phone Number</label>
										<label class="input"> <i class="icon-prepend fa fa-phone"></i>
											<input type="tel" name="cellphone" placeholder="ie: 123-456-7890" id="cellphone"data-mask="(999) 999-9999">
										</label>
									</section>
									<section class="col col-4">
										<label class="label">Phone Number</label>
										<label class="input"> <i class="icon-prepend fa fa-phone"></i>
											<input type="tel" name="phone" id="phone" placeholder="ie: 123-456-7890" data-mask="(999) 999-9999">
										</label>
									</section>
									<section class="col col-4">
										<label class="label">E-mail</label>
										<label class="input"> <i class="icon-prepend fa fa-envelope-o"></i>
											<input type="email" id="emailIN   "name="emailIN" placeholder="E-mail" required>
										</label>
									</section>

								</div>
								</fieldset>

								<fieldset>
								<section>
									<label class="label">Comments/Enquiries</label>
									<label class="textarea">
										<i class="icon-append fa fa-comment"></i>
										<textarea rows="4" name="message" id="message" required></textarea>
									</label>
								</section>
								<section>
									<label class="checkbox"><input type="checkbox" name="copy" id="copy"><i></i>Send a copy to my e-mail address</label>
								</section>

							</fieldset>

							<footer>
								<button  id ='sendBtn'class="btn btn-left btn-default">Submit</button>
							</footer>

							<div class="message">
								<i class="fa fa-thumbs-up"></i>
								<p>Your message was successfully sent!</p>
							</div>
						</form>
						</font>
					</div>
					<!-- end widget content -->

				</div>
				<!-- end widget div -->

			</div>
			<!-- end widget -->




		</article>

	</div>

</section>



<script type="text/javascript">

	var $fName = $('#fname');
	var $lName = $('#lname');
	var $msg = $('#message');
	var $email= $('#emailIN');
  var $phone = $('#cellphone');
  $.ajax({
    url: 'data/userconfig.json',
    dataType: 'json',
    success: function (data) {
        $fName.val(data.fName);
        $lName.val(data.lName);
        $email.val(data.email);
        $phone.val(data.phoneNumber);
    },
    error: function () {

    }
  })

	$('#sendBtn').on('click', function(){
    console.log($email.val());
    console.log($fName.val());

		var emailData = {
			fname: $fName.val(),
			lname: $lName.val(),
			message: $msg.val(),
			email: $email.val(),
		};

    var $contactForm = $("#contact-form").validate({
    			// Rules for form validation
    			rules : {
    				fname : {
    					required : true
    				},
    				lname : {
    					required : true
    				},
    				email : {
    					required : true,
    					email : true
    				},
    				message : {
    					required : true,
    					minlength : 10
    				}
    			},

    			// Messages for form validation
    			messages : {
    				fname : {
    					required : 'Please enter your name',
    				},
    				lname : {
    					required : 'Please enter your last name',
    				},
    				email : {
    					required : 'Please enter your email address',
    					email : 'Please enter a VALID email address'
    				},
    				message : {
    					required : 'Please enter your message'
    				}
    			},

    			// Ajax form submition
    			submitHandler : function(form) {
    				$(form).ajaxSubmit({
    					success : function() {

                send();
    					}
    				});
    			},

    			// Do not change code below
    			errorPlacement : function(error, element) {
    				error.insertAfter(element.parent());
    			}
    		});

    function send(){
  		$.ajax({
  			type: 'POST',
  			url: 'ajax/emailcontacts.php',
  			data: emailData,
  			success: function(response){
  				console.log(response);
  				console.log('ok');
          $("#contact-form").addClass('submited');

  			},
  			error: function(er){
          alert(er);
  				console.log(er);
  				console.log("merr");
  			}
  		});
    }
	})











	/* DO NOT REMOVE : GLOBAL FUNCTIONS!
	 *
	 * pageSetUp(); WILL CALL THE FOLLOWING FUNCTIONS
	 *
	 * // activate tooltips
	 * $("[rel=tooltip]").tooltip();
	 *
	 * // activate popovers
	 * $("[rel=popover]").popover();
	 *
	 * // activate popovers with hover states
	 * $("[rel=popover-hover]").popover({ trigger: "hover" });
	 *
	 * // activate inline charts
	 * runAllCharts();
	 *
	 * // setup widgets
	 * setup_widgets_desktop();
	 *
	 * // run form elements
	 * runAllForms();
	 *
	 ********************************
	 *
	 * pageSetUp() is needed whenever you load a page.
	 * It initializes and checks for all basic elements of the page
	 * and makes rendering easier.
	 *
	 */

	pageSetUp();

	/*
	 * ALL PAGE RELATED SCRIPTS CAN GO BELOW HERE
	 * eg alert("my home function");
	 *
	 * var pagefunction = function() {
	 *   ...
	 * }
	 * loadScript("js/plugin/_PLUGIN_NAME_.js", pagefunction);
	 *
	 * TO LOAD A SCRIPT:
	 * var pagefunction = function (){
	 *  loadScript(".../plugin.js", run_after_loaded);
	 * }
	 *
	 * OR you can load chain scripts by doing
	 *
	 * loadScript(".../plugin.js", function(){
	 * 	 loadScript("../plugin.js", function(){
	 * 	   ...
	 *   })
	 * });
	 */

	// pagefunction

	var pagefunction = function() {




	};

	// end pagefunction
	//loadScript("js/jquery-form/jquery-form.min.js", pagefunction);
	// run pagefunction


</script>
