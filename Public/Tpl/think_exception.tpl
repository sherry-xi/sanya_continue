<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<title>系统发生错误</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
html{ overflow-y: scroll; }
body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
img{ border: 0; }
.error{ padding: 24px 48px; }
.face{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
h1{ font-size: 32px; line-height: 48px; }
.error .content{ padding-top: 10px}
.error .info{ margin-bottom: 12px; }
.error .info .title{ margin-bottom: 3px; }
.error .info .title h3{ color: #000; font-weight: 700; font-size: 16px; }
.error .info .text{ line-height: 24px; }
.copyright{ padding: 12px 48px; color: #999; }
.copyright a{ color: #000; text-decoration: none; }
    .bg{
        width: 100%;
        height: 100%;
        filter:alpha(opacity=70);
        -moz-opacity:0.7;
        -khtml-opacity: 0.7;
        opacity: 0.7;
        background: #000;
    }
    .text{ width:400px;
        height:200px;margin:auto;
        color:#fff;
        font-weight: bolder;
        font-size: 25px;
        letter-spacing: 5px;
        position:relative;top:200px;
        text-align: center;line-height: 200px;z-index: 9999;}
</style>
</head>
<body style=" background:url('/Public/image/404-bg.png') no-repeat">
<div style="display: none">
    <h1>错误信息: <?php echo strip_tags($e['message']);?></h1>
    <h2>错误位置: <?php echo $e['file'] ;?> LINE: <?php echo $e['line'];?><br/></h2>
    <p>Trace:<?php echo $e['trace'];?></p>
</div>
<div class="bg" style="display: block">
    <div class="text">服务器繁忙，请稍后再访问</div>
</div>


</body>
</html>
<script src="/Public/lib/jquery-1.11.0.js"></script>
<script type="text/javascript">

    $(function(){
        $(".bg").css("height",$(document).height());
    });
</script>
