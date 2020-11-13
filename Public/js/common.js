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
 * 获取字符串 出现的字母和数字数量
 * @param string
 */
function getStringNumberCount(string){

    var charCount = 0; //英文字符数量
    for(var i=0;i<string.length;i++) {
        //对每一位字符串进行判断，如果Unicode编码在0-127，计数器+1；否则+2
        if (string.charCodeAt(i) < 128 && string.charCodeAt(i) >= 0) {
            charCount++; //一个英文在Unicode表中站一个字符位
        }
    }
    return charCount;
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

    var newStr  = str;
    if(!isIE()){
        newStr = str.substring(0,maxLength);
        var num = getStringNumberCount(newStr);

        if(num>=2){ //英文和数字 字符占用字节比中文短
            var addition =  Math.floor(num/2);
            newStr = str.substring(0,maxLength+addition);
        }
    }else{
        newStr = str.substring(0,maxLength);
    }

    if(isReturn==undefined){
        document.write(newStr);
    }else{
        return  newStr;
    }
}

/**
 *  判断是否IE浏览器
 * @returns {boolean}
 */
function isIE() {
    var userAgent = window.navigator.userAgent;
    if (userAgent.indexOf("MSIE") >= 1 ){
        return true; //仅10极其以下有效 IE11 无效
    } else  if((userAgent.indexOf("WOW64") >=1) && (userAgent.indexOf("Chrome") <1) ){
        return true;// IE 11
    }
    return false;

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