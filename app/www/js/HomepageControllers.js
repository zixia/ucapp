angular.module('starter.homepagecontrollers', [])

.controller('PersonalHomepageCtrl', function($window,$http, $scope,$state,$ionicLoading,PersonalHomepageService,Format) {
    var passPara = {};
    var user_own_id = $window.sessionStorage['user_id'];

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    $scope.title = "相册";
    $scope.gobackbutton = "朋友圈";

    $scope.userBasicInfo = PersonalHomepageService.getUserInfo();

    passPara.contactId = $scope.userBasicInfo.user_id;

    PersonalHomepageService.getContentInfo(user_own_id).success(function(data) {
        $scope.userContentInfo = data;
        console.log($scope.userContentInfo);
    }).then(function(){
        $ionicLoading.hide();
    });

     $scope.getdate = function(ts){
        var timearray = Format.formattimefriendcircle(ts);
        if(timearray.date<10){
            timearray.date = '0'+timearray.date;

        }
        return timearray.date;
    }

    $scope.getmonth = function(ts){
        var timearray = Format.formattimefriendcircle(ts);
        return timearray.month;
    }

    $scope.Parafun = function(index){
        passPara.infoId = index;
        return passPara;
    }


    $scope.Parafun = function(index){
        passPara.infoId = index;
        return passPara;
    }

    $scope.formatcell = function(num){
         if(num == 1){
            return 1;
          }
          if(num == 2){
            return 2;
          }
          else{
            return 3;
          }
    };

    $scope.infoshowpic = function(type){
        if(type == "img"){
            return true;
        }
        else{
            return false;
        }
    }

    $scope.infoshowword = function(type){
        if(type == "txt"){
            return true;
        }
        else{
            return false;
        }
    }

     $scope.gofriendcircle = function(){
        $state.go("friendcircle");
    }

    $scope.goback = function(contactId){
        $state.go("friendcircle");
    };
})

.controller('PersonalContactHomepageCtrl', function($http, $scope,$state,$ionicLoading,$stateParams,PersonalHomepageService,Format) {
    // console.log($stateParams.contact);
    var num = $stateParams.contact;
    console.log(num);
    // var num = $stateParams.contact;


    // var num = contactPath.id;
    // var urlPara = contactPath.path;

    $scope.urlPath = {};
    $scope.urlPath.id = num;
    // $scope.urlPath.path = contactPath.path;

    var passPara = {};


    $scope.gobackbutton = "详细资料";

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    PersonalHomepageService.getContactUserInfo(num).success(function(data) {
        $scope.userBasicInfo = data;
        $scope.title = $scope.userBasicInfo.user_name;
        passPara.contactId = $scope.userBasicInfo.user_id;
    });

    PersonalHomepageService.getContentInfo(num).success(function(data) {
        $scope.userContentInfo = data;
        console.log(data);
    }).then(function(){
        $ionicLoading.hide();
    });

    $scope.getdate = function(ts){
        var timearray = Format.formattimefriendcircle(ts);
         if(timearray.date<10){
            timearray.date = '0'+timearray.date;

        }
        return timearray.date;
    }

    $scope.getmonth = function(ts){
        var timearray = Format.formattimefriendcircle(ts);
        return timearray.month;
    }

    $scope.Parafun = function(index){
        passPara.infoId = index;
        return passPara;
    }

    $scope.formatcell = function(num){
         if(num == 1){
            return 1;
          }
          if(num == 2){
            return 2;
          }
          else{
            return 3;
          }
    };

    $scope.infoshowpic = function(type){
        if(type == "img"){
            return true;
        }
        else{
            return false;
        }
    };

    $scope.infoshowword = function(type){
        if(type == "txt"){
            return true;
        }
        else{
            return false;
        }
    };

     $scope.gofriendcircle = function(){
        $state.go("friendcircle");
    };


    $scope.goback = function(){
        // var contactPathstr = JSON.stringify(contactPath);
        // $state.go($scope.urlPath.path,{'contact':contactPathstr});
        history.go(-1);
    };
})


.controller('PersonalHomepageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,PersonalHomepageService,$window,Format,IdSearch,$timeout) {

    
    var Paraarray = $stateParams.infoId;
    var ParaObj = JSON.parse(Paraarray);

    $scope.showchat = false;

    $scope.showremark = false;

    $scope.clickfun = function(){
        var user = $window.sessionStorage['user_id'];

        if($scope.InfoItem.like){
          if ($scope.InfoItem.like.indexOf(user) > -1) {
            $scope.heart_tag =  '取消';
          } else {
            $scope.heart_tag =  '点赞';
          }
        }
        else {
          $scope.heart_tag =  '点赞';
        }
        $scope.showchat = !$scope.showchat;
    }

    $scope.clearclick = function(){
        $scope.showchat = false;
    }

    $scope.clickremark = function(){
        $scope.showremark = !$scope.showremark;
        $scope.showchat = !$scope.showchat;
    }

    $scope.remark = function(id) {
        $scope.friend_id = id;
        $scope.inputshow = true;
        console.log('id:' + id);
        // focus will not work without timeout by zixia 201508
        $timeout(function() {
          document.querySelector('#inputContent').focus();
        });
    };

    var num = ParaObj.infoId;
    var contactId = ParaObj.contactId;

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    if (contactId == $window.sessionStorage['user_id']) {
        $scope.userBasic = PersonalHomepageService.getUserInfo();
    }
    else{
        PersonalHomepageService.getContactUserInfo(contactId).success(function(data) {
        $scope.userBasic = data;
        });
    }
    PersonalHomepageService.getContentInfo(contactId).success(function(data) {
      $scope.InfoItem = data.b[num];

      var idlistarray = new Array();

        if (data.b[num].like) {
          for(var j = 0; j<data.b[num].like.length;j++){
            if(idlistarray.indexOf(data.b[num].like[j])== -1){
              idlistarray.push(data.b[num].like[j]);
            }
          }
        }
        if (data.b[num].reply) {
          for(var m = 0; m<data.b[num].reply.length;m++){
            if(idlistarray.indexOf(data.b[num].reply[m][0])==-1){
              idlistarray.push(data.b[num].reply[m][0]);
            }
          }
        }    
      var idcache = IdSearch.getMainInfo(idlistarray).success(function(temp){
        $scope.idcache = temp.b;
      })

    }).then(function(){
        $ionicLoading.hide();
    });

    $scope.getstandardtime = function(ts){
        var timearray = Format.formattimefriendcircle(ts);
        return timearray.timestandard;
    }

    $scope.goback = function(){
        history.back();
    }


    var user = $window.sessionStorage['user_id'];

    $scope.sendremark = function() {
        var username = $window.sessionStorage['user_name'];
        var user = $window.sessionStorage['user_id'];
        var remark_content = $scope.inputContent;
        var remark_json = [user,remark_content];

        contact_id = $scope.InfoItem.uid;
        item_id = $scope.InfoItem.id

        PersonalHomepageService.sendremark(contact_id,item_id,remark_content).success(function(data) {
          if (data.h.r === 0) {
            $scope.InfoItem.reply.push(remark_json);

            var userarray = new Array();
            userarray.push(user);

            var idcache = IdSearch.getMainInfo(userarray).success(function(temp){
                angular.extend($scope.idcache,temp.b);
            })
          } else {
            alert('评论失败' + data.h.ret)
          }
        })
        $scope.inputshow = false;
        $scope.inputContent = null;
    }

    $scope.sendheart = function() {
        var user = $window.sessionStorage['user_id'];
        var is_like;//发送是否已经点赞

        if ($scope.InfoItem.like) {
          if ($scope.InfoItem.like.indexOf(user) > -1) {
           is_like = false;
          }
          else
             is_like = true;
        }  
        else{
          is_like = true;
        }  

        PersonalHomepageService.sendlike($scope.InfoItem.id,is_like).success(function(data) {
          if (data.h.ret === 0) {
            if ($scope.InfoItem.like.indexOf(user) > -1) {
              var reply_heart_index = $scope.InfoItem.like.indexOf(user);
              $scope.InfoItem.like.splice(reply_heart_index, 1);
            } else {
              $scope.InfoItem.like.push(user);
            }
            var userarray = new Array();
            userarray.push(user);
            var idcache = IdSearch.getMainInfo(userarray).success(function(temp){
                angular.extend($scope.idcache,temp.b);
            })
          } else {
            alert('点赞失败' + data.h.r);
          }
        })
    }

    $scope.likeshow = function(like){
        if(like){
          if(like.length >0){
            return true;
          }
          else
            return false;
        }
        else
          return false;
      }

    $scope.showcomma = function(id,idarray){
        if(idarray.indexOf(id) == idarray.length-1){
            return false;
        }
        else
            return true;
    }
})
