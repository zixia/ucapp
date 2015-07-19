angular.module('starter.accountcontrollers', [])

.controller('AccountCtrl', function($scope,AuthService,$state,$window) {
    $scope.user = $window.sessionStorage;

    $scope.logout = function(){
        AuthService.logout();
        $state.go("login");
    }
})



.controller('LoginCtrl',function($scope,$rootScope,AuthService){
    $scope.login = function(username,password){
         AuthService.login(username,password)
        .then(function(res){
            if (res.ret === true) {
                //console.log("resresres");
                console.log(res);
                $rootScope.$broadcast(res);
            }
            else{
                $rootScope.$broadcast("login failed");
            }
            
        },function(){
            $rootScope.$broadcast("transimit failed wuwuwu");
        });
    }

    $scope.logout = function(){
        AuthService.logout();
    }

})
