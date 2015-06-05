angular.module('IdSearchFactroy', [])

.factory("IdSearch",function($http,UrlPath){
  var service = {};

  // var contenturl = UrlPath.getContactpath();

  service.getMainInfo = function(idlist){
    // return $http.get('data/idsearch.json');
    var idtransferurl = UrlPath.getIdtransferurlPath();
    return $http
    .post(idtransferurl,{idlist:idlist});
  }

  service.getIdUsername = function(idlist,fulllist){
  	var newlist = {};
  	for(var i = 0; i<idlist.length;i++){
  		newlist[i] = fulllist[idlist[i]].username;
  	}
    return newlist;
  }

  service.getIdUsernameReply = function(idlist,fulllist){
  	for(var i =0; i<idlist.length;i++){
  		idlist[i][0] = fulllist[idlist[i][0]].username;
  	}
    return idlist;
  }

  return service;
})