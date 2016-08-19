<div id="bodypwd">
          <div id="pwd_panel">
            <h2>Options</h2>

              <li class="list-group-item">Old Password</li>

              <div class="input-group" id ="testinput">
                <span class="input-group-addon"   id="basic-addon1"></span>
                <input type="Password" id ="oldPWD" class="form-control" placeholder="Password " aria-describedby="basic-addon1">
              </div>
              <li class="list-group-item">New Password</li>
              <div class="input-group" id ="testinput">
                <span class="input-group-addon"   id="basic-addon1"></span>
                <input type="Password" id ="newPWD1" class="form-control" placeholder="New Password" aria-describedby="basic-addon1">
              </div>

              <li class="list-group-item" >Repeat new Password</li>
              <div class="input-group" id ="testinput">
                <span class="input-group-addon"   id="basic-addon1"></span>
                <input type="Password" id ="newPWD2" class="form-control" placeholder="New Password" aria-describedby="basic-addon1">
              </div>
              <button id="savePwdBTN"> Save </button>
            <div id = "statuspwd"></div>
          </div>

  </div>

<script>

$("#savePwdBTN").click(function () {
    var $oldpwd = $('#oldPWD');
    var $newpwd = $('#newPWD1');
    var $newpwd2 = $('#newPWD2');
    console.log($oldpwd.val());

    pwdData = {
      usrPWD: $oldpwd.val(),
      newPWD: $newpwd.val(),
      newPWD2: $newpwd2.val()
    }
    console.log(pwdData);

  $.ajax({
      url: 'ajax/change_pwd.php',
      type: 'POST',
    //  dataType: 'json',
       data: {  usrPWD: $oldpwd.val(),
         newPWD: $newpwd.val(),
         newPWD2: $newpwd2.val()},
      success: function (data) {
        console.log(data);
        if(data == 1){
        console.log(pwdData);
        $('#statuspwd').html('Password changed!');
      }else if (data == 0) {
        console.log("error");
        $('#statuspwd').html('Error!');
      }
    },
    error: function(){
      console.log("Connection error");
      $('#statuspwd').html('Connection Error!');
    }

});
});

</script>
