angular.module('MessageServiceFactory', [])

.factory('MessageService', function($http,$ionicLoading,$q,$window,UrlPath) {

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  var getMessagePath = UrlPath.getMessagePath();
  var getMessListPath = UrlPath.getMessListPath();
  var getsendMessPath = UrlPath.getsendMessPath();

  service.getDetailInfo = function(contact_id,start,refresh_num) {
    // return $http.get('data/message.json');
    // return $http
    // .post(getMessagePath,{c_id:contact_id,start:start,num:refresh_num});
    return $http
    .post(getMessagePath, {tid:contact_id});
  }

  service.getMainInfo = function(user_id) {
    return $http
    .post(getMessListPath, {u_id: user_id});
  }

  service.sendMessage = function(contact_id, content) {
    return $http
    .post(getsendMessPath, {user_id:contact_id, txt:content});
  }

  return service;
})
