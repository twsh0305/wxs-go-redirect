<?php
/**
 * Template Name: 知乎风格
 * Description: 仿知乎安全中心外链跳转页，灰色背景灰底卡片，蓝色按钮靠右，点击URL可展开
 * Thumbnail: assets/img/zhihu.png
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
    body,h1,p{margin:0;padding:0}
    a{text-decoration:none}
    button{padding:0;font-family:inherit;background:none;border:none;outline:none;cursor:pointer}
    html{width:100%;height:100%;background-color:#eff2f5}
    body{min-height:100dvh;padding-top:clamp(60px,12vh,100px);color:#222;font-size:13px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;line-height:1.5;-webkit-tap-highlight-color:rgba(0,0,0,0)}
    @media(max-width:620px){body{font-size:15px;padding-top:clamp(30px,8vh,60px)}}
    .button{display:inline-block;padding:10px 16px;color:#fff;font-size:14px;line-height:1;background-color:#0077d9;border-radius:3px;text-decoration:none}
    @media(max-width:620px){.button{font-size:16px}}
    .button:hover{background-color:#0070cd}
    .button:active{background-color:#0077d9}
    .button-danger{background-color:#c33}
    .button-danger:hover{background-color:#b22}
    .link-button{color:#105cb6;font-size:13px;display:inline-block;padding:10px 16px;line-height:1;text-decoration:none}
    @media(max-width:620px){.link-button{font-size:15px}}
    .logo,.wrapper{margin:auto;padding-left:30px;padding-right:30px;max-width:540px}
    .wrapper{padding-top:25px;padding-bottom:25px;background-color:#f7f7f7;border:1px solid #babbbc;border-radius:5px}
    @media(max-width:620px){.logo,.wrapper{margin:0 10px}}
    .logo{margin-bottom:16px}
    .logo img{display:block;width:clamp(120px,30vw,200px)}
    h1{margin-bottom:12px;font-size:16px;font-weight:700;line-height:1}
    @media(max-width:620px){h1{font-size:18px}}
    .warning{color:#c33}
    .info{font-size:14px;color:#222;line-height:24px}
    .link{margin-top:12px;word-wrap:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer;color:#175199}
    .link.is-expanded{word-wrap:break-word;white-space:normal}
    .blocked-reason{color:#c33;font-size:13px;margin-top:8px;font-weight:500}
    .actions{margin-top:15px;padding-top:20px;text-align:right;border-top:1px solid #d8d8d8}
    .actions .link-button+.link-button{margin-left:30px}
    .time-text{font-size:13px;color:#8590a6;margin-top:14px;text-align:right}
    .time-num{color:#0077d9;font-weight:600}
    .ad-area{max-width:540px;margin:16px auto 0;padding:0 30px}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-area img{max-width:100%;height:auto;border-radius:4px}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#fff;background:#0077d9;border-radius:3px;padding:4px 12px;font-size:12px;text-decoration:none;line-height:1.4;transition:background .2s;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.15)}
    .ad-go-btn:hover{background:#0070cd}
    @media(max-width:620px){.ad-area{margin:16px 10px 0;padding:0}}
    </style>
</head>
<body>
<div class="logo">
    <img src="<?php echo esc_url($logoimg); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
</div>
<div class="wrapper">
    <div class="content">
        <h1<?php if ($blacklist_match): ?> class="warning"<?php endif; ?>>
            <?php if ($is_nonce_invalid): ?>
                链接验证失败
            <?php elseif ($blacklist_match): ?>
                安全风险拦截
            <?php else: ?>
                即将离开<?php echo esc_html(get_bloginfo('name')); ?>
            <?php endif; ?>
        </h1>
        <p class="info">
            <?php if ($is_nonce_invalid): ?>
                验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
            <?php elseif ($blacklist_match): ?>
                该网址可能存在安全风险，已拦截此次跳转。
            <?php else: ?>
                您即将离开<?php echo esc_html(get_bloginfo('name')); ?>，请注意您的帐号和财产安全。
            <?php endif; ?>
        </p>
        <p class="link" id="linkEl"><?php echo esc_html($url); ?></p>
        <?php if ($blocked_reason && $blacklist_match): ?>
            <p class="blocked-reason"><?php echo esc_html($blocked_reason); ?></p>
        <?php endif; ?>
    </div>
    <div class="actions">
        <a class="link-button" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
        <?php if ($blacklist_match): ?>
            <a class="button button-danger" id="copyBtn" href="javascript:;">复制链接</a>
        <?php elseif (!$is_nonce_invalid): ?>
            <a class="button" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
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
var linkEl=document.getElementById('linkEl');
if(linkEl){linkEl.addEventListener('click',function(){
    this.className='link is-expanded';
    if(window.getSelection){var s=window.getSelection(),r=document.createRange();r.selectNodeContents(this);s.removeAllRanges();s.addRange(r);}
});}
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
