angular.module('starter.controllers', [])

.controller('EventCtrl', function($scope,EventService,$ionicLoading,Format) {

  $ionicLoading.show({
    template: '<i class = "ion-load-c"><br></i>Loading...'
  });

  EventService.getMainInfo().success(function(data) {
    $scope.eventlist = data;
  }).then(function() {
    $ionicLoading.hide();
  });

  $scope.getstandardtime = function(ts) {
    var timearray = Format.formattimefriendcircle(ts);
    return timearray.timestandard;
  };

})

.controller('EventDetailCtrl', function($state, $scope, $stateParams, $ionicHistory,$ionicLoading, EventService, Format) {

    $scope.$on('$ionicView.enter',function(){
    var history = $ionicHistory.viewHistory();
    $scope.data = "";
    $scope.history = function(){
      console.log(history); 
      $scope.data = history;
    };
  });

  var num = $stateParams.eventId;

  $scope.getstandardtime = function(ts) {
    var timearray = Format.formattimefriendcircle(ts);
    return timearray.timestandard;
  };

  $ionicLoading.show({
    template: '<i class = "ion-load-c"><br></i>Loading...'
  });

  EventService.getDetailInfo(num).success(function(data) {
    $scope.event = data.b;
  }).then(function() {
    $ionicLoading.hide();
  });

})

.controller('DiscoveryCtrl', function($scope, $state) {
  $scope.gofriendcircle = function() {
    $state.go('tab.friendcircle');
  };

  $scope.gocontact = function() {
    $state.go("tab.contact");
  };

  $scope.gochat = function(){
    $state.go("chatroom");
  }
})

.controller('ChatroomCtrl', function($scope,$state) {
  $scope.godiscover = function(){
    $state.go("tab.discovery");
  }
})




