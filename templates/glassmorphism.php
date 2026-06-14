<?php
/**
 * Template Name: 玻璃态风格
 * Description: 毛玻璃效果跳转提示页，半透明卡片配模糊背景，视觉层次丰富，支持子比主题日夜模式自适应
 * Thumbnail: assets/img/glassmorphism.png
 * Author: 天无神话
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_unsafe = ($is_blocked || $blacklist_match);
$is_dark_mode = (function_exists('zib_get_theme_mode') && zib_get_theme_mode() === 'dark-theme');
$ad_enabled = wxs_go_option('ad_enabled', false);
$ad_image   = wxs_go_option('ad_image', '');
$ad_link    = wxs_go_option('ad_link', '');
$ad_alt     = wxs_go_option('ad_alt', '广告');
?><!DOCTYPE html>
<html>
<head>
    <script>
    (function(){
        var m=document.cookie.match(/(?:^|;\s*)theme_mode=([^;]*)/);
        var mode=m?decodeURIComponent(m[1]):'';
        if(mode==='dark-theme'){
            document.documentElement.classList.add('dark');
        }
        document.addEventListener('DOMContentLoaded',function(){
            var isDark=document.documentElement.classList.contains('dark')||document.body.classList.contains('dark-theme');
            var logo=document.getElementById('img_logo');
            if(logo&&isDark){
                var darkSrc=logo.getAttribute('dark-src');
                if(darkSrc) logo.src=darkSrc;
            }
        });
    })();
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <?php zib_head_favicon(); ?>
    <?php if (!$is_unsafe): ?>
    <noscript><meta http-equiv="refresh" content="1;url='<?php echo esc_url($url); ?>';"></noscript>
    <?php endif; ?>
    <title><?php echo esc_html($title); ?></title>
    <style type="text/css">
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%;overflow:hidden}
    body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;line-height:1.6}
    .bg-layer{position:fixed;inset:0;z-index:0;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);transition:background .4s}
    html.dark .bg-layer,.dark-theme .bg-layer{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%)}
    .bg-shape{position:fixed;z-index:1;border-radius:50%;opacity:.3;filter:blur(80px)}
    .bg-shape-1{width:400px;height:400px;top:-100px;left:-100px;background:#ff9a9e}
    .bg-shape-2{width:350px;height:350px;bottom:-80px;right:-80px;background:#a18cd1}
    .bg-shape-3{width:250px;height:250px;top:40%;left:60%;background:#fbc2eb}
    html.dark .bg-shape,.dark-theme .bg-shape{opacity:.15}
    .page{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100dvh;padding:24px}
    .glass-card{max-width:540px;width:100%;background:rgba(255,255,255,.15);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.25);border-radius:16px;padding:clamp(20px,4vh,32px) clamp(18px,3vw,28px);box-shadow:0 8px 32px rgba(0,0,0,.12);transition:background .4s,border-color .4s}
    html.dark .glass-card,.dark-theme .glass-card{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);box-shadow:0 8px 32px rgba(0,0,0,.3)}
    .logo-area{text-align:center;margin-bottom:20px}
    .logo-area img{width:clamp(100px,26vw,150px);height:auto;filter:drop-shadow(0 2px 8px rgba(0,0,0,.15))}
    .alert-bar{display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgba(255,158,77,.12);border-radius:10px;margin-bottom:20px;border:1px solid rgba(255,158,77,.2)}
    .alert-bar-danger{background:rgba(255,77,79,.1);border-color:rgba(255,77,79,.2)}
    html.dark .alert-bar,.dark-theme .alert-bar{background:rgba(255,158,77,.08);border-color:rgba(255,158,77,.15)}
    html.dark .alert-bar-danger,.dark-theme .alert-bar-danger{background:rgba(255,77,79,.06);border-color:rgba(255,77,79,.15)}
    .alert-icon{width:22px;height:22px;min-width:22px;background-size:contain;background-repeat:no-repeat;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M872.7 582.6L635.2 177c-53.5-91.3-186.6-88.1-235.6 5.7L187.7 588.3c-46.8 89.7 18.2 197 119.4 197h449.4c104 0 168.8-112.9 116.2-202.7zM496.6 295.2c0-20.5 11.7-31.5 35.1-32.9 22 1.5 33.7 12.5 35.1 32.9V315l-26.4 267.9h-13.2L496.6 315v-19.8zm35.1 350.4c-19.4 0-35.1-15.8-35.1-35.1s15.8-35.1 35.1-35.1 35.1 15.8 35.1 35.1-15.8 35.1-35.1 35.1z' fill='%23fc9153'%3E%3C/path%3E%3C/svg%3E")}
    .alert-icon-danger{background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm-32 256c0-17.7 14.3-32 32-32s32 14.3 32 32v224c0 17.7-14.3 32-32 32s-32-14.3-32-32V320zm32 448c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z' fill='%23ff4d4f'%3E%3C/path%3E%3C/svg%3E")}
    .alert-label{font-size:clamp(13px,2vw,15px);font-weight:700;color:#fff;letter-spacing:.5px}
    .content-area{font-size:clamp(12px,1.7vw,14px);color:rgba(255,255,255,.85);line-height:1.7;word-break:break-all;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,.15)}
    .url-link{color:#ffd166;font-weight:500;word-break:break-all}
    .blocked-reason{color:#ff6b6b;font-weight:600;font-size:13px;margin-top:6px}
    .warn-tag{display:inline-block;font-size:12px;padding:2px 10px;border-radius:20px;background:rgba(255,77,79,.15);color:#ff6b6b;margin-top:4px}
    .note-tag{display:inline-block;font-size:12px;padding:2px 10px;border-radius:20px;background:rgba(255,209,102,.12);color:#ffd166;margin-top:4px}
    .footer-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
    .time-info{font-size:clamp(12px,1.7vw,14px);color:rgba(255,255,255,.7)}
    .time-num{color:#ffd166;font-weight:700;font-size:16px;margin-right:4px}
    a{text-decoration:none}
    .btn-back,.btn-back:visited{color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.3);padding:8px 20px;border-radius:10px;font-size:14px;transition:all .25s;white-space:nowrap;background:rgba(255,255,255,.05)}
    .btn-back:hover{background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.5)}
    .btn-go,.btn-go:visited{color:#fff;border:1px solid rgba(255,209,102,.5);padding:8px 20px;border-radius:10px;font-size:14px;transition:all .25s;white-space:nowrap;background:rgba(255,209,102,.08)}
    .btn-go:hover{background:rgba(255,209,102,.25);border-color:#ffd166;box-shadow:0 4px 15px rgba(255,209,102,.2)}
    .ad-area{max-width:540px;width:100%;margin-top:16px;text-align:center}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-glass{background:rgba(255,255,255,.08);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:8px}
    html.dark .ad-glass,.dark-theme .ad-glass{background:rgba(255,255,255,.04)}
    .ad-area img{max-width:100%;height:auto;border-radius:8px;display:block}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#ffd166;border:1px solid rgba(255,209,102,.5);border-radius:10px;padding:4px 14px;font-size:12px;text-decoration:none;line-height:1.4;transition:all .25s;white-space:nowrap;background:rgba(255,209,102,.08);backdrop-filter:blur(8px)}
    .ad-go-btn:hover{background:rgba(255,209,102,.25);border-color:#ffd166;box-shadow:0 4px 15px rgba(255,209,102,.2)}
    @media(max-width:480px){
        .glass-card{padding:24px 18px;border-radius:12px}
        .btn-back,.btn-go{padding:8px 16px;font-size:13px}
        .bg-shape{filter:blur(60px)}
    }
    </style>
</head>
<body>
<div class="bg-layer"></div>
<div class="bg-shape bg-shape-1"></div>
<div class="bg-shape bg-shape-2"></div>
<div class="bg-shape bg-shape-3"></div>
<div class="page">
<div class="glass-card">
    <div class="logo-area">
        <img id="img_logo" src="<?php echo esc_url($is_dark_mode ? $logoimg_dark : $logoimg); ?>" white-src="<?php echo esc_url($logoimg); ?>" dark-src="<?php echo esc_url($logoimg_dark); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>
    <div class="alert-bar<?php echo $is_unsafe ? ' alert-bar-danger' : ''; ?>">
        <div class="alert-icon<?php echo $is_unsafe ? ' alert-icon-danger' : ''; ?>"></div>
        <div class="alert-label"><?php
            if ($is_nonce_invalid) {
                echo '验证失败';
            } elseif ($blacklist_match) {
                echo '安全风险警告';
            } else {
                echo '请注意账号和财产安全';
            }
        ?></div>
    </div>
    <div class="content-area">
        <?php if ($is_nonce_invalid): ?>
            链接验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
            <?php if ($blocked_reason): ?>
                <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
            <?php endif; ?>
        <?php elseif ($blacklist_match): ?>
            您即将访问：<span class="url-link"><?php echo esc_html($url); ?></span>
            <br><span class="warn-tag">安全警告</span> 该网址可能存在违反法律规定的内容或安全风险，为保护您的权益，已拦截此次跳转。
        <?php else: ?>
            您即将离开<?php echo esc_html(get_bloginfo('name')); ?>，去往：<span class="url-link"><?php echo esc_html($url); ?></span>
            <br><span class="note-tag">注意</span> 该网址与本站无关，本站不负任何责任或义务
        <?php endif; ?>
    </div>
    <div class="footer-row">
        <div class="time-info">
            <?php if ($is_nonce_invalid): ?>
                <span class="time-num">!</span>请返回刷新原页面
            <?php elseif ($blacklist_match): ?>
                <span class="time-num">X</span>已拦截
            <?php else: ?>
                <span id="time" class="time-num"><?php echo (int)$countdown; ?></span>秒后自动跳转
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px">
            <a class="btn-back" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
            <?php if ($blacklist_match): ?>
                <a class="btn-go" id="copyBtn" href="javascript:;" rel="external nofollow">复制链接</a>
            <?php elseif (!$is_nonce_invalid): ?>
                <a class="btn-go" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if ($ad_enabled && $ad_image): ?>
<div class="ad-area">
    <div class="ad-glass">
        <div class="ad-img-wrap">
            <?php if ($ad_link): ?>
                <a href="<?php echo esc_url($ad_link); ?>" target="_blank"><img src="<?php echo esc_url($ad_image); ?>" alt="<?php echo esc_attr($ad_alt); ?>"></a>
                <a class="ad-go-btn" href="<?php echo esc_url($ad_link); ?>" target="_blank">前往</a>
            <?php else: ?>
                <img src="<?php echo esc_url($ad_image); ?>" alt="<?php echo esc_attr($ad_alt); ?>">
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
</div>

<?php if (!$is_unsafe): ?>
<script type="text/javascript">
(function(){
    var seconds=<?php echo (int)$countdown; ?>;
    var el=document.getElementById('time');
    var url=<?php echo json_encode($url); ?>;
    function tick(){
        if(seconds>0){
            seconds--;
            el.innerHTML=seconds;
            setTimeout(tick,1000);
        }else{
            window.location.href=url;
        }
    }
    tick();
    setTimeout(function(){window.opener=null;window.close();},15000);
})();
</script>
<?php endif; ?>

<?php if ($blacklist_match): ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var copyBtn=document.getElementById('copyBtn');
    if(!copyBtn)return;
    copyBtn.addEventListener('click',function(){
        var urlText=document.querySelector('.url-link');
        if(urlText&&navigator.clipboard){
            navigator.clipboard.writeText(urlText.textContent).then(function(){alert('已复制链接');});
        }
    });
});
</script>
<?php endif; ?>
</body>
</html>
