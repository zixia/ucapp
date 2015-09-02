angular.module('ContactServiceFactory', [])

.factory("ContactService", function($http,$ionicLoading,$q,$window,UrlPath){

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  var contenturl = UrlPath.getContactpath();

  service.getMainInfo = function() {
    return $http({
      url: contenturl,
      method: 'POST',
      data: JSON.stringify({ user_id: user_id }),
      withCredentials: true,
    })
  }

  return service;
})
