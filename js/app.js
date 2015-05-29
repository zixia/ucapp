// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('starter', [
  'ionic',
  // 'ui.router',
  'ngCordova',
  'ionic.service.core',
  'ionic.service.push',
  'ionic.service.deploy',
  'starter.controllers',
])

.config(['$ionicAppProvider', function($ionicAppProvider) {
  // Identify app
  $ionicAppProvider.identify({
    // The App ID (from apps.ionic.io) for the server
    app_id: '301dd65b',
    // The public API key all services will use for this app
    api_key: 'd76bc552414571ce7024ed7a642e2c08a77c2c357f4647d2',
    // The GCM project ID (project number) from your Google Developer Console (un-comment if used)
    //gcm_id: 'GCM_ID',
  });
}])

.config(function($ionicConfigProvider){
  // console.log(">>>>>>>");
  // console.log($ionicConfigProvider);
  // $ionicConfigProvider.backButton.previousTitleText(false).text('');
})

.factory("Format",function(){

  function formatcell (cell){
    if(cell == 1){
            return 1;
          }
          if(cell == 2){
            return 2;
          }
          else{
            return 3;
          }
  };

  return {
    formatcell:formatcell
  }

})

.factory("ContactService", function($http,$ionicLoading,$q,$window){

  var service = {};
  var user_id = $window.sessionStorage['user_id'];
  // service.getMainInfo = function(){
  //   return $http.get('data/contact.json');
  // }

  service.getMainInfo = function(){
    // return $http.get('data/contact.json');
    return $http
    .post('http://17salsa.com/app++/showcontact.php',{user_id:user_id});
  }

  return service;
})

.factory("MessageService", function($http,$ionicLoading,$q,$window){

  var service = {};
  var user_id = $window.sessionStorage['user_id'];

  service.getMainInfo = function(){
    // return $http.get('data/message.json');
    return $http
    .post('http://17salsa.com/app++/showmessage.php',{user_id:user_id});
  }

  return service;
})

.factory("PersonalHomepageService", function($http,$ionicLoading,$q,$window){

  var service = {};
  var userInfo = {};
  var user_id = $window.sessionStorage['user_id'];

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
    .post('http://17salsa.com/app++/showcontactInfo.php',{user_id:user_id});
  };
  

  service.getContentInfo = function(){
    return $http
    .post('http://17salsa.com/app++/showhomepage.php',{user_id:user_id});
  };

  return service;
})

.factory("AuthService",function($http,$location,$window){
//	var authService = {};
	function login (username,password){
		return $http
//		.post('http://17salsa.com/login.php',{username:"zixia"})
		// .post('http://127.0.0.1/17salsa_serve/login.php',{username:username,password:password})
    .post('http://17salsa.com/app++/login.php',{username:username,password:password})
		.then(function(res){
      if (res.data.ret == true) {
        $window.sessionStorage['sessionId'] = res.data.sid;
        //个人主页相关信息
        $window.sessionStorage['user_id'] = res.data.user_id;
        $window.sessionStorage['user_name'] = res.data.user_name;
        $window.sessionStorage['user_avatar'] = res.data.user_avatar;
        $window.sessionStorage['user_headpic'] = res.data.user_headpic;
        $window.sessionStorage['user_gender'] = res.data.user_gender;
        $window.sessionStorage['user_area'] = res.data.user_area;
        $window.sessionStorage['user_sign'] = res.data.user_sign;

        alert('登陆成功');
        $location.path('/tab/home');
      }
      else{
        console.log('login error');
        alert('用户名密码错误！');
      }
			return res.data;
		});
	};
	
	function isAuthenticated (){
    return $window.sessionStorage['user_id'];
    // return true;
  }
	// authService.isAuthenticated = function(){
	// 	return !!Session.userId;
	// };

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
	
.run(function($ionicPlatform) {
  $ionicPlatform.ready(function() {
    // Hide the accessory bar by default (remove this to show the accessory bar above the keyboard
    // for form inputs)
    if(window.cordova && window.cordova.plugins.Keyboard) {
      cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
    }
    if(window.StatusBar) {
      StatusBar.styleDefault();
    }
  });
})

.run(function($rootScope, $location, AuthService,$state) {

  $rootScope.$on("$stateChangeStart",function(event,toState,b,c,d,e){
    if(toState.name=='login')return;// 如果是进入登录界面则允许
    if (!AuthService.isAuthenticated()) {
      event.preventDefault();// 取消默认跳转行为
      $state.go("login");
      // $location.path("/tab/user");//uiroute和ngroute的区别 uiroute是angularjs的扩展
    };
  })
})

.config(function($stateProvider, $urlRouterProvider) {

  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider

  // setup an abstract state for the tabs directive
    .state('tab', {
    url: "/tab",
    abstract: true,
    templateUrl: "templates/tabs.html"
  })

  //用户登录
  .state('login', {
    url: '/login',
    controller: 'LoginCtrl',
    templateUrl: 'templates/tab-login.html',
  })

  .state('tab.activity', {
    url: '/activity',
    views: {
      'tab-activity': {
        templateUrl: 'templates/tab-activity.html',
        controller: 'ActivityCtrl'
      }
    }
  })

  .state('tab.message', {
    url: '/message',
    views: {
      'tab-message': {
        templateUrl: 'templates/tab-message.html',
        controller: 'MessageCtrl'
      }
    }
  })

  .state('message-detail', {
      url: '/message/:messageId',
          templateUrl: 'templates/message-detail.html',
          controller: 'MessageDetailCtrl'
  })

  .state('contact', {
    url: '/contact',
    // views: {
    //   'tab-contact': {
        templateUrl: 'templates/tab-contact.html',
        controller: 'ContactCtrl'
    //   }
    // }
  })

  .state('contact-detail', {
      url: '/contact/:contactId',
          templateUrl: 'templates/contact-detail.html',
          controller: 'ContactDetailCtrl'
  })

  .state('tab.discovery', {
    url: '/discovery',
    views: {
      'tab-discovery': {
        templateUrl: 'templates/tab-discovery.html',
        controller: 'DiscoveryCtrl'
      }
    }
  })

  // .state('tab.discovery-test', {
  //   url: '/discovery/11111111111111111',
  //   views: {
  //     'tab-discovery': {
  //       templateUrl: 'templates/test.html',
  //       controller: 'TestCtrl'
  //     }
  //   }
  // })
  
  .state('friendcircle', {
    url: '/friendcircle',
      // views{
      //   'tab.discovery-friendcircle':{
        templateUrl: 'templates/tab-friendcircle.html',
        controller: 'FriendcircleCtrl',
        // resolve:{
        //   auth:function($q,AuthService){
        //     var flag = AuthService.isAuthenticated();
        //     console.log(flag);
        //     console.log(AuthService);
        //     if (flag!='null') {
        //       console.log("ruiruiruiruir");
        //       return $q.when(flag);
        //     }
        //     else{
        //       console.log('exit');
        //       // $scope.$emit('authenticated','false');
        //       // console.log($q);
        //       // console.log($q.defer());
        //       return $q.reject({ authenticated: false });
        //     }

////        	auth:["$q","authenticationSvc",function($q,authenticationSvc){
////        		console.log(authenticationSvc);
////        		var userInfo = authenticationSvc.getUserInfo();
////        		console.log("authentication里面的userInfo:"+userInfo);
////        		if(userInfo){
////        			console.log('when');
////        			return $q.when(userInfo);
////        		}
////        		else{
////        			console.log('reject');
////        			console.log($q.reject({authenticated:false}));
////        			return $q.reject({authenticated:false});
////        		}
////        	}]
//        }
    //   }
    // }
    // }}
  })
  
  // 聊天室 tab
  .state('tab.chatroom', {
    url: '/chatroom',
    views: {
      'tab-chatroom': {
        templateUrl: 'templates/tab-chatroom.html',
        controller: 'ChatroomCtrl'
      }
    }
  })

  .state('tab.account', {
    url: '/account',
    views: {
      'tab-account': {
        templateUrl: 'templates/tab-account.html',
        controller: 'AccountCtrl'
      }
    }
  })

  .state('personalHomepage', {
      url: '/personalHomepage',
          templateUrl: 'templates/personal_homepage.html',
          controller: 'PersonalHomepageCtrl'
  })

  .state('personalContactHomepage', {
      url: '/personalContactHomepage/:contactId',
          templateUrl: 'templates/personal_homepage.html',
          controller: 'PersonalContactHomepageCtrl'
  })

  .state('personalHomepage-detail', {
      url: '/personalHomepage/:infoId',
          templateUrl: 'templates/personal_homepage_detail.html',
          controller: 'PersonalHomepageDetailCtrl'
  })


  // Ionic Analytics tab
  .state('tab.analytics', {
    url: '/analytics',
    views: {
      'tab-analytics': {
        templateUrl: 'templates/tab-analytics.html',
        controller: 'AnalyticsCtrl'
      }
    }
  });

  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/tab/message');

});
