<?php
if(@$_GET['Key'] != "123456789")exit;
require("data.php");
echo "<?php" . "\r\n";
echo '$answer = array();' . "\r\n";
foreach ($answer as $key => $value){
  echo '$answer["' . $key . '"] = \'' . $value . "'" . "\r\n";
}?>