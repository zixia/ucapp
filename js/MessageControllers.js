angular.module('starter.messagecontrollers', [])

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

.controller('MessageDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,MessageService,$window)  {

    var num = $stateParams.messageId;

    var account_img_src = $window.sessionStorage['user_avatar'];

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

    //需要和紫霞调
    $scope.sendmessagedetail = function(){
        $message_content = $scope.message_detail_send;
        $message_json = {"name":"right","content":$message_content};
        $scope.messageitem.message_array.push($message_json);
    }
})