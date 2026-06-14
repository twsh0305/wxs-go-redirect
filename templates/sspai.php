<?php
/**
 * Template Name: 少数派风格
 * Description: 仿少数派外链跳转页，浅色背景圆角卡片，黑色按钮，居中布局，强调手动复制链接
 * Thumbnail: assets/img/sspai.png
 * Author: 天无神话
 * Version: 1.0.0
 * Sticker: true
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
    button{padding:0;font-family:inherit;background:none;border:none;outline:none;cursor:pointer}
    html{width:100%;min-height:100%;background-color:#fff}
    body{min-height:100dvh;padding:0 16px;padding-top:clamp(40px,8vh,64px);color:#222;font-size:14px;font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue","PingFang SC","Microsoft YaHei",sans-serif;line-height:1.5;position:relative;padding-bottom:116px}
    @media(min-width:1024px){body{padding-top:clamp(100px,15vh,164px)}}
    .page__link{background:#f7f7f7;border-radius:20px;padding:clamp(20px,4vh,32px);max-width:788px;margin:auto}
    @media(min-width:1024px){.page__link{padding:clamp(40px,8vh,64px) clamp(40px,8vw,80px) clamp(32px,5vh,48px)}}
    .page__header{text-align:center;margin-bottom:24px}
    .page__header img{width:112px}
    @media(min-width:1024px){.page__header img{width:128px}}
    .page__title{font-size:20px;line-height:28px;font-weight:500;margin-bottom:8px;text-align:center}
    .page__desc{font-size:14px;line-height:20px;text-align:center;max-width:494px;margin:auto;margin-bottom:32px;color:#666}
    .page__target{cursor:pointer;border-radius:16px;background:#fff;font-size:13px;line-height:16px;padding:12px 16px;color:#222;word-break:break-word;text-align:center}
    .blocked-reason{color:#d4483b;font-size:12px;margin-top:8px;font-weight:500}
    .btn__wrapper{margin-top:40px;text-align:center;display:flex;justify-content:center;gap:12px;flex-wrap:wrap}
    .btn{min-width:120px;height:48px;background:#000;color:#fff;font-size:17px;line-height:48px;font-weight:500;border-radius:12px;display:inline-block;text-align:center;text-decoration:none;transition:opacity .2s}
    .btn:hover{opacity:.85}
    .btn-back{background:#fff;color:#333;border:1px solid #ddd}
    .btn-back:hover{border-color:#999;opacity:1}
    .btn-danger{background:#d4483b}
    .footer__txt{text-align:center;color:#999;position:absolute;bottom:0;left:0;width:100%;box-sizing:border-box;padding:24px;font-size:13px;line-height:18px}
    .ad-area{max-width:788px;margin:24px auto 0;text-align:center}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-area img{max-width:100%;height:auto;border-radius:12px}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#fff;background:#000;border-radius:6px;padding:4px 12px;font-size:12px;text-decoration:none;line-height:1.4;transition:opacity .2s;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.15)}
    .ad-go-btn:hover{opacity:.85}
    @media(max-width:500px){
        body{padding-top:clamp(24px,6vh,40px);padding-bottom:100px}
        .page__link{padding:clamp(16px,3vh,24px) clamp(12px,3vw,20px)}
        .btn{min-width:100px;height:44px;line-height:44px;font-size:15px}
    }
    </style>
</head>
<body>
<div class="page__link">
    <div class="page__header">
        <img src="<?php echo esc_url($logoimg); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>
    <div class="page__title">
        <?php if ($is_nonce_invalid): ?>
            链接验证失败
        <?php elseif ($blacklist_match): ?>
            你访问的网站可能包含未知的安全风险
        <?php else: ?>
            即将离开<?php echo esc_html(get_bloginfo('name')); ?>
        <?php endif; ?>
    </div>
    <div class="page__desc">
        <?php if ($is_nonce_invalid): ?>
            链接验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
        <?php elseif ($blacklist_match): ?>
            该网址可能存在安全风险，已为您拦截。
        <?php else: ?>
            你访问的网站可能包含未知的安全风险，如需继续访问，请手动复制链接访问，并注意保护账号和隐私信息
        <?php endif; ?>
    </div>
    <?php if (!$is_nonce_invalid): ?>
    <div class="page__target" id="target">
        <?php echo esc_html($url); ?>
        <?php if ($blocked_reason && $blacklist_match): ?>
            <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="btn__wrapper">
        <a class="btn btn-back" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
        <?php if ($blacklist_match): ?>
            <a class="btn btn-danger" id="copyBtn" href="javascript:;">复制链接</a>
        <?php elseif (!$is_nonce_invalid): ?>
            <a class="btn" id="copyBtn" href="javascript:;">复制链接</a>
        <?php endif; ?>
    </div>
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
<div class="footer__txt">
    &copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var b=document.getElementById('copyBtn');
    if(b){b.addEventListener('click',function(){
        var url=<?php echo json_encode($url); ?>;
        if(navigator.clipboard){navigator.clipboard.writeText(url).then(function(){alert('复制成功！');});}
    });}
});
</script>
</body>
</html>
