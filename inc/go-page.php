<?php
/**
 * 外链重定向增强 - 跳转页路由
 * 处理 URL 解码、nonce 验证、黑名单判断，然后加载模板渲染
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 获取所有可用模板
 * 扫描 templates/ 目录，解析文件头注释获取模板信息
 *
 * @return array 模板数组，键为文件名（不含扩展名），值为模板信息
 */
function wxs_go_get_templates() {
    static $templates = null;
    if ($templates !== null) {
        return $templates;
    }

    $templates = array();
    $dir = wxs_go_path . 'templates';

    if (!is_dir($dir)) {
        return $templates;
    }

    $files = glob($dir . '/*.php');
    if (empty($files)) {
        return $templates;
    }

    $headers = array(
        'Name'        => 'Template Name',
        'Description' => 'Description',
        'Thumbnail'   => 'Thumbnail',
        'Author'      => 'Author',
        'Version'     => 'Version',
        'Sticker'     => 'Sticker',
    );

    foreach ($files as $file) {
        $data = get_file_data($file, $headers);

        // 必须有 Template Name 才算有效模板
        if (empty($data['Name'])) {
            continue;
        }

        $slug = basename($file, '.php');

        // Thumbnail 转为完整 URL（相对于插件目录）
        if (!empty($data['Thumbnail'])) {
            $data['Thumbnail'] = plugins_url($data['Thumbnail'], dirname(__FILE__));
        }

        $data['File'] = $file;
        $templates[$slug] = $data;
    }

    return $templates;
}

/**
 * 获取所有可用贴纸（特色图标）
 * 扫描 assets/stickers/ 目录，返回贴纸列表
 *
 * @return array 贴纸数组，键为slug，值包含 title, preview, clear, blocked 的URL
 */
function wxs_go_get_stickers() {
    static $stickers = null;
    if ($stickers !== null) {
        return $stickers;
    }

    $stickers = array();
    $dir = wxs_go_path . 'assets/stickers';

    if (!is_dir($dir)) {
        return $stickers;
    }

    // 扫描所有不带后缀修饰的 png 文件作为贴纸基础名
    $files = glob($dir . '/*.png');
    if (empty($files)) {
        return $stickers;
    }

    // 中英文标题映射
    $titles = array(
        'bye-coffee'     => '咖啡杯',
        'portal-fox'     => '传送门狐狸',
        'pixel-fairy'    => '像素仙子',
        'farewell-robot' => '告别机器人',
        'map-explorer'   => '地图冒险家',
        'angel-cat'      => '天使猫信封',
        'wave-shiba'     => '挥手柴犬',
        'moon-rabbit'    => '月亮船白兔',
        'door-bunny'     => '木门兔爪',
        'heart-girl'     => '比心少女',
        'cat-plane'      => '猫脸纸飞机',
        'tv-guy'         => '电视机小人',
    );

    $base_url = plugins_url('assets/stickers/', dirname(__FILE__));

    foreach ($files as $file) {
        $name = basename($file, '.png');
        // 跳过 -clear 和 -blocked 后缀的文件
        if (preg_match('/-(clear|blocked)$/', $name)) {
            continue;
        }

        $slug = $name;
        $clear_file   = $dir . '/' . $slug . '-clear.png';
        $blocked_file = $dir . '/' . $slug . '-blocked.png';

        // 必须三件套齐全
        if (!file_exists($clear_file) || !file_exists($blocked_file)) {
            continue;
        }

        $stickers[$slug] = array(
            'title'   => isset($titles[$slug]) ? $titles[$slug] : $slug,
            'preview' => $base_url . $slug . '.png',
            'clear'   => $base_url . $slug . '-clear.png',
            'blocked' => $base_url . $slug . '-blocked.png',
        );
    }

    return $stickers;
}

/**
 * 获取当前模板应使用的 logo 图片地址
 * 若启用特色图标且当前模板支持，返回贴纸图片地址
 *
 * @param bool $is_blocked 当前是否为拦截状态
 * @return string|false 贴纸图片URL 或 false（使用默认logo）
 */
function wxs_go_get_sticker_logo($is_blocked = false) {
    $sticker_enabled = wxs_go_option('sticker_enabled', false);
    if (!$sticker_enabled) {
        return false;
    }

    // 检查当前模板是否支持贴纸
    $selected_tpl = wxs_go_option('template', 'default');
    $templates = wxs_go_get_templates();
    if (!isset($templates[$selected_tpl]) || empty($templates[$selected_tpl]['Sticker'])) {
        return false;
    }

    $sticker_slug = wxs_go_option('sticker_selected', '');
    if (empty($sticker_slug)) {
        return false;
    }

    $stickers = wxs_go_get_stickers();
    if (!isset($stickers[$sticker_slug])) {
        return false;
    }

    $sticker = $stickers[$sticker_slug];
    return $is_blocked ? $sticker['blocked'] : $sticker['clear'];
}

/**
 * 获取当前选中的模板文件路径
 *
 * @return string 模板文件绝对路径
 */
function wxs_go_get_current_template_file() {
    $selected = wxs_go_option('template', 'default');
    $templates = wxs_go_get_templates();

    if (isset($templates[$selected])) {
        return $templates[$selected]['File'];
    }

    // 回退到 default
    if (isset($templates['default'])) {
        return $templates['default']['File'];
    }

    // 最终回退：取第一个可用模板
    if (!empty($templates)) {
        $first = reset($templates);
        return $first['File'];
    }

    return '';
}

/**
 * 渲染跳转页面
 * @param string $golink_query  golink 参数值（base64 编码的 URL）
 * @param string $nonce         nonce 参数值
 */
function wxs_go_render_page($golink_query, $nonce = '') {
    $countdown = (int) wxs_go_option('countdown', 5);
    $logoimg   = _pz('logo_src', '');
    $logoimg_dark = _pz('logo_src_dark', '');
    if (!$logoimg_dark) $logoimg_dark = $logoimg;

    // 安全头检查
    if (
        strlen($_SERVER['REQUEST_URI']) > 384 ||
        stripos($_SERVER['REQUEST_URI'], 'eval(') !== false ||
        stripos($_SERVER['REQUEST_URI'], 'base64') !== false
    ) {
        header('HTTP/1.1 414 Request-URI Too Long');
        header('Status: 414 Request-URI Too Long');
        header('Connection: Close');
        exit;
    }

    // nonce 验证
    $is_nonce_invalid = false;
    if (empty($nonce) || !wp_verify_nonce($nonce, 'go_link_nonce')) {
        $is_nonce_invalid = true;
    }

    // 解码 URL
    $url = '';
    $title = '';
    $decoded = base64_decode($golink_query);

    if ($decoded !== false && !empty($decoded)) {
        // XSS 防护
        $decoded = htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
        $decoded = str_replace(array("'", '"'), array('&#39;', '&#34;'), $decoded);
        $decoded = str_replace(array("\r", "\n", "\t"), array('&#13;', '&#10;', '&#9;'), $decoded);

        // URL 协议校验
        if (preg_match('/^(http|https|thunder|qqdl|ed2k|Flashget|qbrowser):\/\//i', $decoded)) {
            $url   = $decoded;
            $title = '页面加载中,请稍候...';
        } elseif (preg_match('/\./i', $decoded)) {
            $url   = 'http://' . $decoded;
            $title = '页面加载中,请稍候...';
        } else {
            $url   = home_url();
            $title = '参数错误，正在返回首页...';
        }
    } else {
        $url   = home_url();
        $title = '参数缺失，正在返回首页...';
    }

    // 来源检查
    // - 空 Referer：依靠 nonce 把关，不额外拦截
    // - 有 Referer 且为本站：放行
    // - 有 Referer 且为外站：一律拦截
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (!empty($referer)) {
        $ref_host  = strtolower((string) wp_parse_url($referer, PHP_URL_HOST));
        $site_host = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
        if ($ref_host && $ref_host !== $site_host && !wxs_go_is_subdomain($ref_host, $site_host)) {
            $url   = home_url();
            $title = '非法来源，正在返回首页...';
            $is_nonce_invalid = true;
        }
    }

    // URL 清理
    $url = str_replace(array('&amp;amp;', '&amp;'), '&', $url);

    // 黑名单检查（需开启黑名单开关）
    $blacklist_match = false;
    if (!$is_nonce_invalid && $url !== home_url() && wxs_go_option('blacklist_enabled', true)) {
        $blacklist_match = wxs_go_check_blacklist($url);
    }

    // 确定拦截状态
    $is_blocked = ($is_nonce_invalid || $blacklist_match);
    if ($is_nonce_invalid && $url !== home_url()) {
        $blocked_reason = '链接验证已过期，请返回原页面刷新后重试。';
    } elseif ($blacklist_match) {
        $blocked_reason = '';
    } else {
        $blocked_reason = '';
    }

    // 安全兜底：nonce 失效时清除外站 URL，防止模板层意外跳转/泄露外站地址
    if ($is_nonce_invalid) {
        $url = home_url();
    }

    // 特色图标替换 logo
    $sticker_logo = wxs_go_get_sticker_logo($is_blocked);
    if ($sticker_logo) {
        $logoimg = $sticker_logo;
        $logoimg_dark = $sticker_logo;
    }

    // 加载渲染模板
    $template_file = wxs_go_get_current_template_file();
    if ($template_file && file_exists($template_file)) {
        require $template_file;
    } else {
        // 降级：直接跳首页
        header('Location: ' . home_url());
        exit;
    }
}
