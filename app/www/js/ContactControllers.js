angular.module('starter.contactcontrollers', [])

.controller('ContactCtrl', function($scope,$ionicLoading,$state,ContactService) {
    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>加载中，请稍后...'
    });

    ContactService.getMainInfo().then(function(res){
        $scope.contacts = res.data;
    }).then(function(){
            $ionicLoading.hide();
    });

    $scope.refresh = function(){
    ContactService.getMainInfo().then(function(res){
        $scope.contacts = res.data;
        }).then(function(){
         $scope.$broadcast('scroll.refreshComplete');
        })

    }

    $scope.godiscover = function(){
            $state.go("tab.discovery");
    }

})

.controller('ContactDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,ContactService,$window,$location, $ionicHistory, $ionicViewSwitcher) {
    // var contactPath = JSON.parse($stateParams.contact);
    // console.log(contactPath);
    console.log($stateParams);

    // var num = contactPath.id;
    // var urlPara = contactPath.path;
    var num = $stateParams.contact;

    // $scope.urlPath = {};
    // $scope.urlPath.id = contactPath.id;
    // $scope.urlPath.path = contactPath.path;

    // var num = $stateParams.contact;
    // console.log(num);

    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    ContactService.getMainInfo().success(function(data){
        // $scope.contactpath = {};

        for (var i = 0; i < data.length; i++) {
            if(data[i].contact_id == num){
                $scope.contactitem = data[i];
                // $scope.contactpath.id = $scope.contactitem.contact_id;
                // $scope.contactpath.path = "contact-detail";
                $scope.user_id = $scope.contactitem.contact_id;
            }
        }
        //console.log($scope.contactitem)
    }).then(function(){
            $ionicLoading.hide();
    });


    $scope.goback = function(){
            history.back()
    }

    // XXX by zixia 201508
    $scope.sendmessage = function(contact_id){
      $ionicViewSwitcher.nextDirection('forward');  

      $ionicHistory.nextViewOptions({
        historyRoot: false,
        disableBack: false
      });

      $state.go("tab.message-detail", {messageId: contact_id});
    }
})
