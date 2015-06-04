// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('starter', [
  'ionic',
  'ngCordova',
  'ionic.service.core',
  'ionic.service.push',
  'ionic.service.deploy',

  //controllers
  'starter.controllers',
  'starter.messagecontrollers',
  'starter.contactcontrollers',
  'starter.friendcirclecontrollers',
  'starter.homepagecontrollers',
  'starter.accountcontrollers',

  //factories
  'ContactServiceFactory',
  'FactoryPersonalHomepageService',
  'FactoryFormat',
  'FactoryAuthService',
  'MessageServiceFactory',
  'ActivityServiceFactory',
  
  //route
  'RouteConfig',

  //globalpara
  'GlobalPath',
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
  // console.log($ionicConfigProvider);
  // $ionicConfigProvider.backButton.previousTitleText(false).text('');
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
});


