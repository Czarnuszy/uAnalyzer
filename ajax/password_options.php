            <div id="pbody" class="panel-body">
              <div class="row">
                <div class="col-md-3 col-lg-3 " align="center"> <img alt="User Pic" src="/img/avatars/male.png" class="img-circle img-responsive"> </div>

                <!--<div class="col-xs-10 col-sm-10 hidden-md hidden-lg"> <br>
                  <dl>
                    <dt>DEPARTMENT:</dt>
                    <dd>Administrator</dd>
                    <dt>HIRE DATE</dt>
                    <dd>11/12/2013</dd>
                    <dt>DATE OF BIRTH</dt>
                       <dd>11/12/2013</dd>
                    <dt>GENDER</dt>
                    <dd>Male</dd>
                  </dl>
                </div>-->
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
                            <input type="text" id="unt" class="form-control" placeholder="First Name" aria-describedby="sizing-addon3">
                          </div>
                        </td>
                      </tr>

                         <tr>
                             <tr>
                        <td>Last Name</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="text" id="unt" class="form-control" placeholder="Last Name" aria-describedby="sizing-addon3">
                          </div>
                        </td>
                      </tr>
                        <tr>
                        <td>Email</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="email" id="unt" class="form-control" placeholder="E-Mail" aria-describedby="sizing-addon3">
                          </div>
                        </td>
                      </tr>

                        <td>Phone Number</td>
                        <td>
                          <div class="input-group input-group-sm">
                            <input type="phoneNumber" id="unt" class="form-control" placeholder="Phone Number" aria-describedby="sizing-addon3">
                          </div>
                        </td>

                      </tr>

                    </tbody>
                  </table>
                  <div id = "chuj"></div>
                  <a id="saveUserData" class="btn btn-primary">Save</a>
                </div>
              </div>
            </div>



<script>

$('#changePWD').click(function () {
  $('#popupPWDmain').load('ajax/change_password.html');
})



</script>
<style>
.panel-body{
  width: 400px;
}
.user-row {
    margin-bottom: 14px;
}

.user-row:last-child {
    margin-bottom: 0;
}

.dropdown-user {
    margin: 13px 0;
    padding: 5px;
    height: 100%;
}

.dropdown-user:hover {
    cursor: pointer;
}

.table-user-information > tbody > tr {
    border-top: 1px solid rgb(221, 221, 221);
    width: 380px;

}

.table-user-information > tbody > tr:first-child {
    border-top: 0;
}


.table-user-information > tbody > tr > td {
    border-top: 0;
}
.toppad
{margin-top:20px;
}

input#unt{
  height:  25px;
}


</style>
