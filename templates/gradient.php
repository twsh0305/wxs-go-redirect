<?php
/**
 * Template Name: 渐变流动风格
 * Description: 动态渐变背景跳转提示页，柔和色彩过渡搭配浮动圆点装饰，现代感十足，支持子比主题日夜模式自适应
 * Thumbnail: assets/img/gradient.png
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
    .gradient-bg{position:fixed;inset:0;z-index:0;background:linear-gradient(-45deg,#ee7752,#e73c7e,#23a6d5,#23d5ab);background-size:400% 400%;animation:gradientShift 12s ease infinite;transition:opacity .3s}
    html.dark .gradient-bg,.dark-theme .gradient-bg{background:linear-gradient(-45deg,#1a1a2e,#16213e,#0f3460,#1a1a2e);background-size:400% 400%;animation:gradientShift 15s ease infinite}
    @keyframes gradientShift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
    .float-dot{position:fixed;z-index:1;border-radius:50%;opacity:.4;animation:floatUp 8s ease-in-out infinite;pointer-events:none}
    .float-dot-1{width:80px;height:80px;left:10%;bottom:-80px;background:rgba(255,255,255,.25);animation-duration:7s;animation-delay:0s}
    .float-dot-2{width:60px;height:60px;left:40%;bottom:-60px;background:rgba(255,255,255,.2);animation-duration:9s;animation-delay:1s}
    .float-dot-3{width:100px;height:100px;left:70%;bottom:-100px;background:rgba(255,255,255,.15);animation-duration:11s;animation-delay:2s}
    .float-dot-4{width:40px;height:40px;left:25%;bottom:-40px;background:rgba(255,255,255,.3);animation-duration:6s;animation-delay:3s}
    .float-dot-5{width:50px;height:50px;left:55%;bottom:-50px;background:rgba(255,255,255,.2);animation-duration:8s;animation-delay:4s}
    html.dark .float-dot,.dark-theme .float-dot{opacity:.08}
    @keyframes floatUp{0%{transform:translateY(0) scale(1);opacity:.4}50%{opacity:.2}100%{transform:translateY(-100vh) scale(.5);opacity:0}}
    .page{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100dvh;padding:24px}
    .white-card{max-width:540px;width:100%;background:#fff;border-radius:16px;padding:clamp(20px,4vh,32px) clamp(18px,3vw,28px);box-shadow:0 8px 40px rgba(0,0,0,.1);transition:background .3s,box-shadow .3s}
    html.dark .white-card,.dark-theme .white-card{background:#232425;box-shadow:0 8px 40px rgba(0,0,0,.3)}
    .logo-area{text-align:center;margin-bottom:20px}
    .logo-area img{width:clamp(100px,26vw,150px);height:auto}
    .alert-pill{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:30px;margin-bottom:16px;font-size:clamp(13px,2vw,15px);font-weight:600}
    .alert-pill-warn{background:#fff3e0;color:#e65100}
    .alert-pill-danger{background:#ffebee;color:#d32f2f}
    html.dark .alert-pill-warn,.dark-theme .alert-pill-warn{background:rgba(230,81,0,.1);color:#ffb74d}
    html.dark .alert-pill-danger,.dark-theme .alert-pill-danger{background:rgba(211,47,47,.1);color:#ef5350}
    .alert-icon{width:20px;height:20px;min-width:20px;background-size:contain;background-repeat:no-repeat;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M872.7 582.6L635.2 177c-53.5-91.3-186.6-88.1-235.6 5.7L187.7 588.3c-46.8 89.7 18.2 197 119.4 197h449.4c104 0 168.8-112.9 116.2-202.7zM496.6 295.2c0-20.5 11.7-31.5 35.1-32.9 22 1.5 33.7 12.5 35.1 32.9V315l-26.4 267.9h-13.2L496.6 315v-19.8zm35.1 350.4c-19.4 0-35.1-15.8-35.1-35.1s15.8-35.1 35.1-35.1 35.1 15.8 35.1 35.1-15.8 35.1-35.1 35.1z' fill='%23ffb300'%3E%3C/path%3E%3C/svg%3E")}
    .alert-icon-danger{background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm-32 256c0-17.7 14.3-32 32-32s32 14.3 32 32v224c0 17.7-14.3 32-32 32s-32-14.3-32-32V320zm32 448c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z' fill='%23ff4d4f'%3E%3C/path%3E%3C/svg%3E")}
    .content-area{font-size:clamp(13px,1.8vw,15px);color:#555;line-height:1.7;word-break:break-all;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid rgba(0,0,0,.06)}
    html.dark .content-area,.dark-theme .content-area{color:#aaa;border-bottom-color:rgba(255,255,255,.06)}
    .url-link{color:#23a6d5;font-weight:500;word-break:break-all}
    html.dark .url-link,.dark-theme .url-link{color:#42a5f5}
    .blocked-reason{color:#ff4d4f;font-weight:600;font-size:13px;margin-top:6px}
    .tag{display:inline-block;font-size:12px;padding:3px 12px;border-radius:20px;margin-top:6px}
    .tag-warn{background:rgba(230,81,0,.08);color:#e65100}
    html.dark .tag-warn,.dark-theme .tag-warn{background:rgba(230,81,0,.1);color:#ffb74d}
    .tag-danger{background:rgba(211,47,47,.08);color:#d32f2f}
    html.dark .tag-danger,.dark-theme .tag-danger{background:rgba(211,47,47,.1);color:#ef5350}
    .footer-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
    .time-info{font-size:clamp(13px,1.8vw,15px);color:#888}
    html.dark .time-info,.dark-theme .time-info{color:#777}
    .time-num{color:#23a6d5;font-weight:700;font-size:18px;margin-right:4px}
    html.dark .time-num,.dark-theme .time-num{color:#42a5f5}
    a{text-decoration:none}
    .btn-back,.btn-back:visited{color:#888;border:1px solid #ddd;padding:8px 20px;border-radius:30px;font-size:14px;transition:all .2s;white-space:nowrap;background:#fff}
    html.dark .btn-back,.dark-theme .btn-back{color:#aaa;border-color:#444;background:#2a2a2a}
    .btn-back:hover{color:#555;border-color:#bbb;background:#f5f5f5}
    html.dark .btn-back:hover{color:#ddd;border-color:#666;background:#333}
    .btn-go,.btn-go:visited{color:#fff;background:#23a6d5;padding:8px 20px;border-radius:30px;font-size:14px;transition:all .25s;white-space:nowrap;border:none}
    html.dark .btn-go,.dark-theme .btn-go{background:#42a5f5}
    .btn-go:hover{background:#1e88e5;box-shadow:0 4px 15px rgba(35,166,213,.3)}
    html.dark .btn-go:hover,.dark-theme .btn-go:hover{box-shadow:0 4px 15px rgba(66,165,245,.3)}
    .btn-copy{background:#d32f2f}
    html.dark .btn-copy,.dark-theme .btn-copy{background:#ef5350}
    .btn-copy:hover{background:#b71c1c;box-shadow:0 4px 15px rgba(183,28,28,.3)}
    html.dark .btn-copy:hover,.dark-theme .btn-copy:hover{background:#c62828;box-shadow:0 4px 15px rgba(198,40,40,.3)}
    .ad-area{max-width:540px;width:100%;margin-top:16px;text-align:center;z-index:2;position:relative}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-card{background:#fff;border-radius:12px;padding:8px;box-shadow:0 4px 20px rgba(0,0,0,.08)}
    html.dark .ad-card,.dark-theme .ad-card{background:#232425;box-shadow:0 4px 20px rgba(0,0,0,.3)}
    .ad-area img{max-width:100%;height:auto;border-radius:8px;display:block}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#fff;background:#23a6d5;border-radius:30px;padding:4px 14px;font-size:12px;text-decoration:none;line-height:1.4;transition:all .25s;white-space:nowrap;box-shadow:0 2px 8px rgba(35,166,213,.3)}
    .ad-go-btn:hover{background:#1e88e5;box-shadow:0 4px 12px rgba(35,166,213,.4)}
    html.dark .ad-go-btn,.dark-theme .ad-go-btn{background:#42a5f5}
    html.dark .ad-go-btn:hover,.dark-theme .ad-go-btn:hover{background:#1e88e5}
    @media(max-width:480px){
        .white-card{padding:24px 18px}
        .btn-back,.btn-go{padding:8px 18px;font-size:13px}
    }
    </style>
</head>
<body>
<div class="gradient-bg"></div>
<div class="float-dot float-dot-1"></div>
<div class="float-dot float-dot-2"></div>
<div class="float-dot float-dot-3"></div>
<div class="float-dot float-dot-4"></div>
<div class="float-dot float-dot-5"></div>
<div class="page">
<div class="white-card">
    <div class="logo-area">
        <img id="img_logo" src="<?php echo esc_url($is_dark_mode ? $logoimg_dark : $logoimg); ?>" white-src="<?php echo esc_url($logoimg); ?>" dark-src="<?php echo esc_url($logoimg_dark); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>
    <div style="text-align:center">
        <div class="alert-pill<?php echo $is_unsafe ? ' alert-pill-danger' : ' alert-pill-warn'; ?>">
            <div class="alert-icon<?php echo $is_unsafe ? ' alert-icon-danger' : ''; ?>"></div>
            <?php
                if ($is_nonce_invalid) {
                    echo '访问验证失败';
                } elseif ($blacklist_match) {
                    echo '链接存在安全风险';
                } else {
                    echo '请注意账号和财产安全';
                }
            ?>
        </div>
    </div>
    <div class="content-area">
        <?php if ($is_nonce_invalid): ?>
            链接验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
            <?php if ($blocked_reason): ?>
                <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
            <?php endif; ?>
        <?php elseif ($blacklist_match): ?>
            您即将访问：<span class="url-link"><?php echo esc_html($url); ?></span>
            <br><span class="tag tag-danger">安全警告</span> 该网址可能存在违反法律规定的内容或安全风险，为保护您的权益，已拦截此次跳转。
        <?php else: ?>
            您即将离开<?php echo esc_html(get_bloginfo('name')); ?>，去往：<span class="url-link"><?php echo esc_html($url); ?></span>
            <br><span class="tag tag-warn">注意</span> 该网址与本站无关，本站不负任何责任或义务
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
                <a class="btn-go btn-copy" id="copyBtn" href="javascript:;" rel="external nofollow">复制链接</a>
            <?php elseif (!$is_nonce_invalid): ?>
                <a class="btn-go" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if ($ad_enabled && $ad_image): ?>
<div class="ad-area">
    <div class="ad-card">
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
