angular.module('RouteConfig', [])

.config(function($stateProvider, $urlRouterProvider) {
  // Ionic uses AngularUI Router which uses the concept of states
  // Learn more here: https://github.com/angular-ui/ui-router
  // Set up the various states which the app can be in.
  // Each state's controller can be found in controllers.js
  $stateProvider

  // setup an abstract state for the tabs directive
  .state('tab', {
    url: '/tab',
    abstract: true,
    templateUrl: 'templates/tabs.html'
  })

  //用户登录
  .state('login', {
    url: '/login',
    controller: 'LoginCtrl',
    templateUrl: 'templates/tab-login.html',
  })

  .state('tab.event', {
    url: '/event',
    views: {
      'tab-event': {
        templateUrl: 'templates/tab-event.html',
        controller: 'EventCtrl'
      }
    }
  })

  .state('event-detail', {
    url:'/event/:eventId',
    templateUrl:'templates/event-detail.html',
    //template: '<ion-view view-title="haha"><ion-content><h1>haha</h1></ion-content></ion-view>',
    controller:'EventDetailCtrl'
  })

  .state('tab.message', {
    data: {need_login: true},
    url: '/message',
    views: {
      'tab-message': {
        templateUrl: 'templates/tab-message.html',
        controller: 'MessageCtrl'
      }
    }
  })

  .state('message-detail', {
    data: {need_login: true},
    url: '/message/:messageId',
    templateUrl: 'templates/message-detail.html',
    controller: 'MessageDetailCtrl'
  })

  .state('contact', {
    data: {need_login: true},
    url: '/contact',
    templateUrl: 'templates/tab-contact.html',
    controller: 'ContactCtrl'
  })

  .state('contact-detail', {
    data: {need_login: true},
    url: '/contact/:contact',
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

  .state('friendcircle', {
    url: '/friendcircle',
    templateUrl: 'templates/tab-friendcircle.html',
    controller: 'FriendcircleCtrl',
  })

  // 聊天室 tab
  .state('chatroom', {
    url: '/chatroom',
    templateUrl: 'templates/tab-chatroom.html',
    controller: 'ChatroomCtrl'
  })

  .state('tab.account', {
    data: {need_login: true},
    url: '/account',
    views: {
      'tab-account': {
        templateUrl: 'templates/tab-account.html',
        controller: 'AccountCtrl'
      }
    }
  })

  .state('personalHomepage', {
    data: {need_login: true},
    url: '/personalHomepage',
    templateUrl: 'templates/personal_homepage.html',
    controller: 'PersonalHomepageCtrl'
  })

  .state('personalContactHomepage', {
    data: {need_login: true},
    url: '/personalContactHomepage/:contact',
    templateUrl: 'templates/personal_homepage.html',
    controller: 'PersonalContactHomepageCtrl'
  })

  .state('personalHomepage-detail', {
    data: {need_login: true},
    url: '/personalHomepage/:infoId',
    templateUrl: 'templates/personal_homepage_detail.html',
    controller: 'PersonalHomepageDetailCtrl'
  })

  // 设置
  .state('setting', {
    data: {need_login: true},
    url: '/setting',
    templateUrl: 'templates/setting.html',
    controller: 'SettingCtrl'
  })

  /*
   * if none of the above states are matched, use this as the fallback
   * http://stackoverflow.com/questions/25065699/why-does-angularjs-with-ui-router-keep-firing-the-statechangestart-event
   */
  $urlRouterProvider.otherwise(function($injector, $location) {
    var $state = $injector.get('$state');
    $state.go('tab.event');
  });
})
