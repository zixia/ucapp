angular.module('EventServiceFactory', [])

.factory("EventService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};


  var getEventPath = UrlPath.getEventPath();
  var getEventDetailPath = UrlPath.getEventDetailPath();

  service.getMainInfo = function(num,start_id,order){
    return $http.post(getEventPath,{num:num,start_id:start_id,order:order});
  };

  service.getDetailInfo = function(event_id){
  	// return $http.get('data/eventdetail.json');
  	return $http
    .post(getEventDetailPath,{event_id:event_id});
  };
  return service;
})