angular.module('SystemCtrls', [])

.controller('SettingCtrl', function($scope, AuthService, $state, $log, $ionicPopup, storage) {
  $scope.logout = function() {
    AuthService.logout()
    $state.go('login')
  }

  $scope.clearStorage = function() {
    var confirm = $ionicPopup.confirm({
      title: '确认清楚17SALSA所有存储数据？',
      template: '这个操作将清空手机应用程序本地缓存。'
    })
    .then(function(res) {
      if (res) {
        storage.clearAll()
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

  $scope.upgrade = function() {
    var alert = $ionicPopup.alert({
      title: '已经是最新版本1.0',
      template: '如有任何问题建议，请发送邮件到： salsa@17salsa.com'
    })
  }

  $scope.back = function() {
    history.back()
  }
})
