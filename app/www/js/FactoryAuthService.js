angular.module('FactoryAuthService', [])

.factory("AuthService",function($http,$location,$window,UrlPath,$state) {
    var loginpath = UrlPath.getLoginpath();

    function login (username,password){
        return $http
        // .post('http://127.0.0.1/17salsa_serve/login.php',{username:username,password:password})
        .post(loginpath,{username:username,password:password})
        .then(function(res){
            if (res.data.ret == true) 
                { //个人主页相关信息
                    $window.sessionStorage['user_id'] = res.data.user_id;
                    $window.sessionStorage['user_name'] = res.data.user_name;
                    $window.sessionStorage['user_avatar'] = res.data.user_avatar;
                    $window.sessionStorage['user_headpic'] = res.data.user_headpic;
                    $window.sessionStorage['user_gender'] = res.data.user_gender;
                    $window.sessionStorage['user_area'] = res.data.user_area;
                    $window.sessionStorage['user_sign'] = res.data.user_sign;

                    $state.go('tab.event');
                } else {
                    console.log('login error');
                    // alert('用户名密码错误！');
                }
                return res.data;
        });
    };

    function isAuthenticated (){
        return $window.sessionStorage['user_id'];
    }

    function logout(){
        $window.sessionStorage.clear();//清空所有内容，我还是不用了把 = =
        // $window.sessionStorage['sessionId'] = null;
        // $window.sessionStorage['userId'] = null;
        // $window.sessionStorage['userName'] = null;
        // $window.sessionStorage['ret'] = null;
        console.log('logout successful');
    }

    return {
        login: login,
        logout:logout,
        isAuthenticated:isAuthenticated
    }
})

