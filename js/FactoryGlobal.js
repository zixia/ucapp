angular.module('GlobalPath', [])

.factory("UrlPath",function(){
  var service = {};
  var urldomain = "http://17salsa.com/app++/";

  service.getLoginpath = function(){
    return urldomain+"login.php";
  };

  service.getContactpath = function(){
    return urldomain+"showcontact.php";
  };

  service.getMessagePath = function(){
    return urldomain+"showmessage.php";
  };

  service.getContactUserPath = function(){
    return urldomain+"showcontactInfo.php";
  };

  service.getContentPath = function(){
    return urldomain+"showhomepage.php";
  };

  return service;
})