angular.module('starter.contactcontrollers', [])

.controller('ContactCtrl', function($http, $scope,$ionicLoading,$state,ContactService) {
    $ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    ContactService.getMainInfo().then(function(res){
        $scope.contacts = res.data;
    }).then(function(){
            $ionicLoading.hide();
    });

    $scope.godiscover = function(){
            $state.go("tab.discovery");
    }

})

.controller('ContactDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,ContactService,$window) {

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

    $scope.gocback = function(){
        $window.history.back();
    }

    // $scope.sendmessage = function(contact_id){
    //     $state.go("tab.message",{contact_id:contact_id});
    // }

})
