angular.module('FactoryFormat', [])

.factory("Format",function(){

  function formatcell (cell){
    if(cell == 1){
            return 1;
          }
          if(cell == 2){
            return 2;
          }
          else{
            return 3;
          }
  };

  function formattimestamp (time){
    // var unixtime=1433420932;
    var unixTimestamp = new Date(time* 1000); 
    commonTime = unixTimestamp.toLocaleString();
    return commonTime;
  };

  function formattimefriendcircle(timetmp){
    timearray = {};
    var time = new Date(timetmp* 1000); 
    timearray.month = time.getMonth()+1;
    timearray.date = time.getDate();
    Y = time.getFullYear()+"年";
    M = time.getMonth()+1+"月";
    D = time.getDate()+"日";
    h = time.getHours()+":";
    m = time.getMinutes();
    timearray.times = time.toLocaleString();
    timearray.timestandard = Y+M+D+h+m;
    return timearray;
  };

  function formatmonth(num){
    switch(num)
      {
      case 1:
        return "一";
        break;
      case 2:
        return "二";
        break;
      case 3:
        return "三";
        break;
      case 4:
        return "四";
        break;
      case 5:
        return "五";
        break;
      case 6:
        return "六";
        break;
      case 7:
        return "七";
        break;
      case 8:
        return "八";
        break;
      case 9:
        return "九";
        break;
      case 10:
        return "十";
        break;
      case 11:
        return "十一";
        break;
      case 12:
        return "十二";
        break;
      default:
        return "error";
      }
  }

  return {
    formatcell:formatcell,
    formattimestamp:formattimestamp,
    formattimefriendcircle:formattimefriendcircle,
    formatmonth:formatmonth
  }

})
