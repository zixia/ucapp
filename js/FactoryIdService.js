angular.module('IdSearchFactroy', [])

.factory("IdSearch",function($http){
  var service = {};

  // var contenturl = UrlPath.getContactpath();

  service.getMainInfo = function(){
    return $http.get('data/idsearch.json');

    // return $http
    // .post(contenturl,{user_id:user_id});
  }

  service.getIdUsername = function(idlist,fulllist){
  	var newlist = {};
  	for(var j= 0 ; j<idlist.length;j++){
        for(var i= 0; i<fulllist.length;i++){
            if (idlist[j] == fulllist[i].id) {
                newlist[j] = fulllist[i].content.username;
                break;
            }
        }
    }
    return newlist;
  }

  service.getIdUsernameReply = function(idlist,fulllist){
  	console.log(idlist.length);
  	console.log(fulllist.length);
  	for(var j= 0 ; j<idlist.length;j++){
        for(var i= 0; i<fulllist.length;i++){
            if (idlist[j][0] == fulllist[i].id) {
                idlist[j][0] = fulllist[i].content.username;
                break;
            }
        }
    }
    return idlist;
  }

  return service;
})