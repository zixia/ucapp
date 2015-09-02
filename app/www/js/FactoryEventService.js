angular.module('EventServiceFactory', [])

.factory("EventService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};


  var getEventPath = UrlPath.getEventPath();
  var getEventDetailPath = UrlPath.getEventDetailPath();

  service.getMainInfo = function(since_id,num){
    return $http.post(getEventPath,{since_id:since_id,num:num});
  };

  service.getDetailInfo = function(event_id){
  	// return $http.get('data/eventdetail.json');
  	return $http
    .post(getEventDetailPath,{event_id:event_id});
  };
  return service;
})