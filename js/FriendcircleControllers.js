angular.module('starter.friendcirclecontrollers', [])

.controller('FriendcircleCtrl', function($scope,$http,$ionicPopup,Format,$ionicLoading,$state,$rootScope,$window,PersonalHomepageService,IdSearch) {       
        $scope.clickarray = new Array();
        $scope.friend_id = null;
        $scope.inputshow = false;

        $scope.userbasic = $window.sessionStorage;

        $scope.gomypage = function(){
            $state.go("personalHomepage");
        }

        $scope.godiscover = function(){
            $state.go("tab.discovery");
        }

        $scope.clearclick = function(){
            $scope.clickarray = new Array();
            $scope.inputshow = false;
        }

        $scope.clickfun = function(num){
            var user = "aaaaaaa";

            if($scope.infos[num].reply_heart.indexOf(user)>-1){
                $scope.heart_tag =  "取消";
            }
            else{
                $scope.heart_tag =  "点赞";
            }   

            $scope.clickarray[num] = !$scope.clickarray[num];
            $scope.serial_num = num;

            
        }

        $scope.searchclick = function(num){
            return $scope.clickarray[num];
        }

        $scope.remark = function(id){
            $scope.friend_id = id;
            $scope.inputshow = true;
            console.log("id:"+ id);
        }

        //发送评论 和zixia调
        $scope.sendremark = function(){
            $remark_content = $scope.inputContent;
            $remark_json = {"name":"newremark","content":$remark_content};
            var serial = $scope.serial_num;//整个数据流中的第几个数据
            $scope.infos[serial].reply.push($remark_json);
            // return $http
            // // .post('http://17salsa.com/login.php',{username:"zixia"})
            // .post('http://127.0.0.1/test.php',{
            //     content:content,
            //     friend_id:$scope.friend_id
            // }).then(function(res){
            //   if (res.data.ret == true) {
                // $scope.infos[serial].reply.push($remark_json);

            //   }
            //   else{
            //     console.log('aaaaaaa');
            //     alert('评论失败');
            //   }
            // return res.data;
            // };
        }

        //点赞 和zixia调
        $scope.sendheart = function(){
            var user = 'aaaaaaa';
            var serial = $scope.serial_num;//整个数据流中的第几个数据
            if($scope.infos[serial].reply_heart.indexOf(user)>-1){
                var reply_heart_index = $scope.infos[serial].reply_heart.indexOf(user);
                $scope.infos[serial].reply_heart.splice(reply_heart_index, 1);
            }
            else{
                $scope.infos[serial].reply_heart.push(user);
            }   
        }

         $ionicLoading.show({
            template:'<i class = "ion-load-c"><br></i>Loading...'
         });
         // $http.get('data/friendcircle.json').success(function(data) {
         // $http.get('http://17salsa.com/home/s.php?rewrite=home-view-all').success(function(data) {
         PersonalHomepageService.getContentInfo().success(function(data) {
            $scope.infos = data.b;



            console.log($scope.infos.length);
            for (var j = 0; j < $scope.infos.length; j++) {       
                
                (function(jj){
                    IdSearch.getMainInfo($scope.infos[jj].like).success(function(data) {
                    var fullarray = data.b;
                    $scope.infos[jj].likelist = fullarray;
                });

                })(j);

                // (function(aa){
                //     var idreplylist = new Array();
                //     for(var b =0; b<$scope.infos[aa].reply.length;b++){
                //         idreplylist[b] = $scope.infos[aa].reply[b][0];
                //     }
                //     IdSearch.getMainInfo(idreplylist).success(function(data) {
                //         var fullarray = data.b;
                //         $scope.infos[aa].replylist = fullarray;                       
                //     });
                // })(j);             
            }
        })
          
        .then(function(){
            $ionicLoading.hide();
        });

        $scope.getstandardtime = function(ts){
            var timearray = Format.formattimefriendcircle(ts);
            return timearray.timestandard;
        }
        
        //格式化类,根据收到的图片展示不同的样式
        $scope.formatcell = function(cell){
            return Format.formatcell(cell);
        }

        $scope.refresh = function(){
            $http.get('http://17salsa.com/home/s.php?rewrite=home-view-all').success(function(data) {
              $scope.infos = data;
            }).then(function(){
                $scope.$broadcast('scroll.refreshComplete');
            });
        }
})