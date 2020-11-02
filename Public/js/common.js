/**
 * 公共js
 * Created by Aous on 2017/8/19.
 */

/**
 * 数据打印到控制台
 * @param data
 */
function log(data){
    console.log(data);
}

/**
 * 移除操作提示信息
 */
function removeMessage(){
    var obj = $(".message-info");
    if(obj.length >0){
        obj.delay(2500).fadeOut(500);
    }
}

/**
 * 字符串截断输出 超过maxLength长度用...表示
 * @param str 字符串
 * @param maxLength 最大长度
 */

function mySubstring(str,maxLength,isReturn){
    if(maxLength == undefined || maxLength==null ||maxLength==''){
        maxLength = 20;
    }
    if(str==''){
        return false;
    }
    var len     = str.length;
    var newStr  = str;
    if(len > maxLength){
        newStr = str.substring(0,maxLength);
    }
    if(isReturn==undefined){
        document.write(newStr);
    }else{
        return  newStr;
    }
}
/**
 * 根据id对比两个控件值是否一致
 * @param id1
 * @param id2
 */
function compareById(id1,id2){
    return $("#"+id1).val() == $("#"+id2).val();
}

/**
 * 根据id判断控件值是否为空
 * @param id
 * @param boolean false 为空，不为空返回jquey对象
 */
function isEmpty(id){
    var obj = $("#"+id);
    if(obj.val() == ''){
        return false;
    }
    return obj;
}

/**
 * 正则校验 从id的控件中获取值
 * @param reg
 * @param id
 */
function regById(reg,id){
    return reg.test($("#"+id).val());
}