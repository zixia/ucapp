angular.module('IdSearchFactroy', [])
.factory('IdSearch', function($http, UrlPath, $q) {
  var APIURL = UrlPath.getIdtransferurlPath()

  function getMainInfo(userIds) {
    console.log('param userIds: ' + userIds)
    var deferred    = $q.defer()
    var promise     = deferred.promise

    promise.success = function(fn) {
      promise.then(fn);
      return promise;
    }
    promise.error = function(fn) {
      promise.then(null, fn);
      return promise;
    }

    var missUserIds     = Array()
    var missUserObjs    = {}

    var hitUserObjs     = {}

    for (var id in userIds) {
      var userId = userIds[id];
      var obj = JSON.parse(localStorage.getItem('userId_' + userId))
      if (obj) {
        hitUserObjs[userId] = obj
      } else {
        missUserIds.push(userId)
      }
    }

    console.log('userIds ' + ' HIT(' + Object.keys(hitUserObjs).length +
      ')+MISS(' + missUserIds.length + ')/ALL(' + userIds.length + ')')

    if (missUserIds.length > 0) {
      $http
      .post(APIURL, {idlist:missUserIds})
      .success(function(data) {
        missUserObjs    = data.b
        for (var id in data.b) {
          localStorage.setItem('userId_' + id, JSON.stringify(data.b[id]))
        }

        console.log('userId_' + id + ' saved')

        data.b = angular.extend({}, hitUserObjs, data.b)
        deferred.resolve(data)

        return promise
      })
      return promise
    } else {
      var data = {}
      data.h = {'ret':0}
      data.b = hitUserObjs
      deferred.resolve(data)
      return promise
    }
  }

  function getIdUsername(idlist, fulllist) {
    var newlist = {}
    for (var i = 0; i < idlist.length; i++) {
      newlist[i] = fulllist[idlist[i]].username
    }
    return newlist
  }

  function getIdUsernameReply(idlist, fulllist) {
    for (var i = 0; i < idlist.length; i++) {
      idlist[i][0] = fulllist[idlist[i][0]].username
    }
    return idlist
  }

  return {
    getIdUsername: getIdUsername,
    getIdUsernameReply: getIdUsernameReply,
    getMainInfo: getMainInfo
  }
})
