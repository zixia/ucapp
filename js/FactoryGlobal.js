angular.module('GlobalPath', [])

.factory("UrlPath",function(){
  var service = {};
  // var urldomain = "http://17salsa.com/ucapp/ucapi/";

  var urldomain = "http://127.0.0.1/ionic/myApp/www/ucapi/";

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

  service.getEventPath = function(){
    return urldomain+"showevent.php";
  }

  service.getEventDetailPath = function(){
    return urldomain+"showeventdetail.php";
  }

  return service;
})