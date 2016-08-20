            <div id="pbody" class="panel-body">
              <div class="row">
                <div class="col-md-3 col-lg-3 " align="center"> <img alt="User Pic" src="/img/avatars/male.png" class="img-circle img-responsive"> </div>

                <div class=" col-md-9 col-lg-9 ">
                  <table class="table table-user-information">
                    <tbody>
                      <tr>
                        <td>Login:</td>
                        <td>User</td>
                      </tr>
                      <tr>
                        <td>Password</td>
                        <td>******** <button id='changePWD'>Change</button></td>
                      </tr>
                      <tr>
                        <td>Name</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="text" id="fnameInput" class="form-control" placeholder="First Name" aria-describedby="sizing-addon3">
                          </div>
                        </td>
                      </tr>

                         <tr>
                             <tr>
                        <td>Last Name</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="text" id="lnameInput" class="form-control" placeholder="Last Name" aria-describedby="sizing-addon3">
                          </div>
                        </td>
                      </tr>
                        <tr>
                        <td>Email</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="email" id="emailInput" class="form-control" placeholder="E-Mail" aria-describedby="sizing-addon3">
                          </div>
                        </td>
                      </tr>

                        <td>Phone Number</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="phoneNumber" id="phoneInput" class="form-control" placeholder="Phone Number" aria-describedby="sizing-addon3">
                          </div>
                        </td>

                      </tr>

                    </tbody>
                  </table>
                  <a id="saveUserData" class="btn btn-primary">Save</a>
                  <div id = "alert"></div>

                </div>
              </div>
            </div>



<script>

var $name = $('#fnameInput');
var $lname = $('#lnameInput');
var $mail = $('#emailInput');
var $phone = $('#phoneInput');

$.ajax({
  url: 'data/userconfig.json',
  dataType: 'json',
  success: function (data) {
      $name.val(data.fName);
      $lname.val(data.lName);
      $mail.val(data.email);
      $phone.val(data.phoneNumber);
  },
  error: function () {

  }
})

$('#saveUserData').click(function () {
  var userdata = {
    fName: $name.val(),
    lName: $lname.val(),
    email: $mail.val(),
    phoneNumber: $phone.val()
  }

    $.ajax({
      url:'ajax/change_user_data.php',
      type: 'POST',
      dataType: 'json',
      data: {userData: JSON.stringify(userdata) },
      success: function (d) {
        $('#alert').text("Succes!");
      },
      error: function(){
        $('#alert').text("Error!");
      }
    })
})

$('#changePWD').click(function () {
  $('#popupPWDmain').load('ajax/change_password.html');
})






</script>
