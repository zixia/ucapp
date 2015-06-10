angular.module('MessageServiceFactory', [])

.factory("MessageService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  var getMessagePath = UrlPath.getMessagePath();

  service.getMainInfo = function(){
    // return $http.get('data/message.json');
    return $http
    .post(getMessagePath,{user_id:user_id});
  }

  return service;
})