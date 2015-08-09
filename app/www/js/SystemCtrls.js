angular.module('SystemCtrls', [])

.controller('SettingCtrl', function($scope, AuthService, $state, $log, $ionicPopup, $ionicUser, $ionicPush, $ionicDeploy, $ionicLoading, $http) {
  $scope.logout = function() {
    AuthService.logout()
    $state.go('login')
  }

  $scope.downloadProgress     = 0
  $scope.showDownloadProgress = false

  $scope.clearStorage = function() {
    var confirm = $ionicPopup.confirm({
      title: '确认清楚17SALSA所有存储数据？',
      template: '这个操作将清空手机应用程序本地缓存。'
    })
    .then(function(res) {
      if (res) {
        //storage.clearAll()
        $ionicPopup.alert({
          title: '存储空间清理完毕',
          template: '本程序第一次加载数据会较慢，请耐心等待。'
        })
      }
    })
  }

  $scope.about = function() {
    var alert = $ionicPopup.alert({
      title: '17SALSA v1.0',
      template: 'Credit:<br />前端：芮芮<br />后端：阿布<br />保留所有权利，17salsa.com 2015'
    })
  }

  $scope.doUpdate = function() {

    $scope.showDownloadProgress = true
    $ionicLoading.show({
      template: '升级中...请稍候...'
    })
    console.log('You are sure');

    $ionicDeploy
    .update()
    .then(function(res) {
      console.log('Ionic Deploy: Update Success! ', res);
      $ionicLoading.hide()
      $scope.showDownloadProgress = false

      $ionicPopup.alert({
        title: '升级已完成！',
        template: '恭喜你升级到了最新版本。'
      });
    }, function(err) {
      console.log('Ionic Deploy: Update error! ', err);
      $ionicLoading.hide()
      $ionicPopup.alert({
        title: '升级失败...',
        template: '可能是网络不稳定，请切换网络环境后重试。'
      })
    }, function(prog) {
      $ionicLoading.hide()
      console.log('Ionic Deploy: Progress... ', prog)
      $scope.downloadProgress = Math.floor((prog + 3) * 96 / 100)
    })
    .finally(function() {
      $scope.showDownloadProgress = false
      $scope.downloadProgress = 0
      $ionicLoading.hide()
    })

    // Check Ionic Deploy for new code
    $scope.upgrade = function() {
      $ionicLoading.show({
        template: '检查更新中...请稍候...'
      })
      console.log('Ionic Deploy: Checking for updates')

      // "dev" is the channel tag for the Dev channel.
      var channel = 'dev'
      $ionicDeploy.setChannel(channel)

      $ionicDeploy
      .check()
      .then(function(hasUpdate) {
        //var info = $ionicDeploy.info()
        console.log('Ionic Deploy: ' + channel + ' Update available: ' + hasUpdate) // + ' ' + info);

        $ionicDeploy.info().then(function(deployInfo) {
          // deployInfo will be a JSON object that contains
          // information relating to the latest update deployed on the device
          $http.post('http://zixia.net/~zixia/git/ionic-web-hook.php', deployInfo)
        }, function() {}, function() {})

        if (hasUpdate) {
          $ionicPopup.confirm({
            title: '发现新版本',
            template: '点击确认键后，系统将升级到最新版本。'
          }).then(function(res) {
            if ( res ) {
              $scope.doUpdate()
            }
          })
        } else {
          var alert = $ionicPopup.alert({
            title: '已经是最新版本',
            template: '如有任何问题建议，请发送邮件到： salsa@17salsa.com'
          })
        }
      }, function(err) {
        console.error('Ionic Deploy: Unable to check for updates', err);
      })
      .finally(function() {
        $ionicLoading.hide()
      })
    }

    // Registers a device for push notifications and stores its token
    $scope.pushRegister = function() {
      console.log('Ionic Push: Registering user');

      // Register with the Ionic Push service.  All parameters are optional.
      $ionicPush.register({
        canShowAlert: true, //Can pushes show an alert on your screen?
        canSetBadge: true, //Can pushes update app icon badges?
        canPlaySound: true, //Can notifications play a sound?
        canRunActionsOnWake: true, //Can run actions outside the app,
        onNotification: function(notification) {
          // Handle new push notifications here
          // console.log(notification);
          return true;
        }
      })
    }

    $scope.identifyUser = function() {
      console.log('Ionic User: Identifying with Ionic User service');

      var user = $ionicUser.get();
      if (!user.user_id) {
        // Set your user_id here, or generate a random one.
        user.user_id = $ionicUser.generateGUID();
      }

      // Add some metadata to your user object.
      angular.extend(user, {
        name: 'Ionitron',
        bio: 'I come from planet Ion'
      })

      // Identify your user with the Ionic User Service
      $ionicUser.identify(user).then(function() {
        $scope.identified = true;
        alert('Identified user ' + user.name + '\n ID ' + user.user_id);
      })
    }
  }
})
