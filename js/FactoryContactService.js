angular.module('ContactServiceFactory', [])

.factory("ContactService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  var contenturl = UrlPath.getContactpath();

  service.getMainInfo = function(){
    // return $http.get('data/contact.json');
    return $http
    .post(contenturl,{user_id:user_id});
  }

  return service;
})
