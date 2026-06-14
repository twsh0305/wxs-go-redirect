<?php
/**
 * Template Name: 子比原生
 * Description: 使用子比主题自带样式和CSS变量的跳转中转页，完美融入Zibll设计语言，自动适配日夜模式
 * Thumbnail: assets/img/zibll.png
 * Author: 天无神话
 * Version: 1.0.0
 * Sticker: true
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

// 跳转方式：仅非拦截状态且选择「子比原生跳转」时使用原始 go.php 风格
$zibll_mode = wxs_go_option('zibll_mode', 'card');
$use_original_style = ($zibll_mode === 'original' && !$is_unsafe);
$ad_image   = wxs_go_option('ad_image', '');
$ad_link    = wxs_go_option('ad_link', '');
$ad_alt     = wxs_go_option('ad_alt', '广告');

// 加载子比主题 CSS，获取全部 CSS 变量和工具类
$zibll_css_url = get_template_directory_uri() . '/css/main.min.css';
?><!DOCTYPE html>
<html>
<head>
    <script>
    // 日夜模式提前检测，防止白屏闪烁
    (function(){
        var m=document.cookie.match(/(?:^|;\s*)theme_mode=([^;]*)/);
        var mode=m?decodeURIComponent(m[1]):'';
        if(mode==='dark-theme'){
            document.documentElement.classList.add('dark');
            document.documentElement.classList.add('dark-theme');
        }
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
    <link rel="stylesheet" href="<?php echo esc_url($zibll_css_url); ?>">
    <style type="text/css">
    /* 仅补充页面级布局，卡片、按钮、文字等全部复用子比主题样式和CSS变量 */
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;height:100%}
    a{text-decoration:none}
    .go-zibll-page{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100dvh;padding:16px;background:var(--body-bg-color)}
    .go-zibll-logo{text-align:center;margin-bottom:16px}
    .go-zibll-logo img{width:clamp(100px,26vw,160px);height:auto}
    .go-zibll-card{max-width:560px;width:100%}
    .go-zibll-card>.box-body{padding:clamp(16px,3vw,24px)}
    /* 提示条：橙色/红色背景，使用CSS变量适配日夜模式 */
    .go-zibll-tip{display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:var(--main-radius);background:rgba(255,158,77,.1)}
    html.dark .go-zibll-tip,.dark-theme .go-zibll-tip{background:rgba(255,158,77,.06)}
    .go-zibll-tip-danger{background:rgba(255,77,79,.08)}
    html.dark .go-zibll-tip-danger,.dark-theme .go-zibll-tip-danger{background:rgba(255,77,79,.04)}
    /* 提示图标 */
    .go-zibll-tip-icon{width:20px;height:18px;min-width:20px;background-size:contain;background-repeat:no-repeat;background-image:url("data:image/svg+xml,%3Csvg class='icon' viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M872.7 582.6L635.2 177c-53.5-91.3-186.6-88.1-235.6 5.7L187.7 588.3c-46.8 89.7 18.2 197 119.4 197h449.4c104 0 168.8-112.9 116.2-202.7zM496.6 295.2c0-20.5 11.7-31.5 35.1-32.9 22 1.5 33.7 12.5 35.1 32.9V315l-26.4 267.9h-13.2L496.6 315v-19.8zm35.1 350.4c-19.4 0-35.1-15.8-35.1-35.1s15.8-35.1 35.1-35.1 35.1 15.8 35.1 35.1-15.8 35.1-35.1 35.1z' fill='%23fc9153'%3E%3C/path%3E%3C/svg%3E")}
    .go-zibll-tip-icon-danger{background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm-32 256c0-17.7 14.3-32 32-32s32 14.3 32 32v224c0 17.7-14.3 32-32 32s-32-14.3-32-32V320zm32 448c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z' fill='%23ff4d4f'%3E%3C/path%3E%3C/svg%3E")}
    /* 内容区域 */
    .go-zibll-content{padding:14px 0;margin-bottom:14px;border-bottom:1px solid var(--main-border-color);line-height:1.7;word-break:break-all}
    .go-zibll-url{color:var(--theme-color);font-weight:500;word-break:break-all}
    .go-zibll-blocked-reason{color:var(--focus-color);font-weight:bold;font-size:13px;margin-top:6px}
    /* 底部操作区 */
    .go-zibll-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
    .go-zibll-time{font-size:clamp(12px,1.7vw,14px);color:var(--muted-2-color)}
    .go-zibll-time-num{color:var(--theme-color);font-weight:bold;font-size:16px;margin-right:4px}
    .go-zibll-btns{display:flex;align-items:center;gap:10px}
    /* 广告区 */
    .go-zibll-ad{max-width:560px;width:100%;margin-top:16px;text-align:center}
    .go-zibll-ad-inner{position:relative;display:inline-block;line-height:0;padding:8px}
    .go-zibll-ad img{max-width:100%;height:auto;border-radius:var(--main-radius)}
    .go-zibll-ad-btn{position:absolute;bottom:12px;right:12px}
    @media(max-width:480px){
        .go-zibll-page{padding:12px}
        .go-zibll-card>.box-body{padding:14px}
    }
    </style>
</head>
<body class="go-zibll<?php if ($is_dark_mode) echo ' dark-theme'; ?>">
<?php if ($use_original_style): ?>
    <!-- 子比原生跳转风格：复制自 go.php 原版样式 -->
    <style type="text/css">
        body {
            background: #fff
        }
        .qjdh_no6 {
            transform: scale(1) translateY(-30px);
        }
        .qjdh_no6>div:nth-child(2) {
            -webkit-animation-delay: -.4s;
            animation-delay: -.4s
        }
        .qjdh_no6>div:nth-child(3) {
            -webkit-animation-delay: -.2s;
            animation-delay: -.2s
        }
        .qjdh_no6>div {
            position: absolute;
            top: 0;
            left: -30px;
            margin: 2px;
            margin: 0;
            width: 15px;
            width: 60px;
            height: 15px;
            height: 60px;
            border-radius: 100%;
            background-color: #ff3cb2;
            opacity: 0;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
            -webkit-animation: ball-scale-multiple 1s .5s linear infinite;
            animation: ball-scale-multiple 1s .5s linear infinite
        }
        @-webkit-keyframes ball-scale-multiple {
            0% {
                opacity: 0;
                -webkit-transform: scale(0);
                transform: scale(0)
            }
            5% {
                opacity: 1
            }
            to {
                -webkit-transform: scale(1);
                transform: scale(1)
            }
        }
        @keyframes ball-scale-multiple {
            0%,
            to {
                opacity: 0
            }
            0% {
                -webkit-transform: scale(0);
                transform: scale(0)
            }
            5% {
                opacity: 1
            }
            to {
                opacity: 0;
                -webkit-transform: scale(1);
                transform: scale(1)
            }
        }
        @keyframes ball-s {
            0%,
            to {
                opacity: 0;
                transform: scale(0)
            }
            to {
                opacity: 1;
                transform: scale(1)
            }
        }
        @keyframes ball-s2 {
            0% {
                opacity: 0;
            }
            30% {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
    <div style="position:fixed;animation:ball-s .5s 0s ease-out;top:-60px;left:0;bottom:0;right:0;display:flex;align-items:center;justify-content:center">
        <div class="qjdh_no6">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
    <div style="position:fixed;top:60px;left:0;bottom:0;color: #f156b4;animation:ball-s2 .8s cubic-bezier(0.36, 0.29, 0.62, 1.36);right:0;display:flex;align-items:center;justify-content:center;font-size:15px;"><?php echo esc_html($title); ?></div>
    <script>
        function link_jump() {
            location.href = "<?php echo $url; ?>";
        }
        setTimeout(link_jump, 1500);
        setTimeout(function() {
            window.opener = null;
            window.close();
        }, 15000);
    </script>
<?php else: ?>
<div class="go-zibll-page">
    <div class="go-zibll-logo">
        <img id="img_logo" src="<?php echo esc_url($is_dark_mode ? $logoimg_dark : $logoimg); ?>" white-src="<?php echo esc_url($logoimg); ?>" dark-src="<?php echo esc_url($logoimg_dark); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>
    <div class="go-zibll-card zib-widget main-shadow">
        <div class="box-body">
            <div class="go-zibll-tip<?php echo $is_unsafe ? ' go-zibll-tip-danger' : ''; ?>">
                <div class="go-zibll-tip-icon<?php echo $is_unsafe ? ' go-zibll-tip-icon-danger' : ''; ?>"></div>
                <span class="em14 font-bold"><?php
                    if ($is_nonce_invalid) {
                        echo '验证失败';
                    } elseif ($blacklist_match) {
                        echo '安全风险警告';
                    } else {
                        echo '请注意账号和财产安全';
                    }
                ?></span>
            </div>
            <div class="go-zibll-content muted-2-color em12">
                <?php if ($is_nonce_invalid): ?>
                    链接验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
                    <?php if ($blocked_reason): ?>
                        <div class="go-zibll-blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
                    <?php endif; ?>
                <?php elseif ($blacklist_match): ?>
                    您即将访问：<span class="go-zibll-url"><?php echo esc_html($url); ?></span>
                    <br><span class="c-red font-bold">【安全警告】</span>该网址可能存在违反法律规定的内容或安全风险，为保护您的权益，已拦截此次跳转。
                <?php else: ?>
                    您即将离开<span class="key-color"><?php echo esc_html(get_bloginfo('name')); ?></span>，去往：<span class="go-zibll-url"><?php echo esc_html($url); ?></span>
                    <br><span class="c-yellow font-bold">【注意】</span>该网址与本站无关，本站不负任何责任或义务
                <?php endif; ?>
            </div>
            <div class="go-zibll-footer">
                <div class="go-zibll-time">
                    <?php if ($is_nonce_invalid): ?>
                        <span class="go-zibll-time-num">!</span>请返回刷新原页面
                    <?php elseif ($blacklist_match): ?>
                        <span class="go-zibll-time-num">X</span>已拦截
                    <?php else: ?>
                        <span id="time" class="go-zibll-time-num"><?php echo (int)$countdown; ?></span>秒后自动跳转
                    <?php endif; ?>
                </div>
                <div class="go-zibll-btns">
                    <a class="but jb-blue radius" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
                    <?php if ($blacklist_match): ?>
                        <a class="but jb-red radius" id="copyBtn" href="javascript:;" rel="external nofollow">复制链接</a>
                    <?php elseif (!$is_nonce_invalid): ?>
                        <a class="but c-blue radius" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($ad_enabled && $ad_image): ?>
    <div class="go-zibll-ad">
        <div class="zib-widget main-shadow go-zibll-ad-inner">
            <?php if ($ad_link): ?>
                <a href="<?php echo esc_url($ad_link); ?>" target="_blank"><img src="<?php echo esc_url($ad_image); ?>" alt="<?php echo esc_attr($ad_alt); ?>"></a>
                <a class="but c-blue radius em09 go-zibll-ad-btn" href="<?php echo esc_url($ad_link); ?>" target="_blank">前往</a>
            <?php else: ?>
                <img src="<?php echo esc_url($ad_image); ?>" alt="<?php echo esc_attr($ad_alt); ?>">
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- 日夜模式 logo 切换 -->
<script>
document.addEventListener('DOMContentLoaded',function(){
    var isDark=document.documentElement.classList.contains('dark')||document.documentElement.classList.contains('dark-theme')||document.body.classList.contains('dark-theme');
    var logo=document.getElementById('img_logo');
    if(logo&&isDark){
        var darkSrc=logo.getAttribute('dark-src');
        if(darkSrc)logo.src=darkSrc;
    }
});
</script>

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
    // 15秒后尝试关闭页面
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
        var urlText=document.querySelector('.go-zibll-url');
        if(urlText&&navigator.clipboard){
            navigator.clipboard.writeText(urlText.textContent).then(function(){alert('已复制链接');});
        }
    });
});
</script>
<?php endif; ?>
<?php endif; ?>
</body>
</html>
