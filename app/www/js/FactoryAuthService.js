angular.module('FactoryAuthService', [])

.factory('AuthService', function($http, $location, $window, UrlPath, $ionicHistory, $log, $ionicConfig, $timeout) {
  var loginpath = UrlPath.getLoginpath();

  function login(username, password) {
    return $http({
      url:  loginpath,
      method: 'POST',
      data: JSON.stringify({
        username: username,
        password: password
      }),
      withCredentials: true,
    })
    .then(function(res) {
      if (res.data.ret === true) { //个人主页相关信息
        $window.sessionStorage['user_id'] = res.data.user_id;
        $window.sessionStorage['user_name'] = res.data.user_name;
        $window.sessionStorage['user_avatar'] = res.data.user_avatar;
        $window.sessionStorage['user_headpic'] = res.data.user_headpic;
        $window.sessionStorage['user_gender'] = res.data.user_gender;
        $window.sessionStorage['user_area'] = res.data.user_area;
        $window.sessionStorage['user_sign'] = res.data.user_sign;
      } else {
        console.log('login error');
        // alert('用户名密码错误！');
      }
      return res.data;
    });
  }

  function isAuthenticated () {
    return $window.sessionStorage['user_id'];
  }

  function logout() {
    clean();

    console.log('logout successful');
  };

  return {
    login: login,
    logout:logout,
    isAuthenticated:isAuthenticated
  }

  function clean() {
    $timeout(function() {
      $ionicHistory.clearHistory();
      $ionicHistory.clearCache();      // destory all view caches
    });

    $ionicConfig.views.maxCache(0);
    $ionicConfig.views.maxCache(10);

    $window.sessionStorage.clear();  //清空所有内容，我还是不用了把 = =

    for (var i = 0; i < localStorage.length; i++) {
      var key = localStorage.key(i);
      $log.log('localStorage deleting ' + i + ' key:' + key);
      localStorage.removeItem(key);
    }

    $window.location.reload(true); // force reload to try to destory all view caches
  }
});

