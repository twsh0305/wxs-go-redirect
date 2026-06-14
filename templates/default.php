<?php
/**
 * Template Name: 默认模板
 * Description: 简洁风格跳转提示页，支持子比主题日夜模式自适应
 * Thumbnail: assets/img/default.png
 * Author: 天无神话
 * Version: 1.0.0
 *
 * 变量由 go-page.php 传入：
 *   $url, $title, $logoimg, $logoimg_dark, $countdown,
 *   $is_blocked, $is_nonce_invalid, $blacklist_match, $blocked_reason
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
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;height:100%}
    #loading{position:fixed;bottom:0;left:50%;transform:translateX(-50%);z-index:1;pointer-events:none}
    html.dark .go-to,.dark-theme.go-to{background:#1b1d1f}
    html.dark .loading-info,.dark-theme .loading-info{background:#2a2c2e;box-shadow:0 15px 20px rgba(0,0,0,.4)}
    html.dark .loading-topic,.dark-theme .loading-topic{border-bottom-color:rgba(136,136,136,.15);color:#c8c9cc}
    html.dark .taxt-auto,.dark-theme .taxt-auto{color:#a0a2a5}
    html.dark .loader .getting-there,html.dark .loader .binary,.dark-theme .loader .getting-there,.dark-theme .loader .binary{color:#bbb}
    .go-to{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100dvh;background:#e8eaec;padding:16px}
    .loading-content{position:relative;max-width:1200px;width:100%;margin:0 auto;margin-top:-5vh;z-index:2}
    .flex{display:flex}.flex-center{align-items:center}.flex-end{display:flex;justify-content:flex-end}.flex-fill{flex:1 1 auto !important}
    .logo-img{text-align:center}
    .logo-img img{width:clamp(120px, 30vw, 200px);height:auto;margin-bottom:16px}
    .loading-info{padding:clamp(14px, 3vw, 20px);background:#fff;border-radius:10px;box-shadow:0 15px 20px rgba(18,19,20,.2)}
    .loading-tip{background:rgba(255,158,77,.1);border-radius:6px;padding:6px 10px}
    .loading-text{color:#b22e12;font-weight:bold;font-size:clamp(13px, 2vw, 15px)}
    .loading-topic{padding:16px 0;border-bottom:1px solid rgba(136,136,136,.2);margin-bottom:16px;font-size:clamp(12px, 1.8vw, 14px);word-break:break-all;line-height:1.6}
    a{text-decoration:none}
    .loading-btn,.loading-btn:active,.loading-btn:visited{color:#fc5531;border-radius:5px;border:1px solid #fc5531;padding:6px 16px;transition:.3s;white-space:nowrap;font-size:14px}
    .loading-btn:hover{color:#fff;background:#fc5531;box-shadow:0 15px 15px -10px rgba(184,56,25,0.8)}
    .loading-url{color:#fc5531;word-break:break-all}
    .taxt-auto{color:#787a7d;font-size:clamp(12px, 1.8vw, 14px)}
    .auto-second{color:#fc5531;font-size:16px;margin-right:5px;font-weight:bold}
    .safe-tip{max-width:580px;width:100%;margin:0 auto 20px auto}
    .warning-ico{width:26px;height:22px;min-width:26px;margin-right:6px;background-size:contain;background-repeat:no-repeat;background-image:url("data:image/svg+xml,%3Csvg class='icon' viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M872.7 582.6L635.2 177c-53.5-91.3-186.6-88.1-235.6 5.7L187.7 588.3c-46.8 89.7 18.2 197 119.4 197h449.4c104 0 168.8-112.9 116.2-202.7zM496.6 295.2c0-20.5 11.7-31.5 35.1-32.9 22 1.5 33.7 12.5 35.1 32.9V315l-26.4 267.9h-13.2L496.6 315v-19.8zm35.1 350.4c-19.4 0-35.1-15.8-35.1-35.1s15.8-35.1 35.1-35.1 35.1 15.8 35.1 35.1-15.8 35.1-35.1 35.1z' fill='%23fc9153'%3E%3C/path%3E%3C/svg%3E")}
    .warning-ico-danger{background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm-32 256c0-17.7 14.3-32 32-32s32 14.3 32 32v224c0 17.7-14.3 32-32 32s-32-14.3-32-32V320zm32 448c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z' fill='%23ff4d4f'%3E%3C/path%3E%3C/svg%3E")}
    .home-btn,.home-btn:active,.home-btn:visited{color:#2563eb;border-radius:5px;border:1px solid #2563eb;padding:6px 16px;margin-right:10px;transition:.3s;white-space:nowrap;font-size:14px}
    .home-btn:hover{color:#fff;background:#2563eb;box-shadow:0 10px 15px -5px rgba(37,99,235,0.4)}
    .btn-group{display:flex;align-items:center;flex-wrap:wrap;gap:8px}
    .blocked-reason{color:#ff4d4f;font-weight:bold;font-size:13px;margin-top:5px}
    .container{max-width:1200px;margin:20px auto 0;padding:0 16px;box-sizing:border-box;text-align:center}
    .apd-footer .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .apd-footer img{max-width:100%;height:auto;border-radius:6px}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#fc5531;border:1px solid #fc5531;border-radius:5px;padding:4px 12px;font-size:12px;text-decoration:none;background:#fff;line-height:1.4;transition:.2s;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.1)}
    .ad-go-btn:hover{background:#fc5531;color:#fff}
    html.dark .ad-go-btn,.dark-theme .ad-go-btn{background:#2a2c2e;color:#fc5531}
    html.dark .ad-go-btn:hover,.dark-theme .ad-go-btn:hover{background:#fc5531;color:#fff}
    .loader{width:130px;height:170px;position:relative}
    .loader::before,.loader::after{content:"";width:0;height:0;position:absolute;bottom:30px;left:15px;z-index:1;border-left:50px solid transparent;border-right:50px solid transparent;border-bottom:20px solid rgba(107,122,131,.15);transform:scale(0);transition:all 0.2s ease}
    .loader::after{border-right:15px solid transparent;border-bottom:20px solid rgba(102,114,121,.2)}
    .loader .getting-there{width:120%;text-align:center;position:absolute;bottom:0;left:-7%;font-family:"Lato";font-size:12px;letter-spacing:2px;color:#555}
    .loader .binary{width:100%;height:140px;display:block;color:#555;position:absolute;top:0;left:15px;z-index:2;overflow:hidden}
    .loader .binary::before,.loader .binary::after{font-family:"Lato";font-size:24px;position:absolute;top:0;left:0;opacity:0}
    .loader .binary:nth-child(1)::before{content:"0";animation:a 1.1s linear infinite}
    .loader .binary:nth-child(1)::after{content:"0";animation:b 1.3s linear infinite}
    .loader .binary:nth-child(2)::before{content:"1";animation:c 0.9s linear infinite}
    .loader .binary:nth-child(2)::after{content:"1";animation:d 0.7s linear infinite}
    .loader.JS_on::before,.loader.JS_on::after{transform:scale(1)}
    @keyframes a{0%{transform:translate(30px,0) rotate(30deg);opacity:0}100%{transform:translate(30px,150px) rotate(-50deg);opacity:1}}
    @keyframes b{0%{transform:translate(50px,0) rotate(-40deg);opacity:0}100%{transform:translate(40px,150px) rotate(80deg);opacity:1}}
    @keyframes c{0%{transform:translate(70px,0) rotate(10deg);opacity:0}100%{transform:translate(60px,150px) rotate(70deg);opacity:1}}
    @keyframes d{0%{transform:translate(30px,0) rotate(-50deg);opacity:0}100%{transform:translate(45px,150px) rotate(30deg);opacity:1}}
    html.dark .loading-tip,.dark-theme .loading-tip{background:rgba(255,158,77,.08)}
    @media (max-width:480px){
        .loading-info{padding:12px}
        .loading-tip{padding:5px 8px}
        .loading-topic{padding:12px 0;margin-bottom:12px}
        .home-btn,.loading-btn{padding:8px 14px;font-size:13px}
        .btn-group{justify-content:flex-end}
        .loading-content{margin-top:-2vh}
    }
    </style>
</head>
<body class="go-to<?php if ($is_dark_mode) echo ' dark-theme'; ?>">
    <div id="loading">
        <div class="loader JS_on">
            <span class="binary"></span>
            <span class="binary"></span>
            <span class="getting-there">LOADING STUFF...</span>
        </div>
    </div>
<div class="loading-content">
    <div class="safe-tip">
        <div class="logo-img">
            <img id="img_logo" src="<?php echo esc_url($is_dark_mode ? $logoimg_dark : $logoimg); ?>" white-src="<?php echo esc_url($logoimg); ?>" dark-src="<?php echo esc_url($logoimg_dark); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        </div>
        <div class="loading-info">
            <div class="flex flex-center loading-tip">
                <div class="warning-ico<?php echo $is_unsafe ? ' warning-ico-danger' : ''; ?>"></div>
                <div class="loading-text"><?php
                    if ($is_nonce_invalid) {
                        echo '访问验证失败';
                    } elseif ($blacklist_match) {
                        echo '此链接存在安全风险';
                    } else {
                        echo '请注意您的账号和财产安全';
                    }
                ?></div>
            </div>
            <div class="loading-topic">
                <?php if ($is_nonce_invalid): ?>
                    链接验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
                    <?php if ($blocked_reason): ?>
                        <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
                    <?php endif; ?>
                <?php elseif ($blacklist_match): ?>
                    您即将访问的链接：<span class="loading-url"><?php echo esc_html($url); ?></span>
                    <br>
                    <span style="color:#f55d49">【安全警告】</span>该网址可能存在违反法律规定的内容或安全风险，为保护您的权益，已拦截此次跳转。
                <?php else: ?>
                    您即将离开<?php echo esc_html(get_bloginfo('name')); ?>，去往：<span class="loading-url"><?php echo esc_html($url); ?></span>
                    <br>
                    <span style="color:#f55d49">【注意】</span>该网址与<?php echo esc_html(get_bloginfo('name')); ?>无关，本站不负任何责任或义务
                <?php endif; ?>
            </div>
            <div class="flex flex-center">
                <div class="taxt-auto">
                    <?php if ($is_nonce_invalid): ?>
                        <span class="auto-second">验证失败</span>请点击右侧按钮返回刷新原页面
                    <?php elseif ($blacklist_match): ?>
                        <span class="auto-second">已拦截</span>
                    <?php else: ?>
                        <?php printf(__(' %s 秒后自动跳转'), '<span id="time" class="auto-second">' . $countdown . '</span>'); ?>
                    <?php endif; ?>
                </div>
                <div class="flex-fill"></div>
                <div class="btn-group">
                    <a class="home-btn" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
                    <?php if ($blacklist_match): ?>
                        <a class="loading-btn" id="copyBtn" href="javascript:;" rel="external nofollow">复制链接</a>
                    <?php elseif (!$is_nonce_invalid): ?>
                        <a class="loading-btn" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($ad_enabled && $ad_image): ?>
    <div class="container apd apd-footer">
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

<?php if (!$is_unsafe): ?>
<script type="text/javascript">
(function(){
    var seconds = <?php echo (int)$countdown; ?>;
    var el = document.getElementById('time');
    var url = <?php echo json_encode($url); ?>;
    function tick(){
        if(seconds > 0){
            seconds--;
            el.innerHTML = seconds;
            setTimeout(tick, 1000);
        } else {
            window.location.href = url;
        }
    }
    tick();
    // 15秒后尝试关闭页面
    setTimeout(function(){
        window.opener = null;
        window.close();
    }, 15000);
})();
</script>
<?php endif; ?>

<?php if ($blacklist_match): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var copyBtn = document.getElementById('copyBtn');
    if(!copyBtn) return;
    copyBtn.addEventListener('click', function(){
        var urlText = document.querySelector('.loading-url');
        if(urlText && navigator.clipboard){
            navigator.clipboard.writeText(urlText.textContent).then(function(){
                alert('已复制链接');
            });
        }
    });
});
</script>
<?php endif; ?>
</body>
</html>
