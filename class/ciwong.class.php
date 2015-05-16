<?php
class ciwong{
  private $access_token, $expires_in, $openid, $refresh_token, $token_type;
  private $schoolId;
  private $compType, $startTime, $examName;
  private $code;
  private $answer;
  private $record = false;
  public function login($userId, $password){
    $url = "http://121.14.117.216/oauth/token?username=$userId&scope=all&client_secret=d299ebe5a0495aef54be6c7d3b599e65&grant_type=password&client_id=100020&password=$password";
    $this->SetUserId($userId);
    $arr = json_decode($this->Get($url), true);
    if(isset($arr["errcode"])){
      return $arr;
      return false;
    }
    $this->SetToken($arr["access_token"], $arr["expires_in"], $arr["openid"], $arr["refresh_token"], $arr["token_type"]);
    return $this->GetSchoolId();
  }
  public function Step1(){
    $url = "http://mp.s.ciwong.com/s?compId=135&client_id=100020&access_token=" . $this->access_token . 
      "&openid=" . $this->openid . "&oauth_version=2.0&scope=all&clientip=10.".rand(0,255).".".rand(0,255).".".rand(0,255).//随机一下IP
      "&expires_in=" . $this->expires_in .
      "&refresh_token=null&token_type=bearer&schoolid=". $this->schoolId;
    //var_dump($url);
    //curl_setopt($this->ch, CURLOPT_HEADER, true);
    $this->Get($url);
    return $url;
  }
  public function Step2(){
    $url = "http://mp.s.ciwong.com/s/random?__biz=100020&sn=" . $this->access_token .
      "&compid=135&clienttype=0&schoolid=" . $this->schoolId . "&scope=all&openid=" . $this->openid .
      "&termtype=null&token_uid=null&oauth_version=2.0&_=" .time();
    //curl_setopt($this->ch, CURLOPT_HEADER, true);
    $arr = json_decode($this->Get($url), true);
    $this-> SetExam($arr["data"]["compType"], $arr["data"]["startTime"], $arr["data"]["examName"]);
    return true;
  }
  public function GetCode(){
    $url = "http://mp.s.ciwong.com/s/GetCode?at=" . $this->access_token ."&time=" . time();
    $this->code = $this->Get($url);
  }
  public function GetInfo(){
    $arr = array(
      "access_token"=>$this->access_token, 
      "openid"=>$this->openid, 
      "schoolId"=>$this->schoolId, 
      "examName"=>$this->examName, 
      "startTime"=>$this->startTime, 
      "code"=>"data:image/png;base64," . base64_encode($this->code)
    );
    //file_put_contents("D:/temp/code.jpg", $this->code);
    return json_encode($arr);
  }
  public function CheckCode($num, $access_token){
    $url = "http://mp.s.ciwong.com/s/CheckCode";
    $arr = json_decode($this->Post($url, array("num"=>$num, "at"=>$access_token)), true);
    if($arr["data"] == true)
      return true;
    else
      return false;
  }
  
  public function reportjson($access_token, $schoolId, $openid, $examName, $startTime, $code){
    $url = "http://mp.s.ciwong.com/s/reportjson";
    $arr = json_decode($this->Post($url, array(
      "__biz"=>100020,
      "sn"=>$access_token,
      "compId"=>135,
      "schoolid"=>$schoolId,
      "scope"=>"all",
      "openid"=>$openid,
      "termtype"=>"null",
      "token_uid"=>"null",
      "oauth_version"=>"2.0",
      "clientType"=>0,
      "cfgTypeCode"=>"A100001",
      "examName"=>$examName,
      "startTime"=>$startTime,
      "randCode"=>$code,
      "userAnswer"=>$this->GetAnswer($examName)
      )), true);
      //var_dump( $this->GetAnswer($examName));
      if($arr["data"]["currScore"] < 80){//小于80认为答案有问题
        file_put_contents("../data/data.php", $this->DelAnswer(file_get_contents("../data/data.php"), $examName));//删除答案
        $this->record = true;//重新记录答案
      }
      if($this->record === true){
        file_put_contents("../data/data.php", '$answer["' . $examName . '"] = \'' . $this->WorkAnswer($arr["data"]["result"]) ."';\r\n", FILE_APPEND);
      }
      //var_dump($arr);
      return $arr["data"]["currScore"];
  }
  
  public function GetAnswer($examName){
    if(isset($this->answer[$examName])){
      return @$this->answer[$examName];
    }else{
      $this->record = true;
      $data = file_get_contents("http://style.ciwong.net/comp/data/A100001/" . $examName);
      $part1 = substr($data, strpos($data, "一、"), strpos($data, "二、") - strpos($data, "一、"));
      $part2 = substr($data, strpos($data, "二、"), strpos($data, "三、") - strpos($data, "二、"));
      $part3 = strstr($data, "三、");
      preg_match_all("/defenP_(\d+)' style='display: none'/",
          $part1,
          $out1, PREG_SET_ORDER);
      preg_match_all("/defenP_(\d+)' style='display: none'/",
          $part2,
          $out2, PREG_SET_ORDER);
      preg_match_all("/defenP_(\d+)' style='display: none'/",
          $part3,
          $out3, PREG_SET_ORDER);
      $temp = array();
      $a = array("A", "B", "C", "D");
      for ($i = 0; $i < count($out1); ++$i){
        array_push($temp, '{"id":"' . $out1[$i][1] . '","val":"'.$a[rand(0, 3)].'"}');
      }
      for ($i = 0; $i < count($out2); ++$i){
        array_push($temp, '{"id":"' . $out1[$i][1] . '","val":"'.rand(0, 1).'"}');
      }
      for ($i = 0; $i < count($out3); ++$i){
        array_push($temp, '{"id":"' . $out1[$i][1] . '","val":"'.$a[rand(0, 3)].'"}');
      }
      return "[" . join($temp, ", ") . "]";
    }
  }
  public function SetAnswer($answer){
    $this->answer = $answer;
  }
  private function GetSchoolId(){
    if(!isset($this->access_token)&&!isset($this->openid)&&!isset($this->userId)){
      echo "还没登陆";
      return false; 
    }
    $url = "http://api.xixin61.com/v2/school/get_my_school?client_id=100020&access_token=" . $this->access_token .
      "&openid=" . $this->openid . "&oauth_version=2.0&scope=all&clientip=10.0.2.15&termtype=5&userId=" . $this->userId;
    $arr = json_decode($this->Get($url), true);
    if($arr["errcode"]!=0 && !isset($arr["data"][0]["schoolId"])){
      var_dump($arr);
      return false;
    }
    $this->schoolId = $arr["data"][0]["schoolId"];
    return true;
  }
  private function NewCurl(){//新建Curl服务
    $this->ch = curl_init(); 
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android 4.4.4; en-us; Nexus 4 Build/JOP40D) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Mobile Safari/537.36");//设置手机端
    
  }
  private function Get($url){
    if(!isset($this->ch)) $this->NewCurl();
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_POST, false);
    $r = curl_exec ($this->ch);
    return $r;
  }
  private function Post($url, $postArr){
    if(!isset($this->ch)) $this->NewCurl();
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_POST, true);
    $postData = array();
    foreach($postArr as $key => $value){
      array_push($postData, $key . "=" . urlencode($value));
    }
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, join($postData, "&"));
    $r = curl_exec ($this->ch);
    return $r;
  }
  private function SetToken($access_token, $expires_in, $openid, $refresh_token, $token_type){
    $this->access_token = $access_token;
    $this->expires_in = $expires_in;
    $this->openid = $openid;
    $this->refresh_token = $refresh_token;
    $this->token_type = $token_type;
  }
  private function SetUserId($userId){
    $this->userId = $userId;
  }
  private function SetExam($compType, $startTime, $examName){
    $this->compType = $compType;
    $this->startTime = $startTime;
    $this->examName = $examName;
  }
  private function WorkAnswer($str){
    $arr = explode("*", $str);
    $r = array();
    for($i = 0; $i < count($arr); ++$i){
      $arr1 = explode("|", $arr[$i]);
      array_push($r, '{"id":"' . $arr1[0] . '","val":"' . $arr1[3] . '"}');
    }
    return "[". join($r, ","). "]";
  }
  public function DelAnswer($data, $examName){
    //$data = file_get_contents("../data/data.php");
    $answer = explode("\r\n", $data);
    for($i = 0; $i < count($answer); ++$i){
      if(strpos($answer[$i], '$answer["' . $examName . '"]')!==false){
        unset($answer[$i]); 
     }
    }
    array_filter($answer);
    return join($answer, "\r\n");
  }
}


?>