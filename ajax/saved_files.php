<?php
$directory = '../data/Saves';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
$amount_files = count($scanned_directory);

for($i = 2; $i <= $amount_files; $i+=3){
    echo "case '".$scanned_directory[$i]."': ";
    echo 'w2ui.grid.reload();';
  //  echo "w2ui.grid.clear(); ";
//  echo "w2ui.grid.clear();";
    echo "console.log('ds'); ";
  // echo   "w2ui.layout.content('main',  w2ui.grid); ";
    echo "$('#gbod').w2render('grid')";
    echo "
    var NumberofLines;
    $.ajax({
          url: "."'ajax/files_size.php',
          type: "."'POST'".',
          data: { DisplayedRecords:'. "'".$scanned_directory[$i]."'"." },
          success: function(response) {
            NumberofLines= response-1;
              console.log(response);
            }
          });
          $.ajax({
                url: "."'ajax/open_homeid.php',
                type: "."'POST'".',
                data: { fileName:'. "'".$scanned_directory[$i+1]."'"." },
                success: function(response) {
                  home_id = response;
                  console.log(3432);
                    console.log(response);
                  }
                });

    $.ajax({
          url: "."'ajax/open_file_data.php',
          type: "."'POST'".',
          data: {'."'data'".":'".$scanned_directory[$i]."'".'}'.',
          dataType:'. '"json"'. ',
          success: function(data) {
            w2ui.grid.clear();
            var color = "";
            for(x=0; x<	NumberofLines; x++){
              if (data[x][2] != home_id){
                  data[x][3] = '."'-'".';
                  data[x][5] = '."'-';".'
                }else {
                  color = parse_sqnum(x, data);
                }
              w2ui['."'grid'".'].records.push({
                recid : x+1,
                 id: x+1,
                rssi: data[x][1],
                data: data[x][0],
                source: data[x][3],
                route: data[x][12],
                destination: data[x][5],
               command: data[x][7],
               h_id: data[x][2],
               style: "background-color: " + color

               });

          }
        }
      });';
    echo  "break; ";
 }


?>
