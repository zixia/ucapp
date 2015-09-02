angular.module('FactoryPersonalHomepageService', [])

.factory("PersonalHomepageService", function($http,$ionicLoading,$q,$window,UrlPath) {

  var service = {};
  var userInfo = {};
  var user_id = $window.sessionStorage['user_id'];

  var getContactUserPath = UrlPath.getContactUserPath();
  var getContentPath = UrlPath.getContentPath();
  var getSendlikePath = UrlPath.getSendlikePath();
  var getSendRemarkPath = UrlPath.getSendRemarkPath();

  service.getUserInfo = function(){
    userInfo.user_id = $window.sessionStorage["user_id"];
    userInfo.user_name = $window.sessionStorage["user_name"];
    userInfo.user_headpic = $window.sessionStorage["user_headpic"];
    userInfo.user_avatar = $window.sessionStorage["user_avatar"];
    userInfo.user_sign = $window.sessionStorage["user_sign"];
    return userInfo;
  };

  service.getContactUserInfo = function(user_id){
    return $http({
      url: getContactUserPath,
      method: 'POST',
      data: JSON.stringify({user_id: user_id}),
      withCredentials: true
    })
  }

  service.getContentInfo = function(contact_id) {
    return $http({
      url: getContentPath,
      method: 'POST',
      data: JSON.stringify({user_id: contact_id}),
      withCredentials: true
    })
  }

  service.sendlike = function(item_id) {
    return $http({
      url: getSendlikePath,
      method: 'POST',
      data: JSON.stringify({item_id:item_id}),
      withCredentials: true
    })
  }

  service.sendremark = function(contact_id, item_id, content) {
    return $http({
      url: getSendRemarkPath,
      method: 'POST',
      data: JSON.stringify({
        contact_id: contact_id,
        item_id: item_id,
        content: content
      }),
      withCredentials: true
    })
  }

  return service;
})
