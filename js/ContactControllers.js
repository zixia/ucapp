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

    // $scope.passcontact = function(id){
    //     var contactPath = {};
    //     contactPath.id = id;
    //     contactPath.path = "contact";
    //     return contactPath;
    // }

    $scope.godiscover = function(){
            $state.go("tab.discovery");
    }

})

.controller('ContactDetailCtrl', function($scope,$stateParams,$state,$ionicLoading,ContactService,$window,$location) {

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
        console.log($scope.contactitem)
    }).then(function(){
            $ionicLoading.hide();
    });


    $scope.goback = function(){
            history.back()
    }


    

    // $scope.sendmessage = function(contact_id){
    //     $state.go("tab.message",{contact_id:contact_id});
    // }

})
