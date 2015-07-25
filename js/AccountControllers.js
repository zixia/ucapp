angular.module('starter.accountcontrollers', [])

.controller('AccountCtrl', function($scope,AuthService,$state,$window) {
    $scope.user = $window.sessionStorage;
    console.log($scope.user);

    $scope.logout = function(){
        AuthService.logout();
        $state.go("login");
    }

    $scope.gofriendcircle = function(){
        $state.go("personalContactHomepage",{'contact':$scope.user.user_id});
    }
})



.controller('LoginCtrl',function($scope,$rootScope,AuthService,$ionicPopup,$ionicLoading){


    $scope.checklogin = false;
    $scope.login = function(username,password){
        $ionicLoading.show({
            template:'<i class = "ion-load-c"><br></i>登陆中...'
        });
    
         AuthService.login(username,password)
        .then(function(res){
            
            $ionicLoading.hide();
            if (res.ret === true) {
                //console.log("resresres");
                console.log(res);
                $rootScope.$broadcast(res);
            }
            else{
                $rootScope.$broadcast("login failed");
                // showAlert();
                $scope.checklogin = true;
            }
            
        },function(){
            $rootScope.$broadcast("transimit failed wuwuwu");
        });
    }

    // var showAlert = function() {
    //    var alertPopup = $ionicPopup.alert({
    //      template: '用户名或密码错误'
    //    });
    //    alertPopup.then(function(res) {
    //      console.log('Thank you for not eating my delicious ice cream cone');
    //    });
    //  };

    $scope.logout = function(){
        AuthService.logout();
    }

})
