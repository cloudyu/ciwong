<?php
if(!isset($_POST["userId"]) && !isset($_POST["password"])){
  echo json_encode(array("err" => "用户密码错误"));
  exit;
}
if(($_POST["userId"] == "") || ($_POST["userId"] == "")){
  echo json_encode(array("err" => "用户密码错误"));
  exit;
}
require_once("../class/ciwong.class.php");
$ciwong = new ciwong();
$msg = $ciwong->login($_POST["userId"], $_POST["password"]);
if($msg !== true){
  echo json_encode(array("err" => $msg["msg"]));
  exit;
}
$ciwong->Step1();
$ciwong->Step2();
$ciwong->GetCode();
echo $ciwong->GetInfo();
//

?>