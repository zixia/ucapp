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

    $scope.gosetting = function(){
        $state.go("setting")
    }
})



.controller('LoginCtrl',function($scope,$rootScope,AuthService,$ionicPopup,$log){
    $scope.checklogin = false;
    $scope.errTxt = '';
    $scope.login = function(username,password){
        $log.log("logining, username: " + username );
        AuthService.login(username,password)
        .then(function(res){
            $log.log('AuthService.login.then...' );
            if (res.ret === true) {
                //console.log("resresres");
                $log.log(res);
                $rootScope.$broadcast(res);
            } else {
                $scope.errTxt = '用户名密码错误'

                if (res.txt) 
                    $scope.errTxt = res.txt

                $scope.checklogin = true;

                $rootScope.$broadcast("login failed");
                // showAlert();
            }
        },function(){
            $rootScope.$broadcast("transimit failed wuwuwu");
        });
    }

    $scope.logout = function(){
        AuthService.logout();
    }

})
