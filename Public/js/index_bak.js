/**
 * 首页js
 * Created by Aous on 2017/8/19.
 */
var intervalSlide       = null ;   //幻灯片定时
var currentSlide        = null;    //当前幻灯片
var isMouseHover        = false;    //是否是鼠标触发幻灯片切换的
var slideImgTotal       = 0;       //幻灯片图片总数
var slideTime           = indexSlideTime;    //幻灯片切换间隔时间

var scrollWith          = 0;  //滚动偏移量
var scrollTotalWidth    = 0; //总滚动偏移量
var scrollLiWidth       = 0; //每次滚动偏移量
var intervalScroll      = null ;//滚动定时
var scrollTime          = indexScrollTime; //滚动栏切换间隔时间

if(slideTime =='' || slideTime == null){
    slideTime = 3000;
}else{
    slideTime *= 1000;
}
if(scrollTime =='' || scrollTime == null){
    scrollTime = 3000;
}else{
    scrollTime *= 1000;
}
log("slideTime:"+slideTime+" scrollTime:"+scrollTime);

$(function(){

    /**
     * begin 幻灯片
     */

    slideImgTotal = $(".slide-thumb img").length;
    if(slideImgTotal >1){
        startSlide();
        //鼠标划过 停止幻灯片
        $(".slide-thumb img").mouseover(function(){
            isMouseHover = true;
            slideActive($(this));
            clearInterval(intervalSlide);
            currentSlide = $(".slide-thumb img").index($(this))+1;
        });
        //说鼠标离开 重新开始幻灯片
        $(".slide-thumb-bg").mouseleave(function() {
            currentSlide++;
            startSlide();
        });
        //鼠标划过大图片
        $(".slide-box").hover(function(){
            clearInterval(intervalSlide);
        },function(){
            startSlide();
        })
    }


    /**
     * 图片滚动
     */
    var srollBoxObj       = $(".web-scroll-box")
    scrollLiWidth     = srollBoxObj.find("li").eq(0).width();
    scrollTotalWidth  = srollBoxObj.width() - $(".web-scroll-container").width();

    if(scrollTotalWidth > 0){
        scrollWith = scrollLiWidth;
    }
    if(scrollWith){
        intervalScroll = setInterval(startScroll,scrollTime);
    }

    $(".web-scroll-box li").hover(function(){
        //停止滚动
        clearInterval(intervalScroll);
    },function(){
        //重新开始滚动
        intervalScroll = setInterval(startScroll,scrollTime);
    });



    //网上报名
    $("#submit-apply").click(function(){
        if(!confirm("您确定提交报名信息么?")){
            return false;
        }
        alert("报名");
    });
});

/**
 * 首页友情链接
 * @param obj
 */
function selectLink(obj){
    var url = $(obj).val();
    if(url!=''){
        window.open(url);
    }
}

/**
 * 启动滚动图片
 */
function startScroll(){

    if(scrollWith > scrollTotalWidth){
        $(".web-scroll-container").scrollLeft(0); //从头来
        scrollWith = scrollLiWidth;
    }else{
        $(".web-scroll-container").scrollLeft(scrollWith);
        scrollWith += scrollLiWidth;
    }
}

/**
 * 启动幻灯片
 */
function startSlide(){
    intervalSlide = setInterval(runSlide,slideTime);

}

function runSlide(){
    if(currentSlide == null){
        //从第一张开始
        currentSlide = 1;
    }
    if(currentSlide>slideImgTotal){
        currentSlide = 1; //最大四张
    }
    slideActive($(".slide-thumb img").eq(currentSlide-1));
    currentSlide++;
}

/**
 * 选中幻灯片
 * @param obj 选择对象
 */
function slideActive(obj){
    var left = obj.offset().left
    var top = obj.offset().top;
    var src = obj.attr("src");
    var alt = mySubstring(obj.attr("alt"),25,true);
    var time = 700;
    var rand = (Math.round(Math.random()*10))%2;
    $(".slide-thumb-bg").css({left:left,top:top}).show();
    if(isMouseHover == true){
        time = 100;
        isMouseHover = false;
    }else{
        time = 500;
    }
    $(".slide-box a").attr("href",obj.attr("data-value"));
    if(rand == 0){
        $(".slide-box img").hide().attr("src",src).slideDown(time);
    }else{
        $(".slide-box img").hide().attr("src",src).fadeIn(time);
    }
    $(".slide-text-contet").text(alt);

}