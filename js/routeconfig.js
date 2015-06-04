angular.module('RouteConfig', [])

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

  .state('tab.event', {
    url: '/event',
    views: {
      'tab-event': {
        templateUrl: 'templates/tab-event.html',
        controller: 'EventCtrl'
      }
    }
  })

  .state('activity-detail',{
    url:"/activity/:activityId",
    templateUrl:'templates/activity-detail.html',
    controller:"ActivityDetailCtrl"
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
  
  .state('friendcircle', {
    url: '/friendcircle',
      templateUrl: 'templates/tab-friendcircle.html',
      controller: 'FriendcircleCtrl',
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



  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/tab/message');

});