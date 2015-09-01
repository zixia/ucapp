angular.module('starter.messagecontrollers', ['luegg.directives'])

.controller('MessageCtrl', function($http, $scope, $ionicLoading, MessageService, Format, IdSearch) {
  $ionicLoading.show({
    template: '<i class = "ion-load-c"><br></i>加载中，请稍侯...'
  });

  MessageService.getMainInfo().success(function(data) {
    var idlistarray = new Array();
    for (var i = 0; i < data.b.length; i++) {
      idlistarray.push(data.b[i].fid);
    };
    $scope.messages = data.b;

    var idcache = IdSearch.getMainInfo(idlistarray).success(function(temp) {

      for (var j = 0; j < $scope.messages.length; j++) {       
        (function(jj){
          $scope.messages[jj].userinfo = temp.b[$scope.messages[jj].fid];
        })(j);
      }
    });

  }).then(function(){
    $ionicLoading.hide();
  })

  $scope.getstandardtime = function(ts){
    return Format.formattimestamp(ts);
  }

  $scope.refresh = function(){
    MessageService.getMainInfo().success(function(data){
      var idlistarray = new Array();
      for (var i = 0; i < data.b.length; i++) {
        idlistarray.push(data.b[i]['fid']);
      };
      $scope.messages = data.b;

      var idcache = IdSearch.getMainInfo(idlistarray).success(function(temp){

        for (var j = 0; j < $scope.messages.length; j++) {       
          (function(jj){
            $scope.messages[jj].userinfo = temp.b[$scope.messages[jj].fid];
          })(j);
        }
      })

    }).then(function(){
      $scope.$broadcast('scroll.refreshComplete');
    })

  }
})

.controller('MessageDetailCtrl', function($timeout, $scope, $stateParams, $state, $ionicLoading, MessageService, $window, IdSearch, $ionicHistory, $ionicNavBarDelegate) {

  $scope.setGlueHeight = function() {
    $scope.glueHeight = angular.element(document.querySelector('#glueContent')).prop('clientHeight') - 15;
    //document.getElementById('glueContent').clientHeight - 15;
  };

  $scope.setGlueHeight();

  $scope.sendfail = false;
  var contact_id = $stateParams.messageId;
  //获取联系人的id，name，avatar
  //userinfo 存储相关信息
  IdSearch.getMainInfo([contact_id]).success(function(data) {
    var fullarray = data.b;
    $scope.contact_info = fullarray[contact_id];
  });

  var account_img_src = $window.sessionStorage['user_avatar'];
  var account_id = $window.sessionStorage['user_id'];

  $scope.message_array = Array();

  $ionicLoading.show({
    template:'<i class = "ion-load-c"><br></i>Loading...'
  });

  MessageService.getDetailInfo(contact_id).success(function(data) {
    $scope.messageitem = data.b.reverse();
    $scope.setGlueHeight();
  }).then(function(){
    $ionicLoading.hide();
  });

  $scope.gomessage = function(){
    $state.go("tab.message");
  }

  $scope.gocontact = function(){
    // $state.go("contact-detail",{'contactId':contact_id});
    console.log(contact_id);
    $state.go("contact-detail",{'contact':contact_id});
  }

  $scope.format_img = function(id){
    if (id == account_id) {
      return account_img_src; 
    }
    else{
      return $scope.contact_info.avatar;
    }
  }

  $scope.format_class = function(id){
    if(id == account_id){
      return "right";
    }
    else{
      return "left";
    }
  }

  $scope.refresh = function(){
    start = start - refresh_num;
    console.log(start);
    MessageService.getDetailInfo(contact_id,start,refresh_num).success(function(data){
      if (data.b.message_array.length == 0) {
        $scope.message_array.empty = "没有更多消息了";
      }
      for (var i = data.b.message_array.length-1; i >= 0; i--) {
        $scope.message_array.unshift(data.b.message_array[i]);
      }
    }).then(function(){
      $scope.$broadcast('scroll.refreshComplete');
    });
  }

  $scope.sendmessagedetail = function(){
    $message_content = $scope.message_detail_send;
    $scope.message_detail_send = null;
    $message_json = {"fid":account_id,"txt":$message_content};
    MessageService.sendMessage(contact_id,$message_content).success(function(data){
      console.log(data.h.ret);
      if (typeof data.h.ret == "undefined") {
        // alert('发送失败');
        $scope.sendfail = true;
      }
      else if(data.h.ret!=0){
        // alert('发送失败');
        $scope.sendfail = true;
      }
      else
        $scope.messageitem.push($message_json);
    });

  };
})
