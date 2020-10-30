/**
 * Created by Aous on 2017/8/26.
 * 导航切换
 */
$(function(){
    /**
     * begin 导航菜单切换
     */
    //获取当前激活的菜单
    var activeLi = '';
    $(".web-nav-list li").each(function(){
        if($(this).hasClass('active')){
            activeLi = $(this);
            return ;
        }
    });
    if(activeLi == ''){
        activeLi = $(".web-nav-list li").eq(0);
    }
    var currentLi = '';
    $(".web-nav-list li").hover(function(){

        if($(this).attr("data-son-cnt") == 0){
            return true;
        }
        currentLi = $(this);
        $(".web-nav-son-list-ul").hide();
        activeLi.removeClass('active');

        var left = $(this).offset().left
        var top = $(this).offset().top;
        var sonUl = '.son-'+$(this).attr("data-id");


        $(".web-nav-son").css({top:top+35}).slideDown(100);
        $(".web-nav-son-list").css({marginLeft:left+22});
        $(sonUl).show();
    },function(){
        activeLi.addClass('active');
        $(".web-nav-son").hide();
    });

    $(".web-nav-son").hover(function(){
        activeLi.removeClass('active');
        if(currentLi !=''){
            currentLi.addClass('active');
        }
        $(this).show();
    },function(){
        if(currentLi !=''){
            currentLi.removeClass('active');
        }
        activeLi.addClass('active');
        $(this).hide();
    });
    //end 导航菜单切换
});