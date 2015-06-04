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

  return {
    formatcell:formatcell
  }

})
