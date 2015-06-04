angular.module('ActivityServiceFactory', [])

.factory("ActivityService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};

  // var getActivityPath = UrlPath.getActivityPath();

  service.getMainInfo = function(){
    return $http.get('data/activity.json');
  };

  service.getDetailInfo = function(){
  	return $http.get('data/activitydetail.json');
  	// return $http
   //  .post(getActivityPath,{user_id:user_id});
  };



  return service;
})