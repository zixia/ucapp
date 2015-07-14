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

.controller('MessageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,MessageService,$window,IdSearch)  {

    // var num = $stateParams.messageId;

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
        var h = $(document).height()-$(window).height();
        $(document).scrollTop(h);

        // var div = document.getElementById("pagescroll");
        // // div.scrollTop = 1000;
        // $("#pagescroll").offsetHeight = 1000;
        // // alert(div.scrollHeight);
    }).then(function(){
        $ionicLoading.hide();
    });

    $scope.gomessage = function(){
        $state.go("tab.message");
    }

    $scope.gocontact = function(){
        $state.go("contact-detail",{'contactId':contact_id});
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
        $message_json = {"fid":account_id,"txt":$message_content};
        MessageService.sendMessage(contact_id,$message_content).success(function(data){
            console.log(data.h.ret);
            if (typeof data.h.ret == "undefined") {
               alert('发送失败');
            }
            else if(data.h.ret!=0){
                alert('发送失败');
            }
            else
                $scope.messageitem.push($message_json);
        });

    }
})