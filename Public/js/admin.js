/**
 * 后台js
 * Created by Aous on 2017/8/20.
 */

$(function(){

    removeMessage(); //移除操作提示信息

    //设置第一个input框获取焦点
    if($("input").length > 0){ 
        $("input").eq(0).focus();
    }
});




/**
 * 重新定义高度 left 和right
 */
function heightEdit(){
    var width = $(window).width();
    if(width < 1300){
        width = 1300;
    }
    $(".top").width(width);
    $(".main").width(width);
    $(".right").width(width-170-50);

    var rightHeight = $('.right').height();
    var leftHeight  = $('.left').height();
   
    if(rightHeight > leftHeight){
        $(".left").height(rightHeight-10);
    }
}
