<?php
/**
 * Template Name: 码云风格
 * Description: 仿Gitee外链跳转页，白底卡片居中，Logo在上，橙色继续按钮靠右下，强调复制链接
 * Thumbnail: assets/img/gitee.png
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
    html,body{font-size:10px;min-height:100dvh;background:#fff}
    body{font-family:-apple-system,BlinkMacSystemFont,"Helvetica Neue","PingFang SC","Microsoft YaHei",sans-serif;position:relative}
    .gitee-external-link-wrapper{display:flex;flex-direction:column;align-items:center;position:absolute;top:clamp(8%,15vh,22%);left:50%;transform:translateX(-50%);width:90%;max-width:600px}
    .logo-img{width:clamp(80px,20vw,113px);margin-bottom:16px;display:block}
    .content-box{display:flex;flex-direction:column;box-sizing:border-box;padding:clamp(16px,3vh,24px);width:100%;min-height:211px;border-radius:4px;border:1px solid #dce3e8;background:#fff}
    .content-title{font-size:20px;font-weight:500;color:#40485b;line-height:28px;margin-bottom:12px}
    .content-text{font-size:16px;font-weight:400;color:#40485b;line-height:22px;margin-bottom:24px}
    .content-link{line-height:21px;margin-bottom:24px}
    .external-link-href{max-width:100%;color:#40485b;font-weight:400;font-size:16px;word-break:break-all}
    .blocked-reason{color:#c9353f;font-size:13px;margin-top:8px}
    .external-link-btn{font-weight:500;align-self:flex-end;font-size:14px;color:#fff;background-color:#e07b53;border:none;border-radius:4px;padding:10px 20px;cursor:pointer;text-decoration:none;display:inline-block;transition:background .2s}
    .external-link-btn:hover{background-color:#d06a42}
    .btn-row{display:flex;justify-content:flex-end;gap:12px;align-items:center;margin-top:auto}
    .btn-back{font-size:14px;color:#40485b;background:#fff;border:1px solid #dce3e8;border-radius:4px;padding:10px 20px;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s}
    .btn-back:hover{border-color:#adb5bd;color:#333}
    .footer-note{font-size:12px;color:#999;text-align:center;margin-top:16px}
    .ad-area{width:100%;max-width:600px;margin-top:16px;text-align:center}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-area img{max-width:100%;height:auto;border-radius:4px}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#fff;background:#e07b53;border-radius:4px;padding:4px 12px;font-size:12px;text-decoration:none;line-height:1.4;transition:background .2s;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.15)}
    .ad-go-btn:hover{background:#d06a42}
    @media(max-width:500px){
        .gitee-external-link-wrapper{top:clamp(5%,10vh,15%);width:94%}
        .content-box{padding:16px;min-height:auto}
        .content-title{font-size:18px}
        .content-text{font-size:14px}
        .external-link-href{font-size:14px}
        .logo-img{width:90px}
    }
    </style>
</head>
<body>
<div class="gitee-external-link-wrapper">
    <img class="logo-img" src="<?php echo esc_url($logoimg); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <div class="content-box">
        <div class="content-title">
            <?php if ($is_nonce_invalid): ?>
                链接验证失败
            <?php elseif ($blacklist_match): ?>
                已拦截不安全链接
            <?php else: ?>
                即将跳转到外部网站
            <?php endif; ?>
        </div>
        <div class="content-text">
            <?php if ($is_nonce_invalid): ?>
                验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
            <?php elseif ($blacklist_match): ?>
                该链接存在安全风险，已为您拦截。
            <?php else: ?>
                您将要访问的链接不属于 <?php echo esc_html(get_bloginfo('name')); ?>，请注意您的账号和财产安全。
                <div>如需浏览，请复制后使用浏览器访问。</div>
            <?php endif; ?>
        </div>
        <?php if (!$is_nonce_invalid): ?>
        <div class="content-link">
            <div class="external-link-href"><?php echo esc_html($url); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($blocked_reason && $blacklist_match): ?>
            <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
        <?php endif; ?>
        <div class="btn-row">
            <a class="btn-back" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
            <?php if (!$is_nonce_invalid): ?>
                <a class="external-link-btn" id="copyBtn" href="javascript:;">复制链接</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer-note"><?php echo esc_html(get_bloginfo('name')); ?> 对外部网站内容不承担任何责任</div>
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
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var b=document.getElementById('copyBtn');
    if(b){b.addEventListener('click',function(){
        var url=<?php echo json_encode($url); ?>;
        if(navigator.clipboard){navigator.clipboard.writeText(url).then(function(){alert('链接已复制到剪贴板');});}
    });}
});
</script>
</body>
</html>
