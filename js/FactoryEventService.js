angular.module('EventServiceFactory', [])

.factory("EventService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};


  var getEventPath = UrlPath.getEventPath();
  var getEventDetailPath = UrlPath.getEventDetailPath();

  service.getMainInfo = function(){
    return $http.post(getEventPath);
  };

  service.getDetailInfo = function(event_id){
  	// return $http.get('data/eventdetail.json');
  	return $http
    .post(getEventDetailPath,{event_id:event_id});
  };



  return service;
})