angular.module('starter.controllers', [])

.controller('EventCtrl', function($scope,EventService,$ionicLoading,Format) {

  var DESC = "DESC";
  var ASC = "ASC";
  var loadNum = 5//上拉加载的个数
  var refreshNum = 5 //下拉刷新的个数

  $ionicLoading.show({
    template: '<i class = "ion-load-c"><br></i>加载中...'
  });

  EventService.getMainInfo().success(function(data) {
    $scope.eventlist = data.b;
    $scope.since_id = $scope.eventlist[$scope.eventlist.length-1].event_id;
  }).then(function() {
    $ionicLoading.hide();
  });


  $scope.loadMore = function() {
      EventService.getMainInfo(loadNum,$scope.since_id,DESC).success(function(data) {
      var eventArray = new Array();
      if ($scope.eventlist) {
        eventArray = $scope.eventlist;
      }

      for(var i = 0; i < data.b.length;  i++){
        eventArray.push(data.b[i]);
      }

      $scope.eventlist = eventArray;
      console.log($scope.eventlist);
      // $scope.since_id ＝ $scope.eventlist[$scope.eventlist.length-1].event_id;
      console.log( $scope.eventlist[$scope.eventlist.length-1].event_id);
      $scope.since_id = $scope.eventlist[$scope.eventlist.length-1].event_id;
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




