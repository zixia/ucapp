angular.module('IdSearchFactroy', [])

.factory("IdSearch",function($http){
  var service = {};

  // var contenturl = UrlPath.getContactpath();

  service.getMainInfo = function(){
    return $http.get('data/idsearch.json');
    // return $http
    // .post(contenturl,{user_id:user_id});
  }

  service.getIdInfo = function(id){
  	// var idlist = {};
  	// $http.get('data/idsearch.json').success(function(data) {
   //      idlist.array = data;
   //      });

  	// return idlist.array;
  	// for(var i= 0; i<idlist.array.length;i++){
  	// 	if (id == idlist.array.id) {
  	// 		return idlist.array.content;
  	// 		break;
  	// 	}
  	// }
  }

  return service;
})