$(function(){
    //网上报名
    $("#submit-apply").click(function(){
        if(!confirm("您确定提交报名信息么?")){
            return false;
        }

    });
    $(".web-container").css('min-width',$(document).width());

    //搜索
    $("#search-btn").click(function(){
        searchKeyword();
    });
    $("#search-keyword").keydown(function (e) {
        if(e.which == 13){
            searchKeyword();
        }
    });

    /**
     * 处理搜索
     */
    function searchKeyword(){
        location.href = searchUrl+"?keyword="+$.trim($("#search-keyword").val());
    }

    $(".sys-login-tag").click(function(){
        var url = $(this).find("a").attr("href");
        location.href = url;
    });
});