angular.module('GlobalPath', [])

.factory("UrlPath",function(){
  var service = {};
  var urldomain = "http://17salsa.com/ucapp/ucapi/";

  // var urldomain = "http://127.0.0.1/ionic/myApp/www/ucapi/";

  service.getLoginpath = function(){
    return urldomain+"login.php";
  };

  service.getContactpath = function(){
    return urldomain+"showcontact.php";
  };

  service.getMessagePath = function(){
    return urldomain+"showmessage.php";
  };

  service.getMessListPath = function(){
    return urldomain+"showmessagelist.php";
  };

  service.getsendMessPath = function(){
    return urldomain+"sendmessage.php";
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

  service.getIdtransferurlPath = function(){
    return urldomain+"idtransfer.php";
  }

  service.getSendlikePath = function(){
    return urldomain+"receivelike.php";
  }

  service.getSendRemarkPath = function(){
    return urldomain+"receiveremark.php";
  }

  return service;
})

// .factory("BrowserHistory",function(){

//   service = {};

//   function getHash(){
//     var h = location.hash;

//     if(!h){
//       return "";
//     }else{
//       return location.hash;
//     }
//   }

//   service.changeHash = function(){
//     location.hash = "#"+nextHash++;
//   }

//   function changeHashCallBack(){
//     var hash = getHash();

//     if (curHash!=hash) {
//       curHash = hash;
//       alert("hash change:"+hash);
//     }
//   }

//   return service;


// })




