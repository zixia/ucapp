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



.controller('LoginCtrl',function($scope,$rootScope,AuthService,$ionicPopup){
    $scope.checklogin = false;
    $scope.err_txt = '';
    $scope.login = function(username,password){
         AuthService.login(username,password)
        .then(function(res){
            if (res.ret === true) {
                //console.log("resresres");
                console.log(res);
                $rootScope.$broadcast(res);
            }
            else{
                $scope.err_txt = '用户名密码错误'

                if (res.txt) 
                    $scope.err_txt = res.txt

                $scope.checklogin = true;


                $rootScope.$broadcast("login failed");
                // showAlert();
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
