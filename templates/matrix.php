<?php
/**
 * Template Name: 黑客帝国
 * Description: 经典黑客帝国风格跳转提示页，绿色代码雨与数字矩阵背景，科技感十足
 * Thumbnail: assets/img/matrix.png
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
<html>
<head>
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
    html,body{min-height:100dvh}
    body{font-family:"Courier New",Consolas,"SF Mono",monospace;line-height:1.6;background:#0d0d0d}
    .page{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100dvh;padding:24px;position:relative;overflow:hidden}
    /* grid background */
    .grid-bg{position:fixed;inset:0;z-index:0;background-image:
        linear-gradient(rgba(0,255,136,.03) 1px,transparent 1px),
        linear-gradient(90deg,rgba(0,255,136,.03) 1px,transparent 1px);
        background-size:40px 40px;pointer-events:none}
    #matrix-canvas{position:fixed;inset:0;z-index:0;pointer-events:none}
    .glow-line{position:fixed;z-index:1;height:2px;width:200%;left:-50%;background:linear-gradient(90deg,transparent,#00ff88,transparent);opacity:.15;pointer-events:none;animation:scanline 4s linear infinite}
    .glow-line-2{animation-delay:2s;top:60%;opacity:.08}
    @keyframes scanline{0%{top:-5%}100%{top:105%}}
    .card{position:relative;z-index:2;max-width:520px;width:100%;background:rgba(13,13,13,.85);border:1px solid rgba(0,255,136,.2);border-radius:4px;padding:clamp(18px,3vh,28px);box-shadow:0 0 30px rgba(0,255,136,.08),inset 0 0 30px rgba(0,255,136,.02)}
    .card::before{content:'';position:absolute;top:-1px;left:20px;right:20px;height:1px;background:linear-gradient(90deg,transparent,#00ff88,transparent);opacity:.5}
    .logo-area{text-align:center;margin-bottom:20px}
    .logo-area img{width:clamp(100px,26vw,150px);height:auto;filter:drop-shadow(0 0 12px rgba(0,255,136,.3))}
    .alert-bar{display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(0,255,136,.06);border:1px solid rgba(0,255,136,.15);border-radius:2px;margin-bottom:16px}
    .alert-bar-danger{background:rgba(255,77,79,.08);border-color:rgba(255,77,79,.25);box-shadow:0 0 15px rgba(255,77,79,.1)}
    .alert-icon{width:22px;height:22px;min-width:22px;background-size:contain;background-repeat:no-repeat;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M872.7 582.6L635.2 177c-53.5-91.3-186.6-88.1-235.6 5.7L187.7 588.3c-46.8 89.7 18.2 197 119.4 197h449.4c104 0 168.8-112.9 116.2-202.7zM496.6 295.2c0-20.5 11.7-31.5 35.1-32.9 22 1.5 33.7 12.5 35.1 32.9V315l-26.4 267.9h-13.2L496.6 315v-19.8zm35.1 350.4c-19.4 0-35.1-15.8-35.1-35.1s15.8-35.1 35.1-35.1 35.1 15.8 35.1 35.1-15.8 35.1-35.1 35.1z' fill='%2300ff88'%3E%3C/path%3E%3C/svg%3E")}
    .alert-icon-danger{background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 1024 1024' xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Cpath d='M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm-32 256c0-17.7 14.3-32 32-32s32 14.3 32 32v224c0 17.7-14.3 32-32 32s-32-14.3-32-32V320zm32 448c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48z' fill='%23ff4d4f'%3E%3C/path%3E%3C/svg%3E")}
    .alert-label{font-size:clamp(13px,2vw,15px);font-weight:700;color:#00ff88;text-shadow:0 0 8px rgba(0,255,136,.4);letter-spacing:1px}
    .alert-label-danger{color:#ff4d4f;text-shadow:0 0 8px rgba(255,77,79,.4)}
    .divider{height:1px;background:linear-gradient(90deg,transparent,rgba(0,255,136,.15),transparent);margin:16px 0}
    .content-area{font-size:clamp(12px,1.7vw,14px);color:rgba(0,255,136,.7);line-height:1.7;word-break:break-all;margin-bottom:4px}
    .url-link{color:#00ff88;font-weight:500;word-break:break-all;text-shadow:0 0 6px rgba(0,255,136,.3)}
    .blocked-reason{color:#ff4d4f;font-weight:600;font-size:13px;margin-top:6px;text-shadow:0 0 6px rgba(255,77,79,.3)}
    .tag{display:inline-block;font-size:12px;padding:2px 10px;border-radius:2px;margin-top:6px;border:1px solid rgba(0,255,136,.3);color:#00ff88;text-shadow:0 0 4px rgba(0,255,136,.2)}
    .tag-danger{border-color:rgba(255,77,79,.4);color:#ff4d4f;text-shadow:0 0 4px rgba(255,77,79,.3)}
    .footer-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
    .time-info{font-size:clamp(12px,1.7vw,14px);color:rgba(0,255,136,.6)}
    .time-num{color:#00ff88;font-weight:700;font-size:18px;margin-right:4px;text-shadow:0 0 10px rgba(0,255,136,.5)}
    a{text-decoration:none}
    .btn-back,.btn-back:visited{color:rgba(0,255,136,.7);border:1px solid rgba(0,255,136,.3);padding:8px 20px;border-radius:2px;font-size:14px;transition:all .2s;white-space:nowrap;background:transparent}
    .btn-back:hover{color:#00ff88;border-color:#00ff88;box-shadow:0 0 12px rgba(0,255,136,.15)}
    .btn-go,.btn-go:visited{color:#00ff88;border:1px solid #00ff88;padding:8px 20px;border-radius:2px;font-size:14px;transition:all .25s;white-space:nowrap;background:rgba(0,255,136,.05);text-shadow:0 0 6px rgba(0,255,136,.3)}
    .btn-go:hover{background:rgba(0,255,136,.15);box-shadow:0 0 20px rgba(0,255,136,.2),inset 0 0 10px rgba(0,255,136,.05)}
    .btn-go-danger{color:#ff4d4f;border-color:#ff4d4f;text-shadow:0 0 6px rgba(255,77,79,.3);background:rgba(255,77,79,.05)}
    .btn-go-danger:hover{background:rgba(255,77,79,.15);box-shadow:0 0 20px rgba(255,77,79,.2)}
    .ad-area{max-width:520px;width:100%;margin-top:16px;text-align:center;z-index:2;position:relative}
    .ad-img-wrap{position:relative;display:inline-block;line-height:0}
    .ad-area img{max-width:100%;height:auto;border-radius:4px;border:1px solid rgba(0,255,136,.1)}
    .ad-go-btn,.ad-go-btn:visited{position:absolute;bottom:8px;right:8px;color:#00ff88;border:1px solid rgba(0,255,136,.4);border-radius:2px;padding:4px 12px;font-size:12px;text-decoration:none;line-height:1.4;transition:all .2s;white-space:nowrap;background:rgba(13,13,13,.8);text-shadow:0 0 6px rgba(0,255,136,.3);box-shadow:0 0 10px rgba(0,255,136,.1)}
    .ad-go-btn:hover{background:rgba(0,255,136,.15);border-color:#00ff88;box-shadow:0 0 20px rgba(0,255,136,.2)}
    /* pulse animation for countdown */
    @keyframes pulse-glow{0%,100%{text-shadow:0 0 10px rgba(0,255,136,.5)}50%{text-shadow:0 0 20px rgba(0,255,136,.8)}}
    .time-num-pulse{animation:pulse-glow 1s ease-in-out infinite}
    @media(max-width:480px){
        .page{padding:16px}
        .card{padding:clamp(14px,2.5vh,20px) clamp(12px,2.5vw,16px)}
        .btn-back,.btn-go{padding:8px 16px;font-size:13px}
    }
    </style>
</head>
<body>
<div class="grid-bg"></div>
<div class="glow-line"></div>
<div class="glow-line glow-line-2"></div>
<canvas id="matrix-canvas"></canvas>
<div class="page">
<div class="card">
    <div class="logo-area">
        <img id="img_logo" src="<?php echo esc_url($logoimg_dark ? $logoimg_dark : $logoimg); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
    </div>
    <div class="alert-bar<?php echo $is_unsafe ? ' alert-bar-danger' : ''; ?>">
        <div class="alert-icon<?php echo $is_unsafe ? ' alert-icon-danger' : ''; ?>"></div>
        <div class="alert-label<?php echo $is_unsafe ? ' alert-label-danger' : ''; ?>"><?php
            if ($is_nonce_invalid) {
                echo '[验证失败]';
            } elseif ($blacklist_match) {
                echo '[安全风险]';
            } else {
                echo '[安全提示]';
            }
        ?></div>
    </div>
    <div class="divider"></div>
    <div class="content-area">
        <?php if ($is_nonce_invalid): ?>
            链接验证已过期或来源不合法，请返回原页面刷新后重新点击链接。
            <?php if ($blocked_reason): ?>
                <div class="blocked-reason"><?php echo esc_html($blocked_reason); ?></div>
            <?php endif; ?>
        <?php elseif ($blacklist_match): ?>
            您即将访问：<span class="url-link"><?php echo esc_html($url); ?></span>
            <br><span class="tag tag-danger">WARNING</span> 该网址可能存在违反法律规定的内容或安全风险，为保护您的权益，已拦截此次跳转。
        <?php else: ?>
            您即将离开<?php echo esc_html(get_bloginfo('name')); ?>，去往：<span class="url-link"><?php echo esc_html($url); ?></span>
            <br><span class="tag">NOTE</span> 该网址与本站无关，本站不负任何责任或义务
        <?php endif; ?>
    </div>
    <div class="divider"></div>
    <div class="footer-row">
        <div class="time-info">
            <?php if ($is_nonce_invalid): ?>
                <span class="time-num">ERROR</span>请返回刷新原页面
            <?php elseif ($blacklist_match): ?>
                <span class="time-num">BLOCKED</span>已拦截
            <?php else: ?>
                <span id="time" class="time-num time-num-pulse"><?php echo (int)$countdown; ?></span>s 后跳转
            <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px">
            <a class="btn-back" href="#" onclick="if(document.referrer){location.href=document.referrer;}else{history.back();};return false;" rel="external nofollow">返回</a>
            <?php if ($blacklist_match): ?>
                <a class="btn-go btn-go-danger" id="copyBtn" href="javascript:;" rel="external nofollow">复制链接</a>
            <?php elseif (!$is_nonce_invalid): ?>
                <a class="btn-go" href="<?php echo esc_url($url); ?>" rel="external nofollow">继续访问</a>
            <?php endif; ?>
        </div>
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
</div>

<script>
(function(){
    var canvas=document.getElementById('matrix-canvas');
    var ctx=canvas.getContext('2d');
    var chars='ANCDEFGHIJKLMNOPQISTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz<>/\\|{}[]#@$%&*';
    var fontSize=14;
    var columns, drops;
    var hue=120;

    function resize(){
        canvas.width=window.innerWidth;
        canvas.height=window.innerHeight;
        columns=Math.floor(canvas.width/fontSize);
        drops=[];
        for(var i=0;i<columns;i++){
            drops[i]=Math.random()*-100;
        }
    }
    resize();
    window.addEventListener('resize',resize);

    function draw(){
        ctx.fillStyle='rgba(0,0,0,.05)';
        ctx.fillRect(0,0,canvas.width,canvas.height);
        ctx.font=fontSize+'px "Courier New",monospace';
        for(var i=0;i<drops.length;i++){
            var ch=chars[Math.floor(Math.random()*chars.length)];
            var x=i*fontSize;
            var y=drops[i]*fontSize;
            var alpha=.15+Math.random()*.35;
            ctx.fillStyle='hsla('+hue+',100%,50%,'+alpha+')';
            ctx.fillText(ch,x,y);
            if(y>canvas.height&&Math.random()>.975){
                drops[i]=0;
            }
            drops[i]++;
        }
    }
    setInterval(draw,50);
})();
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
