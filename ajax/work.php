<?php

if(!isset($_POST["access_token"]) && !isset($_POST["examName"])&& !isset($_POST["openid"])&& !isset($_POST["schoolId"])&& !isset($_POST["startTime"]) && !isset($_POST["code"])){
  echo json_encode(array("err" => "参数不正确"));
  exit;
}
if(($_POST["access_token"] == "") || ($_POST["examName"] == "") || ($_POST["openid"] == "") || ($_POST["schoolId"] == "") || ($_POST["startTime"] == "") || ($_POST["code"] == "")){
  echo json_encode(array("err" => "参数不正确"));
  exit;
}
require_once("../class/ciwong.class.php");
$ciwong = new ciwong();
if($ciwong->CheckCode($_POST["code"], $_POST["access_token"])==false){
  echo json_encode(array("err" => "验证码错误, 请重新点击提交获取验证码"));
  exit;
}
$score = $ciwong->reportjson($_POST["access_token"], $_POST["schoolId"], $_POST["openid"], $_POST["examName"], $_POST["startTime"], $_POST["code"]);
echo json_encode(array("msg" => "这次你考了".$score."分"));
?>
