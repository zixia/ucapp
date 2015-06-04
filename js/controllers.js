angular.module('starter.controllers', [])

.controller('EventCtrl', function($scope,EventService,$ionicLoading) {

    // var unixtime=1433420932;
    // var unixTimestamp = new Date(unixtime* 1000); 
    // commonTime = unixTimestamp.toLocaleString();

    // console.log("commonTime:"+commonTime);

    // var month = unixTimestamp.getFullYear();
    // console.log("month:"+month);

	$ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

	EventService.getMainInfo().success(function(data){
        $scope.eventlist = data;
        }).then(function(){
            $ionicLoading.hide();
    });
	
})

.controller('EventDetailCtrl',function($state,$scope,$stateParams,$ionicLoading,EventService){
	var num = $stateParams.eventId;

	$ionicLoading.show({
        template:'<i class = "ion-load-c"><br></i>Loading...'
    });

    EventService.getDetailInfo(num).success(function(data){
        $scope.event = data;
        }).then(function(){
            $ionicLoading.hide();
    });

    $scope.goback = function(){
    	$state.go("tab.event");
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




