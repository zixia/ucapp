angular.module('FactoryPersonalHomepageService', [])

.factory("PersonalHomepageService", function($http,$ionicLoading,$q,$window,UrlPath){

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
    return $http
    .post(getContactUserPath,{user_id:user_id});
  };
  

  service.getContentInfo = function(contact_id){
    return $http
    .post(getContentPath,{user_id:contact_id});
  };

  service.sendlike = function(contact_id,item_id){
    return $http
    .post(getSendlikePath,{contact_id:contact_id,item_id:item_id});
  }

  service.sendremark = function(contact_id,item_id,content){
    return $http
    .post(getSendRemarkPath,{contact_id:contact_id,item_id:item_id,content:content});
  }

  return service;
})
