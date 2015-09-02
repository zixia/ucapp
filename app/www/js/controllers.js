angular.module('starter.controllers', [])

.controller('EventCtrl', function($scope,EventService,$ionicLoading,Format) {

  $ionicLoading.show({
    template: '<i class = "ion-load-c"><br></i>加载中...'
  });

  EventService.getMainInfo().success(function(data) {
    $scope.eventlist = data.b.reverse();
    $scope.since_id = $scope.eventlist[$scope.eventlist.length-1].event_id;
  }).then(function() {
    $ionicLoading.hide();
  });


  $scope.loadMore = function() {
    EventService.getMainInfo($scope.since_id,5).success(function(data) {
      var testarray = new Array();
      if ($scope.eventlist) {
        testarray = $scope.eventlist;
      };
      
      for(var i = data.b.length; i>0; i--){
        testarray.push(data.b[i-1]);
      }

      $scope.eventlist = testarray;
      console.log($scope.eventlist);
      
    }).then(function() {
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });      
  }

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




