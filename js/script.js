// JavaScript Document
$(document).ready(function(){
  $("form").submit(function(){
    userId = $("#userId").val();
    password = $("#password").val();
    code = $("#code").val();
    $("[type='submit']").button('loading');
    if($("#codeform").css("display") == "none"){
      $.post("ajax/login.php",{userId:userId,password:password},function(data){
    $("[type='submit']").button('reset');
        json = eval('(' + data + ')');
        if(json.err !== undefined){
          alert(json.err);
          $("#password").val("");
        }else{
          $("#codeform").css("display", "block");
          $("#codeimg").attr("src", json.code);
          window.access_token = json.access_token;
          window.examName = json.examName;
          window.openid = json.openid;
          window.schoolId = json.schoolId;
          window.startTime = json.startTime;
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
    $("[type='submit']").button('reset');
        json = eval('(' + data + ')');
        if(json.err !== undefined){
          if(json.err =="验证码错误, 请重新点击提交获取验证码"){
            $("#codeform").css("display", "none");
            $("#code").val("");
          }
          alert(json.err);
        }else{
          alert(json.msg);
          $("#codeform").css("display", "none");
          $("#code").val("");
          $("#userId").val("");
          $("#password").val("");
        }
      }
      );
    }
    return false;
  });
});
