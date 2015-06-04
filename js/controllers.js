angular.module('starter.controllers', [])

.controller('ActivityCtrl', function($scope,ActivityService,$ionicLoading) {

	$ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

	ActivityService.getMainInfo().success(function(data){
        $scope.activitylist = data;
        }).then(function(){
            $ionicLoading.hide();
    });
	
})

.controller('ActivityDetailCtrl',function($state,$scope,$stateParams,$ionicLoading,ActivityService){
	var num = $stateParams.activityId;

	$ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    ActivityService.getDetailInfo().success(function(data){
        $scope.activity = data;
        }).then(function(){
            $ionicLoading.hide();
    });

    $scope.goback = function(){
    	$state.go("activity");
    }
})

.controller('DiscoveryCtrl', function($scope,$state) {
    $scope.gofriendcircle = function(){
        $state.go("friendcircle");
    }

    $scope.gocontact = function(){
        $state.go("contact");
    }
})

.controller('ChatroomCtrl', function($scope) {
  // Nothing to see here.
})




