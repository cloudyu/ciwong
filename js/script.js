// JavaScript Document
$(document).ready(function(){
  $("form").submit(function(){
    userId = $("#userId").val();
    password = $("#password").val();
    code = $("#code").val();
    console.log(password, userId);
    if($("#codeform").css("display") == "none"){
      $.post("ajax/login.php",{userId:userId,password:password},function(data){
        json = eval('(' + data + ')');
        if(json.err !== undefined){
          alert(json.err);
        }else{
          $("#codeform").css("display", "block");
          $("#codeimg").attr("src", json.code);
          window.access_token = json.access_token;
          window.examName = json.examName;
          window.openid = json.openid;
          window.schoolId = json.schoolId;
          window.startTime = json.startTime;
          console.log(json);
        }
      });
    }else{
      $.post("ajax/work.php",{access_token:window.access_token,
        examName:window.examName,
        openid:window.openid,
        schoolId:window.schoolId,
        startTime:window.startTime,
        code:code}
      ,function(data){
        json = eval('(' + data + ')');
        console.log(data);
        if(json.err !== undefined){
          if(json.err =="验证码错误, 请重新点击提交获取验证码"){
            $("#codeform").css("display", "none");
          }
          alert(json.err);
        }else{
          alert(json.msg);
        }
        
      });
    }
    return false;
  });
});
