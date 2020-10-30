/**
 * Created by Aous on 2017/9/10.
 * js验证
 */
    //admin 前缀的是后台的验证，index前缀的是前台的验证

    adminLogin();       //后台登录
    adminProfile();     //用户信息修改
    adminPassword();    //修改密码
    adminUserAdd();     //添加用户
    addChannel();       //添加频道
    addArticle();       //添加文章
    addSlide();           //7.添加幻灯片  友情链接 滚动栏
    applyStudent();     //8.招生申请
    applyQuery();       //招生查询

function applyQuery(){
    validate('applyQuery', {
        fields: {
            name: "姓名:required;length[2~20]",
            card: "身份证号:required;card",
            //phone: "手机号:required;tel",
            code: "验证码:required;length[4]"
        },
        valid:function(form){
            $.ajax({
                type: "GET",
                url: checkCode,
                async:false,
                data: {code:$("#code").val()},
                dataType: "json",
                success: function(data){
                    if(data.errcode == 0){
                        form.submit();
                    }else{
                        $("input[name=code]").focus().select();
                        alert(data.msg);
                    }
                }
            });
        }
    });
}

//8.招生申请
function applyStudent(){
    validate('applyStudent',{
        fields: {
            name: "姓名:required;length[2~20]",
            card: "身份证号:required;card",
            birthday: "出生年月:required;length[2~20]",
            nation: "民族:required;length[1~20]",
            native: "籍贯:required;length[2~20]",
            account_addr: "户口所在地:required;length[2~20]",
            addr: "家庭住址:required;length[2~50]",
            school: "初中毕业学校:required;length[2~20]",
            class: "报考专业:required;length[2~20]",
            qq: "QQ:required;qq",
            phone: "本人联系电话:required;tel",
            parent_phone: "家长联系电话:required;tel"
        },
        valid: function(form) {
            if($("#id").val() == ''){
                if(!confirm("您确定报名吗?(提交后不可修改,请您仔细检查)")){
                    return false;
                }
            }
            form.submit();
        }
    });
}

//7.添加幻灯片  友情链接 滚动栏
function addSlide(){
    validate('addSlide',{
        fields: {
            title: "简述:required;length[2~40]",
            sort: "排序:required;integer(+);range(1~100)",
        },
        valid: function(form) {
            //缩略图校验
            if( $("#type").val() != 2 && $("#imgSrc").val() == ''){
                $("#thumbMsg").trigger("showmsg", ["error", "请上传图片"]);
                $("#thumbMsg").focus();
                return false;
            }
            form.submit();
        }
    });
}

//6.添加文章
function addArticle(){
    validate('addArticle',{

        fields: {
            title: "文章标题:required;length[2~40]",
            //keyword: "关键字:required;length[2~20]",
            cid: "所属频道:required",
        },
        valid: function(form) {
            //缩略图校验
           if( $(".extra:checked").length > 0 && $("#thumb").val() == ''){
               $("#thumbMsg").trigger("showmsg", ["error", "选择了幻灯片就必须上传缩略图"]);
               $("#thumbMsg").focus();
               return false;
           }
            form.submit();
        }
    });
}


//5.添加频道
function addChannel(){
    validate('addChannel',{
        fields: {
            name: "频道名称:required;length[2~10]",
            sort: "排序:required;integer(+);range(1~100)",
        }
    });
}

//4.添加用户
function adminUserAdd(){

    validate('userAdd',{
        fields: {
            username: "账号:required;username",
            truename: "姓名:required;length[2~10]",
            mobile: "手机号:required;mobile",
            email: "邮箱:required;email"
        },
        valid: function(form) {
            var userid   = $("#id").val();
            var password = $("#password").val();

            if(userid == ''){ //添加用户必须填写密码
                if(isEmpty("password") === false){
                    alert("请输入密码");
                    $("#password").focus();
                    return false;
                }
            }
            if(password != ''){
                if(!regById(/^[\S]{6,16}$/,'password')){
                    alert("请填写6-16位字符，不能包含空格");
                    $("#password").focus();
                    return false;
                }
                if(isEmpty("confirmPassword") === false){
                    alert("请确认密码");
                    $("#confirmPassword").focus();
                    return false;
                }
                if(!compareById('password',"confirmPassword")){
                    alert("两次输入密码不相同");
                    $("#confirmPassword").focus();
                    return false;
                }
            }

            form.submit();
        }
    });

}

//3.修改密码
function adminPassword(){
    validate('password',{
        fields: {
            oldPassword: "旧密码:required;password",
            newPassword: "新密码:required;password",
            comfirmPassword: "确认新密码:required:match(eq, newPassword)"
        }
    });
}

//2.用户信息修改
function adminProfile(){
    validate('profile',{
        fields: {
            /*truename: "姓名:required;length[2~10]",
            mobile: "手机号:required;mobile",
            email: "邮箱:required;email",*/
        }
    });
}

//1.后台登录
function adminLogin(){
    validate('login',{
        fields: {
            username: "账号:required",
            password: "密码:required",
            code:     "验证码:required;length[4]"
        }
    });
}



/**
 * 校验方法
 * @param id
 * @param param
 */
function validate(id,param){
    var obj = $("#"+id+"Form");
    param.timely      = 2;
    param.stopOnError = true;
    param.theme       = "yellow_right";
    obj.validator(param);
}