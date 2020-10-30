/**
 * 图片上传
 * Created by Aous on 2017/8/25.
 */

/**
 * 图片上传
 * @param ids 绑定的id 可以多个
 * @param success 上传成功回调函数
 * @param error 上传失败回调函数
 */
function uplogImg(ids,uploadUrl){
    var ids = ids.split(',');

    setForm(uploadUrl);

    for(i=0;i<ids.length;i++){
        ids[i] = $("#"+ids[i]);
        ids[i].click(function(){
            $("#uploadFile").attr('name',$(this).attr('id')).click();
        });
    }
}

    $("body").on('change','#uploadFile',function(){

        var url     = $(this).val();
        var regImg  = /(jpg|png|bmp|jpeg|gif)$/i;
        if(!regImg.test(url)){
            alert("仅支持jpg、png、bmp、jpeg、gif图片格式");
            $(this).val('');
            return false;
        }
        $("#uploadForm").submit();
        $('#uploadFile').val('').removeAttr('name');
    });

/**
 * 设置隐藏表单和iframe
 *
 */
function setForm(url){
    var form = '<form action="'+url+'" method="post" enctype="multipart/form-data" target="uploadIframe" id="uploadForm">';
        form += '<input type="file" value="" id="uploadFile"/>';
        form += '</form>';
    var iframe = '<iframe  name="uploadIframe"></iframe>';
    var css = {display:'none'};
   $(form).css(css).appendTo("body");
    $(iframe).css(css).appendTo("body");

}
