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

        localStorage.setItem('user_info', JSON.stringify($window.sessionStorage));
        // JSON.parse(localStorage.getItem('user_info'));
        // console.log(obj);

      } else {
        console.log('login error');
        // alert('用户名密码错误！');
      }
      return res.data;
    });
  }

  function isAuthenticated () {
    if(JSON.parse(localStorage.getItem('user_info'))){
      $window.sessionStorage = JSON.parse(localStorage.getItem('user_info'));
    }

    return $window.sessionStorage['user_id'];
  }

  function logout() {
    clean();

    console.log('logout successful');
  };

  function getAuthedUser() {
    var userId = $window.sessionStorage.user_id
    $log.log('getAuthedUser userId:' + userId)

    if (parseInt(userId) > 0) {
      $log.log('getAuthedUser succ. user_id: ' + userId)
      return $window.sessionStorage
    }

    $log.log('getAuthedUser not logined.')
    return null
  }

  return {
    login: login
    , logout: logout
    , isAuthenticated: isAuthenticated
    , getAuthedUser: getAuthedUser
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

