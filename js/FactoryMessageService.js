angular.module('MessageServiceFactory', [])

.factory("MessageService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  var getMessagePath = UrlPath.getMessagePath();
  var getMessListPath = UrlPath.getMessListPath();

  service.getDetailInfo = function(contact_id,start,refresh_num){
    // return $http.get('data/message.json');
    return $http
    .post(getMessagePath,{c_id:contact_id,start:start,num:refresh_num});
  }

  service.getMainInfo =function(user_id){
  	return $http
    .post(getMessListPath,{u_id:user_id});
  }

  return service;
})