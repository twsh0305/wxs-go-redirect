<?php
/**
 * Template Name: 简书风格
 * Description: 仿简书外链跳转页，浅灰背景白卡片居中，链接卡片带图标，圆角橙色边框按钮
 * Thumbnail: assets/img/jianshu.png
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
    body{background-color:#f3f3f3;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.42857;min-height:100dvh}
    .wrapper{position:absolute;width:620px;max-width:94%;padding:clamp(24px,5vh,40px) 0;border-radius:6px;text-align:center;top:clamp(40px,10vh,88px);left:50%;transform:translateX(-50%);background-color:#fff}
    .danger{text-align:center;line-height:1;margin-bottom:12px;font-size:64px;color:#ea725d}
    .title{font-size:22px;color:#2f2f2f}
    .tip{font-size:16px;color:#888;margin-top:8px}
    .link-card{display:flex;align-items:center;width:460px;max-width:90%;margin:12px auto 0;padding:10px;border-radius:4px;background:#fafafa;border:1px solid #ddd}
    .link-card .icon{flex-shrink:0;width:40px;height:40px;line-height:40px;font-size:20px;background:#bcc6d8;text-align:center;border-radius:2px}
    .link-card .icon svg{width:20px;height:20px;fill:#f3f3f3;vertical-align:middle}
    .link-card .link{font-size:14px;color:#3194d0;margin-left:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .blocked-reason{color:#ea725d;font-size:13px;margin-top:8px}
    .btn-group{text-align:center;font-size:0;margin-top:24px}
    .btn{display:inline-block;width:144px;height:44px;line-height:43px;border-radius:22px;font-size:14px;color:#ea725d;border:1px solid #ea725d;cursor:pointer;text-decoration:none;transition:all .2s;background:#fff}
    .btn:hover{background:#ea725d;color:#fff}
    .btn+.btn{margin-left:24px}
    .btn-back{color:#999;border-color:#ddd}
    .btn-back:hover{background:#f5f5f5;color:#666;border-color:#bbb}
    .btn-danger{color:#ea725d;border-color:#ea725d}
    .time-text{font-size:13px;color:#999;margin-top:16px;text-align:center}
    .time-num{color:#ea725d;font-weight:600}
    .ad-area{width:620px;max-width:94%;margin:20px auto 0;text-align:center;position:absolute;top:calc(88px + 100%);left:50%;transform:translateX(-50%)}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-area img{max-width:100%;height:auto;border-radius:4px}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#ea725d;border:1px solid #ea725d;border-radius:4px;padding:4px 12px;font-size:12px;text-decoration:none;background:#fff;line-height:1.4;transition:all .2s;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.1)}
    .ad-go-btn:hover{background:#ea725d;color:#fff}
    @media(max-width:650px){
        .wrapper{width:94%;top:clamp(30px,8vh,60px);padding:clamp(16px,3vh,30px) 0}
        .title{font-size:18px}
        .tip{font-size:14px}
        .link-card{width:90%}
        .btn{width:120px;height:40px;line-height:39px;font-size:13px}
    }
    </style>
</head>
<body>
<div class="wrapper">
    <?php if ($blacklist_match): ?>
    <div class="danger">
        <svg viewBox="0 0 1024 1024" width="64" height="64" fill="#ea725d"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm-32 232c0-4.4 3.6-8 8-8h48c4.4 0 8 3.6 8 8v272c0 4.4-3.6 8-8 8h-48c-4.4 0-8-3.6-8-8V296zm32 440c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z"/></svg>
    </div>
    <?php endif; ?>
    <div class="title">
        <?php if ($is_nonce_invalid): ?>
            链接验证失败
        <?php elseif ($blacklist_match): ?>
            该网站可能存在风险
        <?php else: ?>
            即将跳转到外部网站
        <?php endif; ?>
    </div>
    <div class="tip">
        <?php if ($is_nonce_invalid): ?>
            验证已过期或来源不合法，请返回原页面刷新后重试。
        <?php elseif ($blacklist_match): ?>
            我们不建议您前往
        <?php else: ?>
            安全性未知，是否继续
        <?php endif; ?>
    </div>
    <?php if (!$is_nonce_invalid): ?>
    <div class="link-card">
        <div class="icon">
            <svg viewBox="0 0 1024 1024" width="20" height="20" fill="#f3f3f3"><path d="M832 192c-53-53-139-53-192 0l-96 96c-53 53-53 139 0 192s139 53 192 0l96-96c53-53 53-139 0-192zM384 448l-96 96c-53 53-53 139 0 192s139 53 192 0l96-96c53-53 53-139 0-192s-139-53-192 0z"/></svg>
        </div>
        <div class="link" title="<?php echo esc_attr($url); ?>"><?php echo esc_html($url); ?></div>
    </div>
    <?php endif; ?>
    <?php if ($blocked_reason && $blacklist_match): ?>
        <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
    <?php endif; ?>
    <div class="btn-group">
        <?php if ($blacklist_match): ?>
            <a class="btn btn-danger" id="copyBtn" href="javascript:;">复制链接</a>
        <?php elseif (!$is_nonce_invalid): ?>
            <a class="btn" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续前往</a>
        <?php endif; ?>
        <a class="btn btn-back" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
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

<?php if (!$is_unsafe): ?>
<script>
(function(){
    var seconds=<?php echo (int)$countdown; ?>;
    var el=document.getElementById('time');
    var url=<?php echo json_encode($url); ?>;
    function tick(){if(seconds>0){seconds--;el.innerHTML=seconds;setTimeout(tick,1000);}else{location.replace(url);}}
    tick();
})();
</script>
<?php endif; ?>
<?php if ($blacklist_match): ?>
<script>
document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('copyBtn');if(b){b.addEventListener('click',function(){if(navigator.clipboard){navigator.clipboard.writeText(<?php echo json_encode($url); ?>).then(function(){alert('复制成功');});}});}});
</script>
<?php endif; ?>
</body>
</html>
