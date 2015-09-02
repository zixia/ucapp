angular.module('MessageServiceFactory', [])

.factory('MessageService', function($http,$ionicLoading,$q,$window,UrlPath) {

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  var getMessagePath = UrlPath.getMessagePath()
  var getMessListPath = UrlPath.getMessListPath()
  var getsendMessPath = UrlPath.getsendMessPath();

  service.getDetailInfo = function(contact_id,start,refresh_num) {
    return $http({
      url: getMessagePath,
      method: 'POST',
      data: JSON.stringify({tid:contact_id}),
      withCredentials: true,
    })
  }

  service.getMainInfo = function(user_id) {
    return $http({
      url:  getMessListPath,
      method: 'POST',
      data: JSON.stringify({ u_id: user_id }),        
      withCredentials: true,
    })
  }

  service.sendMessage = function(contact_id, content) {
    return $http({
      url:  getsendMessPath,
      method: 'POST',
      data: JSON.stringify({ user_id:contact_id, txt:content }),
      withCredentials: true,
    })
  }

  return service;
})
