// Ionic Starter App

// angular.module is a global place for creating, registering and retrieving Angular modules
// 'starter' is the name of this angular module example (also set in a <body> attribute
// in index.html)
// the 2nd parameter is an array of 'requires'
// 'starter.services' is found in services.js
// 'starter.controllers' is found in controllers.js
angular.module('starter', [
  'ionic',
  'ionic.service.core',
  'ionic.service.analytics',
  'ionic.service.push',
  'ngCordova',

  'ionic.service.deploy',

  //controllers
  'starter.controllers',
  'starter.messagecontrollers',
  'starter.contactcontrollers',
  'starter.friendcirclecontrollers',
  'starter.homepagecontrollers',
  'starter.accountcontrollers',
  'starter.publishcontrollers',
  'SystemCtrls',

  //factories
  'ContactServiceFactory',
  'FactoryPersonalHomepageService',
  'FactoryFormat',
  'FactoryAuthService',
  'MessageServiceFactory',
  'EventServiceFactory',
  'IdSearchFactroy',

  //route
  'RouteConfig',

  //globalpara
  'GlobalPath'


])

.config(['$httpProvider', function($httpProvider) {
  // [Error] Failed to load resource: Cannot use wildcard in Access-Control-Allow-Origin 
  // when credentials flag is true. (check, line 0)
  
  //$httpProvider.defaults.withCredentials = true;
}])

.run(function($rootScope, $state) {
  $rootScope.$on('$ionicView.beforeEnter', function() {
    $rootScope.hideTab = false
    if ($state.current.data) {
      $rootScope.hideTab = !!$state.current.data.hide_tab
    }
  });
})

.run(['$ionicPlatform', '$ionicAnalytics', function($ionicPlatform, $ionicAnalytics) {
  $ionicPlatform.ready(function() {

    // Hide the accessory bar by default remove this to show
    // the accessory bar above the keyboard
    // for form inputs
    if (window.cordova && window.cordova.plugins && window.cordova.plugins.Keyboard) {
      window.cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
    }
    if (window.StatusBar) {
      window.StatusBar.styleDefault();
    }

    // XXX without register analytics works?
    // $ionicAnalytics.register()
  });
}])

/*
.run(['$ionicHistory', function($ionicHistory) {
  // XXX 201508 by zixia
  // get back button work between inside tab and outside tab view switch
  // http://stackoverflow.com/questions/26980222/ionic-no-back-button-when-navigating-away-from-tab-view
  $ionicHistory.enabledBack = function(view) {
    var backView = $ionicHistory.viewHistory().backView;
    return backView != null;
  };
}])
*/

.config(['$ionicConfigProvider', function($ionicConfigProvider) {
  $ionicConfigProvider.navBar.alignTitle('center'); // center view title of ionic-view
  $ionicConfigProvider.tabs.position('bottom'); //Places them at the bottom for all OS
  $ionicConfigProvider.tabs.style('standard'); //Makes them all look the same across all OS
}])

.run(function($rootScope, $location, AuthService, $state, $timeout, $log) {
  $rootScope.$on('$ionicView.beforeEnter', function() {
    if ($state.current.data && $state.current.data.hide_tab) {
      $rootScope.hideTab = true;
    } else {
      $rootScope.hideTab = false;
    }
    $log.log('hideTab: ' + $rootScope.hideTab);
  });

  $rootScope.$on('$stateChangeError', console.log.bind(console));

  $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
    //$log.log('$stateChangeStart (toState:' + toState.name + ',fromState:' + fromState.name + ')' + fromParams)

    if (AuthService.isAuthenticated()) {
      return;
    }

    if (toState.name === 'login') {
      return;
    }
    if (!toState.data || !toState.data.need_login) {
      return;
    }

    /*
     * https://github.com/angular-ui/ui-router/issues/1158
     *
    if (toState.retryInProgress) {
      toState.retryInProgress = false;
      return;
    }
    */
    event.preventDefault();

    // Optionally set option.notify to false if you don't want
    // to retrigger another $stateChangeStart event
    $log.log('redirect to login');
    $state.go('login', {refer_state: fromState.name});
    //$state.go('login', undefined, {notify: false})
    //$state.go('login', toParams, {notify: false, location: false})
  });
});
