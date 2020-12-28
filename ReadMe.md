# [17SALSA](http://17salsa.com/app/) UCHome App [![Circle CI](https://circleci.com/gh/AKAMobi/ucapp/tree/master.svg?style=svg)](https://circleci.com/gh/AKAMobi/ucapp/tree/master)

[![Stories in Ready](https://badge.waffle.io/AKAMobi/ucapp.png?label=ready&title=Ready)](https://waffle.io/AKAMobi/ucapp)
[![Circle CI](https://circleci.com/gh/AKAMobi/ucapp/tree/master.svg?style=svg)](https://circleci.com/gh/AKAMobi/ucapp/tree/master)
[![GitHub Issues](https://img.shields.io/github/issues/AKAMobi/ucapp.svg)](https://github.com/AKAMobi/ucapp/issues)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/AKAMobi/ucapp?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

[![Browser Support](https://ci.testling.com/AKAMobi/ucapp.png)](https://ci.testling.com/AKAMobi/ucapp)

* View UCApp on GitHub - https://github.com/AKAMobi/ucapp
* View 17salsa on [Android Market](https://play.google.com/store/apps/details?id=com.salsa17.home)
* View 17salsa on [Apple Store](https://itunes.apple.com/cn/app/17salsa/id1019231034)

See: <https://appadvice.com/app/17salsa/1019231034>

## Coding style
* JSCS: preset to google
* JSHint
* vim:
 * pathogen
  * syntastic vim-js-indent vim-sensible html5.vim
 * color-theme distinguished

Reference: [https://github.com/showdownjs/code-style](https://github.com/showdownjs/code-style)

## Semantic Versioning

Given a version number MAJOR.MINOR.PATCH, increment the:

* MAJOR version when you make incompatible API changes,
* MINOR version when you add functionality in a backwards-compatible manner, and
* PATCH version when you make backwards-compatible bug fixes.

Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.

Reference: [http://semver.org/](http://semver.org/)

## Init Project After Check Out
1. npm install -g ionic cordova gulp ios-deploy ios-sim
1. cd app
1. npm install
1. bower install
1. ionic resources
1. ionic browser add crosswalk
1. ionic config build
1. ionic platform add ios android
1. ionic hooks add
1. ionic add ionic-service-push
1. ionic build ios android
1. ionic serve --lab

## Debug
 * Inspector: [Guide to Remote Debugging on iOS & Android](http://developer.telerik.com/featured/a-concise-guide-to-remote-debugging-on-ios-android-and-windows-phone/)
 * Console Log: 
```bash
ionic run android --livereload --consolelogs --serverlogs
```
## REST API
```shell
curl -u token https://17salsa.com/api/v2/feed

### API related HTTP header
 * ERROR CODE
 * X-UCAPP-CLIENT-VERSION
 * X-UCAPP-SERVER-VERSION
 * X-UCAPP-MESSAGE

common params:
 * before/:date
 * after/:date
 * limit/:limit
  
/api/v2/auth
  POST
    /login
      {username: $username, password: $password}
  /logout
  
/api/v2/user
  GET
    /me - current user
    /:id - {name, nick, sex, avatar, location, ...}
    /:id/detail - {... ...}

/api/v2/event
  GET
    / - lastest event id list
    /:id - {...}
    /:id/detail - {... ...}

    /api/v2/message
    GET
    / {uid, date}
    /:uid - { {date,msg}, {}, ... }

    POST
    /:uid - {msg}

    /api/v2/feed
    / - [ {...} ]
    /api/v2/reply
    /api/v2/like
    /api/v2/contact

    /api/v2/batch
      POST
      [
        {
          "method": "POST",
          "path": "/1.1/classes/Post",
          "body": {
            xx: 'xx',
            xx: 'xx'
          }
        },
        {
          "method": "POST",
          "path": "/1.1/classes/Post",
          "body": {
            "content": "",
            "user": ""
          }
        }
      ]


