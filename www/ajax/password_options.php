<div id="bodypwd">
          <div id="pwd_panel">
            <h2>Options</h2>
            <li>Old Password</li>
          <input type="password" name="oldPWD" id ="oldPWD" class = "pwdchangelabel" placeholder="Old password" ></br>
          <li>New Password</li>
          <input type="password" name="newPWD" id = "newPWD1" class = "pwdchangelabel" placeholder="New Password" ></br>
          <li>Repeat new Password</li>
          <input type="password" name="newPWD" id = "newPWD2" class = "pwdchangelabel" placeholder="New Password"></br>
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
