angular.module('starter.publishcontrollers', [])

.controller('PublishtxtCtrl', function($scope, $state,$timeout,$window,PersonalHomepageService) {
    $timeout(function() {
      document.querySelector('#inputContent').focus();
    });

    var user_id = $window.sessionStorage['user_id'];

    $scope.publish = {
      content: ""
    };

    $scope.sendtxt= function(){
      var publishcontent = $scope.publish.content;
      PersonalHomepageService.publishtxt(user_id,publishcontent).success(function(data) {
        if (data.h.ret === 0) {
          alert("success!");
        } else {
          alert('点赞失败' + data.h.r);
        }
      })
    }



})