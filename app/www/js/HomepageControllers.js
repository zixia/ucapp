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


.controller('PersonalHomepageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,PersonalHomepageService,$window,Format,IdSearch) {

    
    var Paraarray = $stateParams.infoId;
    var ParaObj = JSON.parse(Paraarray);

    $scope.showchat = false;

    $scope.showremark = false;

    $scope.clickfun = function(){
        $scope.showchat = !$scope.showchat;
        if($scope.infos[num].like){
          if ($scope.infos[num].like.indexOf(user) > -1) {
            $scope.heart_tag =  '取消';
          } else {
            $scope.heart_tag =  '点赞';
          }
        }
        else {
          $scope.heart_tag =  '点赞';
        }
    }

    $scope.clickremark = function(){
        $scope.showremark = !$scope.showremark;
        $scope.showchat = !$scope.showchat;
    }

    var num = ParaObj.infoId;
    var contactId = ParaObj.contactId;

    // var idcache = IdSearch.getMainInfo(idlistarray).success(function(temp){
    //     $scope.idcache = temp.b;
    //   })

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
      console.log(idlistarray);
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
console.log(user);


     //发送评论 和zixia调
        $scope.sendremark = function(){
            
        }

        //点赞 和zixia调
        $scope.sendheart = function(){
            $scope.showchat = !$scope.showchat;

            PersonalHomepageService.sendlike($scope.InfoItem.id).success(function(data){
                if(data.h.ret == 0){
                    console.log('success!!');
          
                    console.log($scope.InfoItem);
                    if($scope.InfoItem.like.indexOf(user)>-1){
                        var reply_heart_index = $scope.InfoItem.like.indexOf(user);
                        $scope.InfoItem.like.splice(reply_heart_index, 1);
                    }
                    else{
                        console.log($scope.InfoItem);
                        $scope.InfoItem.like.push(user);
                    }
                    IdSearch.getMainInfo($scope.InfoItem.like).success(function(data) {
                    var fullarray = data.b;
                    console.log(fullarray);
                    console.log(fullarray.username);
                    $scope.InfoItem.likelist = fullarray[user].username;
                    });  
                }
                else{
                    alert("点赞失败"+data.h.r);
                }
                
                
             }); 
            
             
        }

})
