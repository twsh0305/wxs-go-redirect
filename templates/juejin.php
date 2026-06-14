<?php
/**
 * Template Name: 掘金风格
 * Description: 仿稀土掘金外链跳转页，浅灰背景白卡片，logo悬浮卡片上方，蓝色按钮靠右
 * Thumbnail: assets/img/juejin.png
 * Author: 天无神话
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_unsafe = ($is_blocked || $blacklist_match);
$ad_enabled = wxs_go_option('ad_enabled', false);
$ad_image   = wxs_go_option('ad_image', '');
$ad_link    = wxs_go_option('ad_link', '');
$ad_alt     = wxs_go_option('ad_alt', '广告');
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php zib_head_favicon(); ?>
    <title><?php echo esc_html($title); ?></title>
    <style type="text/css">
    *{margin:0;padding:0;box-sizing:border-box}
    a{text-decoration:none}
    html{font-size:12px;background-color:#f4f5f5}
    body{font-family:"PingFang SC",-apple-system,BlinkMacSystemFont,"Helvetica Neue","Microsoft YaHei",sans-serif;min-height:100dvh;position:relative}
    .middle-page{position:absolute;left:50%;top:clamp(10%,18vh,30%);max-width:624px;width:86%;background-color:#fff;transform:translateX(-50%);padding:clamp(20px,4vh,30px) 40px 0;border:1px solid #e5e6eb;border-radius:2px}
    .logo{display:block;width:116px;height:24px;position:absolute;top:-40px;left:0}
    .logo img{width:100%;height:100%;object-fit:contain}
    .content .title{margin:0;font-size:18px;line-height:24px;font-weight:500;color:#252933}
    .link-container{padding:16px 0 24px;border-bottom:1px solid #e5e6eb;position:relative;color:gray;font-size:14px}
    .link-content{overflow:hidden;word-break:break-all;line-height:22px;max-height:44px}
    .link-content.is-expanded{max-height:none}
    .unfold{color:#007fff;cursor:pointer;display:inline}
    .btn-area{overflow:hidden;padding-bottom:24px}
    .btn{float:right;margin-top:20px;color:#fff;border-radius:3px;border:none;background:#007fff;height:32px;font-size:14px;padding:0 14px;cursor:pointer;outline:0;text-decoration:none;display:inline-block;line-height:32px}
    .btn:hover{background:#0070e6}
    .btn-danger{background:#f53f3f}
    .btn-danger:hover{background:#d93636}
    .btn-back{background:#fff;color:#515767;border:1px solid #e5e6eb;margin-right:12px}
    .btn-back:hover{border-color:#c2c8d1;color:#252933;background:#fff}
    .blocked-reason{color:#f53f3f;font-size:13px;margin-top:8px}
    .time-text{font-size:12px;color:#8a919f;margin-top:12px;text-align:right;padding-bottom:20px}
    .time-num{color:#007fff;font-weight:600}
    .ad-area{position:absolute;left:50%;top:55%;transform:translateX(-50%);max-width:624px;width:86%;margin-top:16px;text-align:center}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-area img{max-width:100%;height:auto;border-radius:2px}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#fff;background:#007fff;border-radius:3px;padding:4px 12px;font-size:12px;text-decoration:none;line-height:1.4;transition:background .2s;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.15)}
    .ad-go-btn:hover{background:#0070e6}
    @media(max-width:500px){
        .middle-page{padding:clamp(16px,3vh,25px) 25px 0;top:clamp(8%,15vh,25%)}
        .content .title{font-size:16px}
        .link-container{border-bottom:none;font-size:12px}
        .btn{margin-top:0;width:100%;height:48px;line-height:48px;text-align:center;float:none}
        .btn-back{margin-right:0;margin-bottom:10px;width:100%;float:none}
    }
    </style>
</head>
<body>
<div class="middle-page">
    <div class="logo">
        <img src="<?php echo esc_url($logoimg); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>
    <div class="content">
        <p class="title">
            <?php if ($is_nonce_invalid): ?>
                链接验证失败，请返回原页面刷新后重试
            <?php elseif ($blacklist_match): ?>
                该链接已被安全拦截
            <?php else: ?>
                即将离开<?php echo esc_html(get_bloginfo('name')); ?>，请注意账号财产安全
            <?php endif; ?>
        </p>
        <?php if (!$is_nonce_invalid): ?>
        <div class="link-container">
            <div class="link-content" id="linkContent"><?php echo esc_html($url); ?></div>
            <span class="unfold" id="unfoldBtn" style="display:none">展开</span>
        </div>
        <?php endif; ?>
        <?php if ($blocked_reason && $blacklist_match): ?>
            <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
        <?php endif; ?>
    </div>
    <div class="btn-area">
        <a class="btn btn-back" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
        <?php if ($blacklist_match): ?>
            <a class="btn btn-danger" id="copyBtn" href="javascript:;">复制链接</a>
        <?php elseif (!$is_nonce_invalid): ?>
            <a class="btn" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
        <?php endif; ?>
    </div>
    <?php if (!$is_unsafe): ?>
    <div class="time-text"><span class="time-num" id="time"><?php echo (int)$countdown; ?></span> 秒后自动跳转</div>
    <?php endif; ?>
</div>
<?php if ($ad_enabled && $ad_image): ?>
<div class="ad-area">
    <div class="ad-img-wrap">
        <?php if ($ad_link): ?>
            <a href="<?php echo esc_url($ad_link); ?>" target="_blank"><img src="<?php echo esc_url($ad_image); ?>" alt="<?php echo esc_attr($ad_alt); ?>"></a>
            <a class="ad-go-btn" href="<?php echo esc_url($ad_link); ?>" target="_blank">前往</a>
        <?php else: ?>
            <img src="<?php echo esc_url($ad_image); ?>" alt="<?php echo esc_attr($ad_alt); ?>">
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var lc=document.getElementById('linkContent');
    var ub=document.getElementById('unfoldBtn');
    if(lc&&ub&&lc.scrollHeight>50){ub.style.display='inline';ub.addEventListener('click',function(){lc.className='link-content is-expanded';ub.style.display='none';});}
});
</script>
<?php if (!$is_unsafe): ?>
<script>
(function(){
    var seconds=<?php echo (int)$countdown; ?>;
    var el=document.getElementById('time');
    var url=<?php echo json_encode($url); ?>;
    function tick(){if(seconds>0){seconds--;el.innerHTML=seconds;setTimeout(tick,1000);}else{window.location.href=url;}}
    tick();
})();
</script>
<?php endif; ?>
<?php if ($blacklist_match): ?>
<script>
document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('copyBtn');if(b){b.addEventListener('click',function(){if(navigator.clipboard){navigator.clipboard.writeText(<?php echo json_encode($url); ?>).then(function(){alert('已复制链接');});}});}});
</script>
<?php endif; ?>
</body>
</html>
