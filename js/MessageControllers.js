angular.module('starter.messagecontrollers', [])

.controller('MessageCtrl', function($http, $scope,$ionicLoading,MessageService,Format,IdSearch) {
    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    MessageService.getMainInfo().success(function(data){
        console.log(data);
        $scope.messages = data.b;

        for (var j = 0; j < $scope.messages.length; j++) {       
                (function(jj){
                    IdSearch.getMainInfo([$scope.messages[jj].fid]).success(function(data) {
                    var fullarray = data.b;
                    $scope.messages[jj].userinfo = fullarray;
                });

        })(j);
}


        }).then(function(){
            $ionicLoading.hide();
    });

    $scope.getstandardtime = function(ts){
            return Format.formattimestamp(ts);
        }

    $scope.test = function(){
        console.log('aaa');
    }

    // $scope.getuserinfo = function(id){
    //     //var idlist = [parseInt(id),2,3];
    //     var idlist = new Array(parseInt(id),2,3);
    //     console.log(idlist);
    //     console.log(typeof(idlist))
    //     IdSearch.getMainInfo(idlist);
    //     /*.success(function(data) {
    //         // console.log(data);
    //         // var userinfo = data.b[id];
    //     });*/
    //     // return userinfo;
    // }

    $scope.refresh = function(){
            MessageService.getMainInfo().success(function(data){
            $scope.messages = data.b;
            }).then(function(){
                $scope.$broadcast('scroll.refreshComplete');
            });
    }
})

.controller('MessageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,MessageService,$window)  {

    var num = $stateParams.messageId;

    var contact_id = $stateParams.messageId;

    var account_img_src = $window.sessionStorage['user_avatar'];

    $scope.message_array = Array();

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    var start = 4;//从message里面传递过来的
    var refresh_num = 2;

    MessageService.getDetailInfo(contact_id,start,refresh_num).success(function(data) {
        console.log(data.b);
        //   for (var i = 0; i < data.b.length; i++) {
        //     if(data.b[i].message_user_id == num){
        //         $scope.messageitem = data.b[i];
        //     }
        // }
        $scope.messageitem = data.b;
        for (var i = 0; i < data.b.message_array.length; i++) {
            $scope.message_array.push(data.b.message_array[i]);
        }

    }).then(function(){
        $ionicLoading.hide();
    });

    $scope.gomessage = function(){
        $state.go("tab.message");
    }

    $scope.gocontact = function(){
        $state.go("contact-detail",{'contactId':num});
    }

    $scope.format_img = function(source,img_src){
        if (source == "right") {
            return account_img_src; 
        }
        else{
            return img_src;
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

    //需要和紫霞调
    $scope.sendmessagedetail = function(){
        $message_content = $scope.message_detail_send;
        $message_json = {"name":"right","content":$message_content};
        $scope.messageitem.message_array.push($message_json);
    }
})