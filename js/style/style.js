scrollBottomTest =function(){
     $("#contain").scroll(function(){
         var $this =$(this),
         viewH =$(this).height(),//可见高度
         contentH =$(this).get(0).scrollHeight,//内容高度
         scrollTop =$(this).scrollTop();//滚动高度
        //if(contentH - viewH - scrollTop <= 100) { //到达底部100px时,加载新内容
        if(scrollTop/(contentH -viewH)>=0.95){ //到达底部100px时,加载新内容
        // 这里加载数据..
        }
     });
}