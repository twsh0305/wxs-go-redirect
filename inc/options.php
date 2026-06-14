<?php
/**
 * 外链重定向增强 - CSF设置页面
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', 'wxs_go_admin_csf_options', 99);

/**
 * 远程订阅状态HTML
 */
function wxs_go_remote_status_html() {
    $status = get_option('wxs_go_remote_status', array());
    $html = '<div style="padding:8px 12px;background:#f8f9fa;border-radius:4px;">';

    if (empty($status)) {
        $html .= '<span style="color:#666;">尚未执行过远程更新。</span>';
    } else {
        $time_str = isset($status['time']) ? date('Y-m-d H:i:s', $status['time']) : '未知';
        $is_ok    = (isset($status['status']) && $status['status'] === 'success');
        $color    = $is_ok ? '#28a745' : '#dc3545';
        $icon     = $is_ok ? '&#10003;' : '&#10007;';
        $msg      = isset($status['msg']) ? esc_html($status['msg']) : '';

        $html .= sprintf(
            '<span style="color:%s;font-weight:bold;">%s %s</span><br><small>上次更新：%s</small>',
            $color, $icon, $msg, $time_str
        );
    }

    // 手动更新按钮
    $nonce = wp_create_nonce('wxs_go_update_remote');
    $html .= '<br><br><button type="button" class="button button-secondary" id="wxs-go-manual-update">立即更新</button>';
    $html .= '<span id="wxs-go-update-result" style="margin-left:10px;"></span>';
    $html .= '</div>';
    $html .= '<script>
    (function(){
        var btn = document.getElementById("wxs-go-manual-update");
        if(!btn) return;
        btn.addEventListener("click", function(){
            btn.disabled = true;
            var result = document.getElementById("wxs-go-update-result");
            result.textContent = "更新中...";
            var xhr = new XMLHttpRequest();
            xhr.open("POST", ajaxurl);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function(){
                btn.disabled = false;
                try {
                    var resp = JSON.parse(xhr.responseText);
                    result.textContent = resp.success ? resp.data.msg : (resp.data.msg || "更新失败");
                    result.style.color = resp.success ? "#28a745" : "#dc3545";
                } catch(e) {
                    result.textContent = "请求异常";
                    result.style.color = "#dc3545";
                }
            };
            xhr.onerror = function(){ btn.disabled = false; result.textContent = "网络错误"; };
            xhr.send("action=wxs_go_update_remote&_nonce=' . $nonce . '");
        });
    })();
    </script>';

    return $html;
}

/**
 * 检测已安装/激活的缓存插件，生成CSF notice字段数组
 */
function wxs_go_cache_plugin_notice() {
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $cache_plugins = array(
        'litespeed-cache/litespeed-cache.php'   => 'LiteSpeed Cache',
        'nginx-helper/nginx-helper.php'         => 'Nginx Helper',
        'wp-super-cache/wp-cache.php'           => 'WP Super Cache',
        'w3-total-cache/w3-total-cache.php'     => 'W3 Total Cache',
        'jetpack-boost/jetpack-boost.php'       => 'Jetpack Boost',
        'wp-fastest-cache/wpFastestCache.php'   => 'WP Fastest Cache',
        'cachify/cachify.php'                   => 'Cachify',
        'cache-master/cache-master.php'         => 'Cache Master',
    );

    $detected = array();
    foreach ($cache_plugins as $path => $name) {
        $is_active    = is_plugin_active($path);
        $is_installed = (!$is_active && file_exists(WP_PLUGIN_DIR . '/' . $path));

        if ($is_active) {
            $detected[] = '<strong>' . $name . '</strong>（已启用）';
        } elseif ($is_installed) {
            $detected[] = '<strong>' . $name . '</strong>（已安装未启用）';
        }
    }

    if (empty($detected)) {
        return array(
            'type'       => 'notice',
            'style'      => 'info',
            'content'    => '未检测到已知缓存插件。如果您使用了例如WP Super Cache、Nginx Helper等缓存插件或其他服务端缓存，仍建议开启此功能。',
            'dependency' => array('nonce_refresh', '==', 'false'),
        );
    }

    $html = '检测到您安装了以下缓存插件：<br><ul style="margin:4px 0 4px 18px;list-style:disc;">';
    foreach ($detected as $item) {
        $html .= '<li>' . $item . '</li>';
    }
    $html .= '</ul>';
    $html .= '<strong style="color:#0073aa;">推荐开启JS动态刷新重定向链接</strong>，避免页面被缓存后nonce过期导致外链跳转地址无法访问。';

    return array(
        'type'       => 'notice',
        'style'      => 'warning',
        'content'    => $html,
        'dependency' => array('nonce_refresh', '==', 'false'),
    );
}

function wxs_go_admin_csf_options() {
    if (!is_admin()) {
        return;
    }
    if (!class_exists('CSF')) {
        return;
    }

    $options_key = 'wxs_go_redirect_options';
    $footer_text = sprintf(
            '作者：天无神话 | 版本:v%s <i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> 感谢您使用外链重定向增强插件。',
            esc_html(wxs_go_ver)
        );

    CSF::createOptions($options_key, array(
        'menu_title'      => '外链重定向增强',
        'menu_slug'       => 'wxs-go-redirect',
        'menu_icon'       => 'dashicons-admin-links',
        'menu_position'   => 35,
        'framework_title' => '外链重定向增强<small>by天无神话</small>',
        'theme'           => 'light',
        'footer_text'     => $footer_text,
        'footer_credit'   => '作者：天无神话 版权所有 ',
    ));

    // 插件介绍
    CSF::createSection($options_key, array(
        'id'     => 'intro',
        'title'  => '插件介绍',
        'icon'   => 'fa fa-fw fa-home',
        'fields' => array(
            array(
                'type'    => 'content',
                'content' => wxs_go_intro_html(),
            ),
        ),
    ));

    // 基本设置
    CSF::createSection($options_key, array(
        'id'     => 'basic',
        'title'  => '基本设置',
        'icon'   => 'fa fa-fw fa-cog',
        'fields' => array(
            array(
                'id'      => 'enable',
                'type'    => 'switcher',
                'title'   => '启用插件增强功能',
                'desc'    => '总开关。关闭后插件所有功能停用。<br><strong>注意：</strong>即使开启，也需子比主题同时开启外链重定向和外链重定向鉴权才会生效。',
                'default' => true,
            ),
            array(
                'id'      => 'countdown',
                'type'    => 'number',
                'title'   => '倒计时秒数',
                'desc'    => '跳转页面显示的自动跳转倒计时',
                'default' => '5',
                'attributes' => array(
                    'min'  => 1,
                    'max'  => 30,
                    'step' => 1,
                ),
            ),
            array(
                'id'      => 'nonce_refresh',
                'type'    => 'switcher',
                'title'   => 'JS动态刷新重定向链接',
                'desc'    => '开启后，前端JS会自动获取最新nonce更新页面中的外链跳转地址，防止页面缓存导致nonce过期、链接无法访问。<br>如果您的站点启用了页面缓存（插件缓存、服务端缓存等），<strong>建议开启</strong>。',
                'default' => true,
            ),
            wxs_go_cache_plugin_notice(),
            array(
                'type'    => 'content',
                'content' => '<div class="csf-notice csf-notice-info"><strong>工作原理：</strong><br>1. 子比主题生成<code>?golink=xxx&nonce=xxx</code>格式外链。<br>2. 本插件JS在未登录用户访问时，自动获取最新nonce更新页面中的链接（解决页面缓存导致nonce过期）。<br>3. 本插件接管跳转页面，增加域名黑名单拦截。</div>',
            ),
        ),
    ));

    // 模板设置
    $template_options = array();
    $templates = wxs_go_get_templates();
    foreach ($templates as $slug => $tpl) {
        $template_options[$slug] = !empty($tpl['Thumbnail']) ? $tpl['Thumbnail'] : '';
    }

    CSF::createSection($options_key, array(
        'id'     => 'template',
        'title'  => '模板设置',
        'icon'   => 'fa fa-fw fa-paint-brush',
        'fields' => array(
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '<strong>如何新增模板：</strong><ol style="margin:6px 0 12px 16px;line-height:1.8;">
                    <li>将模板PHP文件放入插件<code>templates/</code>目录</li>
                    <li>文件顶部添加模板信息注释：<br><code>/* Template Name: 模板名称<br>&nbsp;&nbsp;&nbsp;Description: 模板描述<br>&nbsp;&nbsp;&nbsp;Thumbnail: assets/img/缩略图.png<br>&nbsp;&nbsp;&nbsp;Author: 作者<br>&nbsp;&nbsp;&nbsp;Version: 1.0.0 */</code></li>
                    <li>将正方形缩略图放入<code>assets/img/</code>目录</li>
                    <li>保存后模板自动出现在下方选项中</li>
                </ol>'
                . '<style>
                    .csf--image-group{display:flex;flex-wrap:wrap;gap:12px}
                    .csf--image-group .csf--image{margin:0}
                    .csf--image-group .csf--image figure{width:140px;margin:0;border-radius:6px;transition:border-color .2s,box-shadow .2s}
                    .csf--image-group .csf--image img{width:100%;height:auto;border:none;border-radius:6px;display:block;aspect-ratio:1/1;object-fit:cover;outline:none!important}
                    .csf-field-image_select .csf--active figure,
                    .csf--image-group .csf--image.csf--active figure,
                    .csf--image-group .csf--image input:checked ~ figure{border-color:#59b3f6!important;box-shadow:0 0 0 1px #59b3f6,0 0 8px rgba(34,113,177,.25)!important;border-radius:6px}
                    .csf-field-image_select .csf--active figure::before,
                    .csf--image-group .csf--image.csf--active figure::before{border-radius:0 3px 0 3px!important;overflow:hidden}
                </style>',
            ),
            array(
                'id'      => 'template',
                'type'    => 'image_select',
                'title'   => '跳转页模板',
                'desc'    => '选择跳转提示页的显示模板。将新模板PHP文件放入插件 <code>templates/</code> 目录即可自动识别。',
                'options' => $template_options,
                'default' => 'default',
                'inline'  => true,
            ),
            array(
                'type'    => 'content',
                'content' => '<div id="wxs-go-template-info" style="display:none;padding:10px 14px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;line-height:1.8;">
                    <strong id="wxs-go-tpl-name"></strong>
                    <div id="wxs-go-tpl-desc" style="color:#646970;font-size:13px;"></div>
                    <div style="color:#999;font-size:12px;">作者：<span id="wxs-go-tpl-author"></span> | 版本：<span id="wxs-go-tpl-version"></span></div>
                </div>',
            ),
            // 子比模板专属：跳转方式选择
            array(
                'id'      => 'zibll_mode',
                'type'    => 'button_set',
                'title'   => '跳转方式',
                'desc'    => '<strong>子比原生跳转</strong>：复制子比主题go.php原版的简洁球体动画跳转页，无卡片、无按钮，1.5秒后自动跳转。<br><strong>插件自定义卡片提示</strong>：带Logo、安全提示、倒计时和操作按钮的卡片式跳转页，完美融入子比主题设计。',
                'options' => array(
                    'original' => '子比原生跳转',
                    'card'     => '插件自定义卡片提示',
                ),
                'default'    => 'card',
                'dependency' => array('template', '==', 'zibll'),
            ),
            array(
                'id'      => 'sticker_enabled',
                'type'    => 'switcher',
                'title'   => '特色图标',
                'desc'    => '开启后可使用预设贴纸图替代网站Logo显示在跳转页。<span id="wxs-go-sticker-unsupported" style="display:none;color:#d63638;font-weight:500;">（当前模板不支持特色图标）</span>',
                'default' => false,
                'class'   => 'wxs-go-sticker-field',
            ),
            array(
                'id'      => 'sticker_selected',
                'type'    => 'content',
                'title'   => '选择特色图标',
                'content' => '<div id="wxs-go-sticker-picker" style="display:flex;flex-wrap:wrap;gap:12px;"></div>
                    <input type="hidden" name="wxs_go_redirect_options[sticker_selected]" id="wxs-go-sticker-value" value="' . esc_attr(wxs_go_option('sticker_selected', '')) . '">',
                'dependency' => array('sticker_enabled', '==', 'true'),
                'class'   => 'wxs-go-sticker-field',
            ),

        ),
    ));

    // 模板选择JS + 特色图标选择
    add_action('admin_footer', function() use ($templates) {
        // 仅在本插件设置页输出
        if (empty($_GET['page']) || $_GET['page'] !== 'wxs-go-redirect') {
            return;
        }
        $stickers = wxs_go_get_stickers();
        $current_sticker = wxs_go_option('sticker_selected', '');
        ?>
        <script>
        (function($){
            var tplInfo = <?php echo wp_json_encode($templates); ?>;
            var stickers = <?php echo wp_json_encode($stickers); ?>;
            var currentSticker = <?php echo wp_json_encode($current_sticker); ?>;
            var fieldName = 'wxs_go_redirect_options[template]';

            function getSelectedTemplate() {
                var checked = document.querySelector('input[name="' + fieldName + '"]:checked');
                return checked ? checked.value : '';
            }

            function showInfo(slug) {
                var panel = document.getElementById("wxs-go-template-info");
                if (!panel) return;
                var t = tplInfo[slug] || {};
                if (!t.Name) { panel.style.display = "none"; return; }
                document.getElementById("wxs-go-tpl-name").textContent = t.Name;
                document.getElementById("wxs-go-tpl-desc").textContent = t.Description || "";
                document.getElementById("wxs-go-tpl-author").textContent = t.Author || "-";
                document.getElementById("wxs-go-tpl-version").textContent = t.Version || "-";
                panel.style.display = "";
            }

            function updateStickerVisibility(slug) {
                var t = tplInfo[slug] || {};
                var supported = !!(t.Sticker);
                var unsupported = document.getElementById("wxs-go-sticker-unsupported");
                var $fields = $('.wxs-go-sticker-field');

                if (unsupported) {
                    unsupported.style.display = supported ? "none" : "";
                }

                // 根据模板是否支持Sticker控制字段显隐
                if (supported) {
                    $fields.show();
                } else {
                    $fields.hide();
                }
            }

            function renderStickerPicker() {
                var picker = document.getElementById("wxs-go-sticker-picker");
                if (!picker) return;
                picker.innerHTML = "";

                var slugs = Object.keys(stickers);
                if (!slugs.length) {
                    picker.innerHTML = '<span style="color:#999;">暂无可用贴纸图</span>';
                    return;
                }

                slugs.forEach(function(slug) {
                    var s = stickers[slug];
                    var isActive = (slug === currentSticker);
                    var item = document.createElement("div");
                    item.className = "wxs-sticker-item" + (isActive ? " wxs-sticker-active" : "");
                    item.setAttribute("data-slug", slug);
                    item.innerHTML = '<img src="' + s.preview + '" alt="' + s.title + '"><div class="wxs-sticker-title">' + s.title + '</div>';
                    item.addEventListener("click", function() {
                        currentSticker = slug;
                        document.getElementById("wxs-go-sticker-value").value = slug;
                        picker.querySelectorAll(".wxs-sticker-item").forEach(function(el) {
                            el.classList.remove("wxs-sticker-active");
                        });
                        item.classList.add("wxs-sticker-active");
                    });
                    picker.appendChild(item);
                });
            }

            // 初始显示
            var initSlug = getSelectedTemplate();
            if (initSlug) showInfo(initSlug);
            updateStickerVisibility(initSlug);
            renderStickerPicker();

            // 使用jQuery监听change事件
            $(document).on("change", 'input[name="' + fieldName + '"]', function() {
                var slug = this.value;
                showInfo(slug);
                updateStickerVisibility(slug);
            });
        })(jQuery);
        </script>
        <style>
        .wxs-sticker-item{width:80px;text-align:center;cursor:pointer;padding:6px;border:2px solid transparent;border-radius:8px;transition:border-color .2s,box-shadow .2s}
        .wxs-sticker-item:hover{border-color:#c3c4c7}
        .wxs-sticker-item.wxs-sticker-active{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}
        .wxs-sticker-item img{width:64px;height:64px;object-fit:contain;border-radius:6px;background:#f9f9f9}
        .wxs-sticker-item .wxs-sticker-title{font-size:11px;color:#50575e;margin-top:4px;line-height:1.3;word-break:break-all}
        </style>
        <?php
    });

    // 黑名单设置
    CSF::createSection($options_key, array(
        'id'     => 'blacklist',
        'title'  => '黑名单设置',
        'icon'   => 'fa fa-fw fa-shield',
        'fields' => array(
            array(
                'id'      => 'blacklist_enabled',
                'type'    => 'switcher',
                'title'   => '启用黑名单拦截',
                'desc'    => '总开关。关闭后不进行域名黑名单匹配，所有外链直接跳转。',
                'default' => true,
            ),
            array(
                'id'      => 'blacklist_source',
                'type'    => 'button_set',
                'title'   => '黑名单来源',
                'desc'    => '<strong>外部数据源</strong>：从本地文件或远程订阅获取。<br><strong>手动编辑</strong>：直接在下方填写，适合少量域名。',
                'options' => array(
                    'file'   => '外部数据源',
                    'manual' => '手动编辑',
                ),
                'default' => 'file',
                'dependency' => array('blacklist_enabled', '==', 'true'),
            ),
            // 外部数据源模式
            array(
                'id'      => 'blacklist_data_type',
                'type'    => 'button_set',
                'title'   => '数据类型',
                'desc'    => '<strong>本地文件</strong>：从服务器本地路径读取（支持明文/Base64/AutoProxy格式，见下方数据格式设置）。<br><strong>远程订阅</strong>：定时从远程URL下载，解析后缓存为PHP hashmap文件，缓存位置：<code>wp-content/wxs-go-blacklist-cache.php</code>。',
                'options' => array(
                    'local'  => '本地文件',
                    'remote' => '远程订阅',
                ),
                'default' => 'local',
                'dependency' => array('blacklist_source', '==', 'file'),
            ),
            // 本地文件路径
            array(
                'id'    => 'blacklist_file',
                'type'  => 'text',
                'title' => '黑名单文件路径',
                'desc'  => '黑名单文件的服务器绝对路径。<br><strong>允许的文件扩展名：</strong><code>.txt</code>、<code>.list</code>、<code>.conf</code>、<code>.dat</code>、<code>.csv</code>（仅纯文本文件）。<br><strong>支持的内容格式</strong>（由下方数据格式设置决定）：<br>• 明文：每行一个域名<br>• Base64编码：整体内容Base64编码后再按行解析<br>• AutoProxy：AdBlock规则格式（Base64编码）<br><br>文件首次被访问时自动解析并生成PHP hashmap缓存（<code>wp-content/wxs-go-blacklist-local-cache.php</code>），后续请求直接读取缓存，源文件更新后缓存自动重建。',
                'default' => WP_CONTENT_DIR . '/blacklisted-domain.txt',
                'dependency' => array('blacklist_source|blacklist_data_type', '==|==', 'file|local'),
            ),
            // 远程订阅URL
            array(
                'id'    => 'remote_url',
                'type'  => 'text',
                'title' => '远程订阅URL',
                'desc'  => '远程黑名单文件的下载地址（支持HTTP/HTTPS）。下载后自动解析并缓存为PHP hashmap文件，缓存位置：<code>wp-content/wxs-go-blacklist-cache.php</code>。<br>首次下载和定时更新由WordPress定时任务（wp-cron）触发，也可点击下方立即更新按钮手动触发。',
                'default' => '',
                'dependency' => array('blacklist_source|blacklist_data_type', '==|==', 'file|remote'),
            ),
            // 远程更新间隔
            array(
                'id'      => 'remote_interval',
                'type'    => 'number',
                'title'   => '更新间隔（小时）',
                'desc'    => '定时任务自动下载远程黑名单的间隔时间。',
                'default' => '12',
                'attributes' => array(
                    'min'  => 1,
                    'max'  => 168,
                    'step' => 1,
                ),
                'dependency' => array('blacklist_source|blacklist_data_type', '==|==', 'file|remote'),
            ),
            // 自定义User-Agent
            array(
                'id'      => 'remote_user_agent',
                'type'    => 'text',
                'title'   => '自定义User-Agent',
                'desc'    => '请求远程订阅时使用的User-Agent头信息，留空则使用WordPress默认UA。部分订阅源要求特定UA才能正常下载。',
                'default' => '',
                'dependency' => array('blacklist_source|blacklist_data_type', '==|==', 'file|remote'),
            ),
            // 远程状态信息 + 手动更新按钮
            array(
                'id'    => 'remote_status_display',
                'type'  => 'content',
                'title' => '远程订阅状态',
                'content' => wxs_go_remote_status_html(),
                'dependency' => array('blacklist_source|blacklist_data_type', '==|==', 'file|remote'),
            ),
            // 数据格式
            array(
                'id'      => 'blacklist_encoded',
                'type'    => 'button_set',
                'title'   => '数据格式',
                'desc'    => '<strong>明文</strong>：每行一个域名。<br><strong>Base64</strong>：内容经Base64编码。<br><strong>AutoProxy</strong>：AdBlock规则格式（Base64编码）。',
                'options' => array(
                    'plain'     => '明文',
                    'base64'    => 'Base64编码',
                    'autoproxy' => 'AutoProxy',
                ),
                'default' => 'base64',
                'dependency' => array('blacklist_source', '==', 'file'),
            ),
            // 补充自定义黑名单
            array(
                'id'      => 'blacklist_extra',
                'type'    => 'textarea',
                'title'   => '补充自定义黑名单',
                'desc'    => '与外部数据源合并生效，每行一个域名。',
                'default' => '',
                'attributes' => array(
                    'rows' => 6,
                ),
                'sanitize' => false,
                'dependency' => array('blacklist_source', '==', 'file'),
            ),
            // 手动编辑模式
            array(
                'id'      => 'blacklist_manual',
                'type'    => 'textarea',
                'title'   => '手动黑名单',
                'desc'    => '每行一个域名，匹配到则拦截跳转。',
                'default' => '',
                'attributes' => array(
                    'rows' => 8,
                ),
                'sanitize' => false,
                'dependency' => array('blacklist_source', '==', 'manual'),
            ),
        ),
    ));

    // 通知设置
    CSF::createSection($options_key, array(
        'id'     => 'notify',
        'title'  => '通知设置',
        'icon'   => 'fa fa-fw fa-bell',
        'fields' => array(
            array(
                'id'      => 'webhook_enabled',
                'type'    => 'switcher',
                'title'   => '启用Webhook通知',
                'desc'    => '开启后，黑名单更新失败（格式解析错误、下载失败、文件不可读等）时会向指定URL发送POST通知。<br>支持企业微信机器人、钉钉机器人、飞书机器人、自定义HTTP接口等。<br><br><strong>通知内容示例（自动生成，无需额外配置）：</strong><br><code style="background:#f0f0f1;padding:6px 10px;display:inline-block;border-radius:3px;line-height:1.6;">【外链重定向增强 - 站点名称】远程订阅下载失败：cURL error 28（URL：https://example.com/blacklist.txt）</code>',
                'default' => false,
            ),
            array(
                'id'         => 'webhook_url',
                'type'       => 'text',
                'title'      => 'Webhook URL',
                'desc'       => '接收通知的Webhook地址。通知内容由系统自动生成，无需手动填写消息模板。<br><br><strong>各平台填写方式：</strong><br>• 企业微信机器人：复制机器人的Webhook地址直接粘贴<br>• 钉钉机器人：复制access_token完整地址直接粘贴<br>• 飞书机器人：复制webhook地址直接粘贴<br>• 自定义接口：需能接收POST JSON body',
                'default'    => '',
                'dependency' => array('webhook_enabled', '==', 'true'),
            ),
            array(
                'id'         => 'webhook_format',
                'type'       => 'button_set',
                'title'      => '消息格式',
                'desc'       => '选择与您使用的机器人/接口匹配的JSON格式。',
                'options'    => array(
                    'wechat' => '企业微信',
                    'dingtalk' => '钉钉',
                    'feishu'   => '飞书',
                    'custom' => '自定义（content字段）',
                ),
                'default'    => 'custom',
                'dependency' => array('webhook_enabled', '==', 'true'),
            ),
        ),
    ));

    // 广告设置
    CSF::createSection($options_key, array(
        'id'     => 'ad',
        'title'  => '广告设置',
        'icon'   => 'fa fa-fw fa-bullhorn',
        'fields' => array(
            array(
                'id'      => 'ad_enabled',
                'type'    => 'switcher',
                'title'   => '启用广告展示',
                'desc'    => '总开关。关闭后，即使已设置广告图片也不会在跳转页显示。',
                'default' => false,
            ),
            array(
                'id'         => 'ad_image',
                'type'       => 'upload',
                'title'      => '广告图片',
                'desc'       => '跳转页底部广告图片，支持从媒体库选择/上传，也可直接填写外部图片URL。留空不显示。',
                'default'    => '',
                'library'    => 'image',
                'preview'    => true,
                'dependency' => array('ad_enabled', '==', 'true'),
            ),
            array(
                'id'         => 'ad_link',
                'type'       => 'text',
                'title'      => '广告链接地址',
                'desc'       => '点击广告跳转的URL，留空则不可点击。',
                'default'    => '',
                'dependency' => array('ad_enabled', '==', 'true'),
            ),
            array(
                'id'         => 'ad_alt',
                'type'       => 'text',
                'title'      => '广告描述文字',
                'desc'       => '图片alt文字。',
                'default'    => '广告',
                'dependency' => array('ad_enabled', '==', 'true'),
            ),
        ),
    ));

    // 更新设置
    CSF::createSection($options_key, array(
        'id'     => 'update',
        'title'  => '更新设置',
        'icon'   => 'fa fa-fw fa-cloud-download',
        'fields' => array(
            array(
                'type'    => 'notice',
                'style'   => 'info',
                'content' => '插件通过 GitHub Releases检测并下载更新。国内服务器直连GitHub可能超时失败，可在下方选择加速镜像。<br>下载完成后会自动校验安装包内容，确认为本插件才会替换文件，避免下载到错误或损坏的资源破坏现有插件。',
            ),
            array(
                'id'      => 'update_mirror',
                'type'    => 'select',
                'title'   => 'GitHub加速镜像',
                'desc'    => '选择下载更新时使用的镜像加速地址。镜像为第三方公益服务，可用性可能波动，若某个不可用请更换。<br><strong>官方直连</strong>：不使用镜像，直接访问GitHub（海外服务器推荐）。',
                'options' => array(
                    ''                                  => '官方直连（不使用镜像）',
                    'https://edgeone.gh-proxy.com/'     => 'edgeone.gh-proxy.com（推荐）',
                    'https://gh-proxy.com/'             => 'gh-proxy.com',
                    'https://ghproxy.net/'              => 'ghproxy.net',
                    'https://github.akams.cn/'          => 'github.akams.cn',
                    'custom'                            => '自定义镜像地址',
                ),
                'default' => '',
            ),
            array(
                'id'         => 'update_mirror_custom',
                'type'       => 'text',
                'title'      => '自定义镜像地址',
                'desc'       => '填写镜像前缀，需以 http(s):// 开头。插件会将其拼接在 GitHub原始链接前，例如：<code>https://你的镜像域名/</code><br>最终请求形如：<code>https://你的镜像域名/https://github.com/twsh0305/wxs-go-redirect/...</code>',
                'default'    => '',
                'placeholder' => 'https://your-mirror.example.com/',
                'dependency' => array('update_mirror', '==', 'custom'),
            ),
        ),
    ));

    // 调试设置
    CSF::createSection($options_key, array(
        'id'     => 'debug',
        'title'  => '调试设置',
        'icon'   => 'fa fa-fw fa-bug',
        'fields' => array(
            array(
                'id'      => 'debug_nonce',
                'type'    => 'switcher',
                'title'   => 'Nonce快速过期调试',
                'desc'    => '开启后缩短nonce有效期，用于验证前端JS是否正确刷新链接中的nonce。<br><strong style="color:#d63638;">仅用于调试，验证完毕后务必关闭！</strong>',
                'default' => false,
            ),
            array(
                'id'      => 'debug_nonce_life',
                'type'    => 'number',
                'title'   => 'Nonce刷新间隔（秒）',
                'desc'    => '设定后，约每隔该秒数页面上外链的 <code>&nonce=</code> 值会变化一次。建议设为30~120秒。',
                'default' => 60,
                'attributes' => array(
                    'min'  => 10,
                    'max'  => 600,
                    'step' => 10,
                ),
                'dependency' => array('debug_nonce', '==', 'true'),
            ),
            array(
                'type'    => 'notice',
                'style'   => 'info',
                'content' => '<strong>调试步骤：</strong><ol style="margin:8px 0 0 18px;line-height:2;">
                    <li>开启上方开关并设置合适的秒数，保存设置</li>
                    <li><strong>手动清理页面缓存</strong>（如LiteSpeed Cache、Nginx Helper、WP Super Cache、W3 Total Cache、Jetpack Boost、WP Fastest Cache、Cachify、Cache Master等缓存插件）</li>
                    <li>以<strong>未登录状态</strong>（无痕窗口）访问包含外链的文章页面，找到一个带<code>&nonce=xxx</code>的重定向外链</li>
                    <li>等待设定时间后刷新页面，观察该链接 <code>&nonce=</code> 后面的值是否变化，以及链接是否可正常点击跳转</li>
                    <li>确认刷新机制正常后，<strong>关闭此开关</strong></li>
                </ol>
                <p style="margin-top:8px;color:#d63638;">注意：开启期间所有WordPress nonce的有效期都会缩短（包括后台表单），请尽快完成测试后关闭。</p>',
                'dependency' => array('debug_nonce', '==', 'true'),
            ),
        ),
    ));
}

/**
 * 插件介绍首页
 */
function wxs_go_intro_html() {
    $ver       = defined('wxs_go_ver') ? wxs_go_ver : '';
    $alipay    = wxs_go_url . '/assets/img/donate-alipay.png';
    $wechat    = wxs_go_url . '/assets/img/donate-wechat.png';
    $logo      = wxs_go_url . '/assets/img/wxsnote.cn-logo.svg';
    $avatar    = 'https://q.qlogo.cn/headimg_dl?dst_uin=2031301686&spec=140&img_type=jpg';

    ob_start();
    ?>
    <div class="wxs-go-intro">

        <!-- Hero 区 -->
        <div class="wxs-go-hero">
            <div class="wxs-go-hero-body">
                <h2 class="wxs-go-hero-title">
                    <span class="wxs-go-hero-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>
                    外链重定向增强
                    <span class="wxs-go-hero-ver">v<?php echo esc_html($ver); ?></span>
                </h2>
                <p class="wxs-go-hero-desc">配合子比主题外链重定向 + 鉴权功能，解决各类缓存插件导致的外链跳转失效问题，并提供多风格跳转页与域名黑名单拦截。</p>
            </div>
        </div>

        <!-- 功能卡片网格 -->
        <div class="wxs-go-cards">
            <div class="wxs-go-card">
                <div class="wxs-go-card-icon wxs-go-card-icon--refresh">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </div>
                <div class="wxs-go-card-text">
                    <h4>Nonce动态刷新</h4>
                    <p>JS异步获取最新nonce更新页面中的外链地址，防止缓存导致链接过期失效。</p>
                </div>
            </div>

            <div class="wxs-go-card">
                <div class="wxs-go-card-icon wxs-go-card-icon--template">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <div class="wxs-go-card-text">
                    <h4>多风格跳转页</h4>
                    <p>内置 10 套跳转页模板，支持特色图标贴纸与模板自由扩展。</p>
                </div>
            </div>

            <div class="wxs-go-card">
                <div class="wxs-go-card-icon wxs-go-card-icon--shield">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="wxs-go-card-text">
                    <h4>域名黑名单</h4>
                    <p>支持本地文件、远程订阅、手动编辑多种来源，精准拦截违规域名。</p>
                </div>
            </div>

            <div class="wxs-go-card">
                <div class="wxs-go-card-icon wxs-go-card-icon--update">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></svg>
                </div>
                <div class="wxs-go-card-text">
                    <h4>安全自动更新</h4>
                    <p>GitHub Releases检测更新，镜像加速 + 下载包内容校验，安全替换。</p>
                </div>
            </div>
        </div>

        <!-- 提示条 -->
        <div class="wxs-go-tip">
            <svg class="wxs-go-tip-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>使用前提：需安装并启用子比主题，且在主题设置中同时开启外链重定向和外链重定向鉴权。</span>
        </div>

        <!-- 打赏区 -->
        <div class="wxs-go-donate">
            <h3 class="wxs-go-donate-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                支持作者
            </h3>
            <div style="margin-bottom:12px;">
                <a href="https://wxsnote.cn" target="_blank" rel="noopener"><img src="<?php echo esc_url($logo); ?>" alt="wxsnote.cn" style="width:180px;height:auto;display:block;margin:0 auto;"></a>
            </div>
            <p class="wxs-go-donate-desc">本插件免费开源（GPL v3.0）。如果它对你有帮助，欢迎打赏一杯咖啡 ☕</p>
            <div class="wxs-go-donate-imgs">
                <figure class="wxs-go-donate-figure">
                    <img src="<?php echo esc_url($alipay); ?>" alt="支付宝打赏" onerror="this.parentElement.style.display='none'">
                    <figcaption>支付宝</figcaption>
                </figure>
                <figure class="wxs-go-donate-figure">
                    <img src="<?php echo esc_url($wechat); ?>" alt="微信打赏" onerror="this.parentElement.style.display='none'">
                    <figcaption>微信</figcaption>
                </figure>
            </div>
        </div>

        <!-- 联系作者 -->
        <div class="wxs-go-contact">
            <div class="wxs-go-contact-avatar">
                <a href="https://wxsnote.cn" target="_blank" rel="noopener"><img src="<?php echo esc_url($avatar); ?>" alt="天无神话" style="width:80px;height:80px;border-radius:50%;display:block;"></a>
            </div>
            <div class="wxs-go-contact-info">
                <div class="wxs-go-contact-name">天无神话</div>
                <div class="wxs-go-contact-links">
                    <a href="https://qm.qq.com/q/aKlJlhh4VU" target="_blank" rel="noopener">QQ：2031301686</a>
                    <a href="https://jq.qq.com/?_wv=1027&k=eiGEOg3i" target="_blank" rel="noopener">QQ群：399019539</a>
                    <a href="https://github.com/twsh0305" target="_blank" rel="noopener">GitHub</a>
                    <a href="https://github.com/twsh0305/wxs-go-redirect" target="_blank" rel="noopener">插件项目</a>
                    <a href="https://wxsnote.cn/8284.html" target="_blank" rel="noopener">插件介绍文章</a>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* ===== 插件介绍首页 - CSF 风格 ===== */

    .wxs-go-intro {
        max-width: 100%;
        line-height: 1.7;
        font-size: 14px;
        color: #50575e;
    }

    /* -- Hero 区 -- */
    .wxs-go-hero {
        background: #f0f6fc;
        border: 1px solid #c5d9ed;
        border-radius: 8px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .wxs-go-hero-body {
        padding: clamp(16px, 2.5vw, 24px) clamp(16px, 2.5vw, 28px);
    }
    .wxs-go-hero-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 8px;
        font-size: clamp(16px, 2vw, 18px);
        font-weight: 600;
        color: #1d2327;
    }
    .wxs-go-hero-icon {
        display: inline-flex;
        align-items: center;
        color: #2271b1;
        flex-shrink: 0;
    }
    .wxs-go-hero-ver {
        display: inline-block;
        font-size: 11px;
        font-weight: 500;
        background: #e5eef7;
        color: #2271b1;
        padding: 1px 8px;
        border-radius: 10px;
        vertical-align: middle;
        line-height: 1.6;
    }
    .wxs-go-hero-desc {
        margin: 0;
        font-size: 13px;
        color: #50575e;
        padding-left: 36px; /* 对齐图标宽度 */
    }

    /* -- 功能卡片网格 -- */
    .wxs-go-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .wxs-go-card {
        display: flex;
        gap: 12px;
        padding: clamp(14px, 2vw, 18px);
        background: #fff;
        border: 1px solid #e2e4e7;
        border-radius: 8px;
        transition: border-color .2s, box-shadow .2s;
    }
    .wxs-go-card:hover {
        border-color: #c5d9ed;
    }
    .wxs-go-card-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        flex-shrink: 0;
    }
    .wxs-go-card-icon--refresh {
        background: #e8f4f8;
        color: #0e7c9b;
    }
    .wxs-go-card-icon--template {
        background: #f2e8f5;
        color: #7c3a8e;
    }
    .wxs-go-card-icon--shield {
        background: #e8f5e9;
        color: #2e7d32;
    }
    .wxs-go-card-icon--update {
        background: #fff3e0;
        color: #e65100;
    }
    .wxs-go-card-text {
        min-width: 0;
    }
    .wxs-go-card-text h4 {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 600;
        color: #1d2327;
    }
    .wxs-go-card-text p {
        margin: 0;
        font-size: 12.5px;
        color: #646970;
        line-height: 1.6;
    }

    /* -- 提示条 -- */
    .wxs-go-tip {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 12px 14px;
        background: #fcf9e8;
        border: 1px solid #e2dcc2;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #7b6e23;
        line-height: 1.6;
    }
    .wxs-go-tip-icon {
        flex-shrink: 0;
        margin-top: 1px;
        opacity: .75;
    }
    .wxs-go-tip a {
        color: #2271b1;
        text-decoration: underline;
    }

    /* -- 打赏区 -- */
    .wxs-go-donate {
        padding: clamp(16px, 2.5vw, 22px) clamp(16px, 2.5vw, 24px);
        background: #fff;
        border: 1px solid #e2e4e7;
        border-radius: 8px;
        text-align: center;
    }
    .wxs-go-donate-title {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0 0 8px;
        font-size: 15px;
        font-weight: 600;
        color: #1d2327;
    }
    .wxs-go-donate-title svg {
        color: #d63638;
    }
    .wxs-go-donate-desc {
        color: #646970;
        font-size: 13px;
        max-width: 520px;
        margin: 0 auto 18px;
    }
    .wxs-go-donate-imgs {
        display: flex;
        justify-content: center;
        gap: clamp(16px, 3vw, 32px);
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .wxs-go-donate-figure {
        margin: 0;
        text-align: center;
    }
    .wxs-go-donate-figure img {
        width: clamp(140px, 18vw, 180px);
        height: clamp(140px, 18vw, 180px);
        object-fit: contain;
        background: #fafafa;
        border: 1px solid #e2e4e7;
        border-radius: 8px;
        padding: 6px;
        display: block;
    }
    .wxs-go-donate-figure figcaption {
        margin-top: 6px;
        font-size: 12px;
        color: #8c8f94;
    }

    /* -- 联系作者 -- */
    .wxs-go-contact {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: clamp(16px, 2.5vw, 22px) clamp(16px, 2.5vw, 24px);
        background: #fff;
        border: 1px solid #e2e4e7;
        border-radius: 8px;
        margin-top: 14px;
    }
    .wxs-go-contact-avatar {
        flex-shrink: 0;
    }
    .wxs-go-contact-avatar img {
        transition: opacity .2s;
    }
    .wxs-go-contact-avatar img:hover {
        opacity: .85;
    }
    .wxs-go-contact-name {
        font-size: 15px;
        font-weight: 600;
        color: #1d2327;
        margin-bottom: 8px;
    }
    .wxs-go-contact-links {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .wxs-go-contact-links a {
        display: inline-block;
        padding: 4px 10px;
        font-size: 12.5px;
        background: #f0f6fc;
        color: #2271b1;
        border-radius: 4px;
        text-decoration: none;
        transition: background .2s;
    }
    .wxs-go-contact-links a:hover {
        background: #dce9f5;
        color: #0a4b78;
    }

    /* -- CSF 顶部背景文字 -- */
    html body .csf-theme-light .csf-header-inner::before {
        content: "WXS" !important;
    }

    /* -- 响应式 -- */
    @media (max-width: 600px) {
        .wxs-go-cards {
            grid-template-columns: 1fr;
        }
        .wxs-go-hero-desc {
            padding-left: 0;
        }
        .wxs-go-contact {
            flex-direction: column;
            text-align: center;
        }
        .wxs-go-contact-links {
            justify-content: center;
        }
    }
    @media (min-width: 601px) and (max-width: 960px) {
        .wxs-go-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (min-width: 961px) and (max-width: 1400px) {
        .wxs-go-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (min-width: 1401px) {
        .wxs-go-cards {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    </style>
    <?php
    return ob_get_clean();
}
