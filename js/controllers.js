angular.module('starter.controllers', [])

.controller('ActivityCtrl', function($http, $scope,$ionicLoading) {
    // $ionicLoading.show({
    //     template:'<i class = "ion-load-c"><br></i>Loading...'
    // });

    // $http.get('data/contact.json').success(function(data) {
    //       $scope.contacts = data;
    //     }).then(function(){
    //         $ionicLoading.hide();
    // });

})

// .controller("TestCtrl",function($http,$scope){
//     console.log("test");
// })

.controller('MessageCtrl', function($http, $scope,$ionicLoading,MessageService) {
    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    MessageService.getMainInfo().success(function(data){
        $scope.messages = data;
        }).then(function(){
            $ionicLoading.hide();
    });
})

.controller('MessageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,MessageService)  {

    var num = $stateParams.messageId;

    var account_img_src = "img/con4.jpg";

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    MessageService.getMainInfo().success(function(data) {
          for (var i = 0; i < data.length; i++) {
            if(data[i].message_user_id == num){
                $scope.messageitem = data[i];
            }
        }
    }).then(function(){
        $ionicLoading.hide();
    });

    $scope.gomessage = function(){
            $state.go("tab.message");
    }

    $scope.format_img = function(source,img_src){
        if (source == "right") {
            return account_img_src; 
        }
        else{
            return img_src;
        }
    }

    //需要和紫霞调
    $scope.sendmessagedetail = function(){
        $message_content = $scope.message_detail_send;
        $message_json = {"name":"right","content":$message_content};
        $scope.messageitem.message_array.push($message_json);
    }
})

.controller('ContactCtrl', function($http, $scope,$ionicLoading,$state,ContactService) {
    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    ContactService.getMainInfo().success(function(data){
        $scope.contacts = data;
    }).then(function(){
            $ionicLoading.hide();
    });

    $scope.godiscover = function(){
            $state.go("tab.discovery");
    }

})

.controller('ContactDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,ContactService) {

    var num = $stateParams.contactId;

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    ContactService.getMainInfo().success(function(data){
        for (var i = 0; i < data.length; i++) {
            if(data[i].contact_id == num){
                $scope.contactitem = data[i];
            }
        }
    }).then(function(){
            $ionicLoading.hide();
    });

    $scope.gocontact = function(){
            $state.go("contact");
    }

    // $scope.sendmessage = function(contact_id){
    //     $state.go("tab.message",{contact_id:contact_id});
    // }

})

.controller('DiscoveryCtrl', function($scope,$state) {
    $scope.gofriendcircle = function(){
        $state.go("friendcircle");
    }

    $scope.gocontact = function(){
        $state.go("contact");
    }
})

.controller('ChatroomCtrl', function($scope) {
  // Nothing to see here.
})

.controller('FriendcircleHeaderCtrl',function($scope){
    // $http.get('http://17salsa.com/home/s.php?rewrite=home-view-all').success(function(data) {
    //       $scope.infos = data;
    //     });
})

.controller('FriendcircleCtrl', function($scope,$http,$ionicPopup,Format,$ionicLoading,$state,$rootScope) {       
        $scope.clickarray = new Array();
        $scope.friend_id = null;
        $scope.inputshow = false;


        $scope.godiscover = function(){
            $state.go("tab.discovery");
        }

        $scope.clearclick = function(){
            $scope.clickarray = new Array();
            $scope.inputshow = false;
        }

        $scope.clickfun = function(num){
            var user = "aaaaaaa";

            if($scope.infos[num].reply_heart.indexOf(user)>-1){
                $scope.heart_tag =  "取消";
            }
            else{
                $scope.heart_tag =  "点赞";
            }   

            $scope.clickarray[num] = !$scope.clickarray[num];
            $scope.serial_num = num;

            
        }

        $scope.searchclick = function(num){
            return $scope.clickarray[num];
        }

        $scope.remark = function(id){
            $scope.friend_id = id;
            $scope.inputshow = true;
            console.log("id:"+ id);
        }

        //发送评论 和zixia调
        $scope.sendremark = function(){
            $remark_content = $scope.inputContent;
            $remark_json = {"name":"newremark","content":$remark_content};
            var serial = $scope.serial_num;//整个数据流中的第几个数据
            $scope.infos[serial].reply.push($remark_json);
            // return $http
            // // .post('http://17salsa.com/login.php',{username:"zixia"})
            // .post('http://127.0.0.1/test.php',{
            //     content:content,
            //     friend_id:$scope.friend_id
            // }).then(function(res){
            //   if (res.data.ret == true) {
                // $scope.infos[serial].reply.push($remark_json);

            //   }
            //   else{
            //     console.log('aaaaaaa');
            //     alert('评论失败');
            //   }
            // return res.data;
            // };
        }

        //点赞 和zixia调
        $scope.sendheart = function(){
            var user = 'aaaaaaa';
            var serial = $scope.serial_num;//整个数据流中的第几个数据
            if($scope.infos[serial].reply_heart.indexOf(user)>-1){
                var reply_heart_index = $scope.infos[serial].reply_heart.indexOf(user);
                $scope.infos[serial].reply_heart.splice(reply_heart_index, 1);
            }
            else{
                $scope.infos[serial].reply_heart.push(user);
            }   
        }

         $ionicLoading.show({
            template:'<i class = "ion-load-c"><br></i>Loading...'
         });
         $http.get('data/friendcircle.json').success(function(data) {
         // $http.get('http://17salsa.com/home/s.php?rewrite=home-view-all').success(function(data) {
          $scope.infos = data;
        }).then(function(){
            $ionicLoading.hide();
        });
        
        //格式化类,根据收到的图片展示不同的样式
        $scope.formatcell = function(cell){
            return Format.formatcell(cell);
        }

        $scope.refresh = function(){
            $http.get('http://17salsa.com/home/s.php?rewrite=home-view-all').success(function(data) {
              $scope.infos = data;
            }).then(function(){
                $scope.$broadcast('scroll.refreshComplete');
            });
        }

        
        


       
        
        // $scope.showPopup = function(){
        // 	var myPopup = $ionicPopup.show({
        // 		templateUrl:"../templates/input.html",
        // 		scope:$scope,
        // 		buttons:[
        // 		   {text:'取消'},
        // 		   {
        // 			text:'保存',
        // 			type:'button-positive',
        // 		   }
        // 		]
        // 	});
        // }
        
        
})

.controller('AccountCtrl', function($http, $scope,$ionicLoading,$ionicNavBarDelegate) {
	$ionicNavBarDelegate.showBackButton(true);

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    $http.get('data/user.json').success(function(data) {
          $scope.users = data;
        }).then(function(){
            $ionicLoading.hide();
    });
})


.controller('PersonalHomepageCtrl', function($http, $scope,$state,$ionicLoading,PersonalHomepageService) {
    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    PersonalHomepageService.getUserInfo().success(function(data) {
        $scope.userBasicInfo = data;
    });

    PersonalHomepageService.getContentInfo().success(function(data) {
        $scope.userContentInfo = data;
    }).then(function(){
        $ionicLoading.hide();
    });

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
        if(type == "pic"){
            return true;
        }
        else{
            return false;
        }
    }

    $scope.infoshowword = function(type){
        if(type == "word"){
            return true;
        }
        else{
            return false;
        }
    }

     $scope.gofriendcircle = function(){
        $state.go("friendcircle");
    }
})

.controller('PersonalHomepageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,PersonalHomepageService) {

    var num = $stateParams.infoId;

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    PersonalHomepageService.getUserInfo().success(function(data) {
        $scope.userBasic = data;
    });

    PersonalHomepageService.getContentInfo().success(function(data) {
        $scope.InfoItem = data[num];
    }).then(function(){
        $ionicLoading.hide();
    });

    $scope.goaccount = function(){
        $state.go("personalHomepage");
    }
    

})

.controller('LoginCtrl',function($scope,$rootScope,AuthService){
    $scope.login = function(username,password){
         AuthService.login(username,password)
        .then(function(res){
            if (res.ret === true) {
                console.log(res);
                $rootScope.$broadcast(res);
            }
            else{
                $rootScope.$broadcast("login failed");
            }
            
        },function(){
            $rootScope.$broadcast("transimit failed wuwuwu");
        });
    };

    $scope.logout = function(){
        AuthService.logout();
    }

    javascript:void(0);
})



.controller('AnalyticsCtrl', function($scope) {

})


