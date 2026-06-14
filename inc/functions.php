<?php
/**
 * 外链重定向增强 - 核心函数
 * v2.0：配合子比原生重定向，提供 nonce 刷新 + 黑名单拦截
 */

if (!defined('ABSPATH')) {
    exit;
}

// Nonce 调试模式

add_filter('nonce_life', 'wxs_go_nonce_life');
function wxs_go_nonce_life($life) {
    $debug_nonce = wxs_go_option('debug_nonce', false);
    if ($debug_nonce) {
        $seconds = (int) wxs_go_option('debug_nonce_life', 60);
        if ($seconds > 0) {
            return $seconds * 2;
        }
    }
    return $life;
}

// Nonce 刷新 AJAX 接口

add_action('wp_ajax_nopriv_wxs_go_nonce', 'wxs_go_nonce_ajax');
add_action('wp_ajax_wxs_go_nonce', 'wxs_go_nonce_ajax');
function wxs_go_nonce_ajax() {
    if (!wxs_go_is_enabled()) {
        wp_send_json_error(array('msg' => '功能未启用'));
    }
    wp_send_json_success(array(
        'nonce' => wp_create_nonce('go_link_nonce'),
        'time'  => time(),
    ));
}

// 前端 JS 资源加载

add_action('wp_enqueue_scripts', 'wxs_go_enqueue_assets');
function wxs_go_enqueue_assets() {
    if (!wxs_go_is_enabled()) {
        return;
    }
    // JS 动态刷新开关
    if (!wxs_go_option('nonce_refresh', true)) {
        return;
    }
    // 登录用户页面不被 Nginx 缓存，nonce 始终有效，无需刷新
    if (is_user_logged_in()) {
        return;
    }

    wp_enqueue_script(
        'wxs-go-link-refresh',
        wxs_go_url . '/assets/js/go-link-refresh.js',
        array(),
        wxs_go_ver,
        true
    );
    // 计算 nonce tick：nonce 值在 tick 翻页时才会变化
    // tick_duration = nonce_life / 2，WP 默认 43200 秒（12h）
    $debug_nonce = wxs_go_option('debug_nonce', false);
    $nonce_life  = ($debug_nonce && (int) wxs_go_option('debug_nonce_life', 60) > 0)
        ? (int) wxs_go_option('debug_nonce_life', 60) * 2
        : DAY_IN_SECONDS;
    $tick_duration = max(1, (int) ($nonce_life / 2));
    $current_tick  = (int) ceil(time() / $tick_duration);

    wp_localize_script('wxs-go-link-refresh', 'wxs_go_config', array(
        'ajax_url'      => admin_url('admin-ajax.php'),
        'enable'        => true,
        'nonce_tick'    => $current_tick,
        'tick_duration' => $tick_duration,
    ));
}


// 模板路由接管

add_filter('query_vars', 'wxs_go_add_query_vars');
function wxs_go_add_query_vars($vars) {
    if (!is_admin()) {
        $vars[] = 'golink';
    }
    return $vars;
}

add_action('template_redirect', 'wxs_go_template_redirect', 4);
function wxs_go_template_redirect() {
    $golink = get_query_var('golink');
    if (!$golink || !wxs_go_is_enabled()) {
        return;
    }

    // 获取 nonce 参数
    $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

    global $wp_query;
    $wp_query->is_home = false;
    $wp_query->is_page = true;

    // 渲染跳转页
    wxs_go_render_page($golink, $nonce);
    exit;
}

// 辅助函数

/**
 * 判断 $host 是否是 $parent 的子域名
 */
function wxs_go_is_subdomain($host, $parent) {
    $host   = strtolower($host);
    $parent = strtolower($parent);
    if ($host === $parent) {
        return true;
    }
    return (substr($host, -(strlen($parent) + 1)) === '.' . $parent);
}

/**
 * 域名级别精确匹配
 */
function wxs_go_match_domain($url, $domain) {
    $host = wp_parse_url($url, PHP_URL_HOST);
    if (!$host) {
        return false;
    }
    return wxs_go_is_subdomain($host, $domain);
}

// 远程黑名单订阅 - PHP 缓存 + wp_cron

define('WXS_GO_CACHE_FILE', WP_CONTENT_DIR . '/wxs-go-blacklist-cache.php');

/**
 * 注册/注销定时任务
 */
function wxs_go_schedule_cron() {
    $source_type = wxs_go_option('blacklist_data_type', 'local');
    if ($source_type === 'remote' && wxs_go_option('blacklist_source', 'file') === 'file') {
        if (!wp_next_scheduled('wxs_go_update_blacklist')) {
            $interval = (int) wxs_go_option('remote_interval', 12);
            $interval = max(1, $interval);
            wp_schedule_event(time(), 'wxs_go_interval', 'wxs_go_update_blacklist');
        }
    } else {
        wp_clear_scheduled_hook('wxs_go_update_blacklist');
    }
}
add_action('admin_init', 'wxs_go_schedule_cron');

/**
 * 自定义 cron 间隔
 */
add_filter('cron_schedules', 'wxs_go_cron_schedules');
function wxs_go_cron_schedules($schedules) {
    $interval = (int) wxs_go_option('remote_interval', 12);
    $interval = max(1, $interval);
    $schedules['wxs_go_interval'] = array(
        'interval' => $interval * HOUR_IN_SECONDS,
        'display'  => sprintf('每 %d 小时（外链重定向黑名单）', $interval),
    );
    return $schedules;
}

/**
 * 发送 Webhook 通知
 * @param string $message 通知文本内容
 */
function wxs_go_send_webhook_notice($message) {
    if (!wxs_go_option('webhook_enabled', false)) {
        return;
    }
    $webhook_url = trim(wxs_go_option('webhook_url', ''));
    if (empty($webhook_url)) {
        return;
    }

    $format = wxs_go_option('webhook_format', 'custom');
    $site_name = get_bloginfo('name');
    $prefix = '【外链重定向增强 - ' . $site_name . '】';
    $full_message = $prefix . $message;

    switch ($format) {
        case 'wechat':
            // 企业微信机器人
            $body = array(
                'msgtype' => 'text',
                'text'    => array('content' => $full_message),
            );
            break;
        case 'dingtalk':
            // 钉钉机器人
            $body = array(
                'msgtype' => 'text',
                'text'    => array('content' => $full_message),
            );
            break;
        case 'feishu':
            // 飞书机器人
            $body = array(
                'msg_type' => 'text',
                'content'  => array('text' => $full_message),
            );
            break;
        default:
            // 自定义格式
            $body = array('content' => $full_message);
            break;
    }

    wp_remote_post($webhook_url, array(
        'timeout'  => 10,
        'blocking' => false,
        'headers'  => array('Content-Type' => 'application/json'),
        'body'     => wp_json_encode($body),
    ));
}

/**
 * 定时任务：下载远程黑名单并缓存为 PHP 文件
 */
add_action('wxs_go_update_blacklist', 'wxs_go_fetch_remote_blacklist');
function wxs_go_fetch_remote_blacklist() {
    $url = trim(wxs_go_option('remote_url', ''));
    if (empty($url)) {
        update_option('wxs_go_remote_status', array(
            'time'   => time(),
            'status' => 'error',
            'msg'    => '远程 URL 为空',
        ));
        return false;
    }

    $request_args = array(
        'timeout'   => 30,
        'sslverify' => false,
    );
    $custom_ua = trim(wxs_go_option('remote_user_agent', ''));
    if ($custom_ua !== '') {
        $request_args['user-agent'] = $custom_ua;
    }

    $response = wp_remote_get($url, $request_args);

    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        update_option('wxs_go_remote_status', array(
            'time'   => time(),
            'status' => 'error',
            'msg'    => $error_msg,
        ));
        wxs_go_send_webhook_notice('远程订阅下载失败：' . $error_msg . '（URL：' . $url . '）');
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        $error_msg = '远程内容为空';
        update_option('wxs_go_remote_status', array(
            'time'   => time(),
            'status' => 'error',
            'msg'    => $error_msg,
        ));
        wxs_go_send_webhook_notice('远程订阅更新失败：' . $error_msg . '（URL：' . $url . '）');
        return false;
    }

    // 解析内容为域名数组
    $format = wxs_go_option('blacklist_encoded', 'base64');
    $domains = wxs_go_parse_blacklist_content($body, $format);

    // 格式验证：解析结果为空视为格式错误，不覆盖已有缓存
    if (empty($domains)) {
        $error_msg = '格式解析失败，未能提取到有效域名（数据格式设置：' . $format . '）';
        update_option('wxs_go_remote_status', array(
            'time'   => time(),
            'status' => 'error',
            'msg'    => $error_msg,
        ));
        wxs_go_send_webhook_notice('远程订阅更新失败：' . $error_msg . '（URL：' . $url . '）');
        return false;
    }

    // 写入 PHP 缓存文件（原子写入）
    $result = wxs_go_write_cache_file($domains);

    if (!$result) {
        $error_msg = '写入缓存文件失败';
        update_option('wxs_go_remote_status', array(
            'time'   => time(),
            'status' => 'error',
            'msg'    => $error_msg,
        ));
        wxs_go_send_webhook_notice('远程订阅更新失败：' . $error_msg);
        return false;
    }

    update_option('wxs_go_remote_status', array(
        'time'   => time(),
        'status' => 'success',
        'msg'    => sprintf('成功，共 %d 条域名', count($domains)),
        'count'  => count($domains),
    ));

    // 清除对象缓存中的黑名单结果
    wp_cache_flush_group('wxs_go_blacklist');

    return true;
}

/**
 * 解析黑名单内容为域名数组
 */
function wxs_go_parse_blacklist_content($content, $format = 'plain') {
    if ($format === 'base64' || $format === 'autoproxy') {
        $decoded = base64_decode($content);
        if ($decoded !== false) {
            $content = $decoded;
        }
    }

    $lines = explode("\n", trim($content));
    $domains = array();

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        if ($format === 'autoproxy') {
            if ($line[0] === '!' || $line[0] === '[') {
                continue;
            }
            if (strpos($line, '@@') === 0 || strpos($line, '/') === 0) {
                continue;
            }
            if (strpos($line, '||') === 0) {
                $domain = substr($line, 2);
                $domain = rtrim($domain, '/^*');
                if (!empty($domain)) {
                    $domains[] = strtolower($domain);
                }
                continue;
            }
            if (strpos($line, '|') === 0) {
                continue; // URL 模式暂不支持缓存，仅域名
            }
            $line = ltrim($line, '.');
            if (!empty($line)) {
                $domains[] = strtolower($line);
            }
        } else {
            $line = ltrim($line, '.');
            if (!empty($line)) {
                $domains[] = strtolower($line);
            }
        }
    }

    return array_unique($domains);
}

/**
 * 写入 PHP 缓存文件（hashmap 格式，isset() O(1) 查找）
 */
function wxs_go_write_cache_file($domains) {
    $tmp_file = WXS_GO_CACHE_FILE . '.tmp';

    $handle = @fopen($tmp_file, 'w');
    if (!$handle) {
        return false;
    }

    fwrite($handle, "<?php\n");
    fwrite($handle, "// 外链重定向黑名单缓存 - 自动生成，请勿手动编辑\n");
    fwrite($handle, "// 更新时间: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "// 域名数量: " . count($domains) . "\n");
    fwrite($handle, "if (!defined('WXS_GO_BLACKLIST_LOAD')) { http_response_code(404); exit('Not Found'); }\n");
    fwrite($handle, "return array(\n");
    fwrite($handle, "    'updated' => " . time() . ",\n");
    fwrite($handle, "    'count' => " . count($domains) . ",\n");
    fwrite($handle, "    'domains' => array(\n");
    foreach ($domains as $domain) {
        fwrite($handle, "        '" . addslashes($domain) . "'=>1,\n");
    }
    fwrite($handle, "    ),\n");
    fwrite($handle, ");\n");
    fclose($handle);

    // 原子替换
    if (!@rename($tmp_file, WXS_GO_CACHE_FILE)) {
        // Windows rename 不覆盖，先删后改名
        @unlink(WXS_GO_CACHE_FILE);
        if (!@rename($tmp_file, WXS_GO_CACHE_FILE)) {
            @unlink($tmp_file);
            return false;
        }
    }

    // 清除 OPcache
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate(WXS_GO_CACHE_FILE, true);
    }

    return true;
}

/**
 * 读取 PHP 缓存文件中的域名列表（兼容 hashmap 格式）
 */
function wxs_go_load_cache_domains() {
    if (!file_exists(WXS_GO_CACHE_FILE)) {
        return array();
    }
    if (!defined('WXS_GO_BLACKLIST_LOAD')) {
        define('WXS_GO_BLACKLIST_LOAD', true);
    }
    $data = @include WXS_GO_CACHE_FILE;
    if (!is_array($data) || empty($data['domains'])) {
        return array();
    }
    return array_keys($data['domains']);
}

/**
 * AJAX 手动更新远程黑名单
 */
add_action('wp_ajax_wxs_go_update_remote', 'wxs_go_ajax_update_remote');
function wxs_go_ajax_update_remote() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('msg' => '权限不足'));
    }
    check_ajax_referer('wxs_go_update_remote', '_nonce');
    $result = wxs_go_fetch_remote_blacklist();
    if ($result) {
        wp_send_json_success(array('msg' => '更新成功'));
    } else {
        $status = get_option('wxs_go_remote_status', array());
        wp_send_json_error(array('msg' => isset($status['msg']) ? $status['msg'] : '更新失败'));
    }
}

// 黑名单检查

/**
 * 检查 URL 是否命中黑名单
 * @return bool true = 应拦截
 */
function wxs_go_check_blacklist($url) {
    $cache_key = 'wxs_go_bl_' . md5($url);
    $cached = wp_cache_get($cache_key, 'wxs_go_blacklist');
    if ($cached !== false) {
        return ($cached === '1');
    }

    $blocked = false;
    $source = wxs_go_option('blacklist_source', 'file');

    if ($source === 'manual') {
        // 手动编辑模式：纯域名列表
        $blocked = wxs_go_check_manual_blacklist($url);
    } else {
        // 外部数据源模式（本地文件或远程订阅）
        $data_type = wxs_go_option('blacklist_data_type', 'local');

        if ($data_type === 'remote') {
            // 远程订阅：从 PHP 缓存文件读取（已预处理为域名数组）
            $blocked = wxs_go_check_cache_blacklist($url);
        } else {
            // 本地文件：实时读取并解析
            $blocked = wxs_go_check_file_blacklist($url);
        }

        // 补充自定义黑名单（两种数据源都生效）
        if (!$blocked) {
            $extra = wxs_go_option('blacklist_extra', '');
            if ($extra) {
                $lines = explode("\n", trim($extra));
                foreach ($lines as $line) {
                    $line = trim(ltrim($line, '.'));
                    if (!empty($line) && wxs_go_match_domain($url, $line)) {
                        $blocked = true;
                        break;
                    }
                }
            }
        }
    }

    wp_cache_set($cache_key, $blocked ? '1' : '0', 'wxs_go_blacklist', 604800);
    return $blocked;
}

/**
 * 手动黑名单检查
 */
function wxs_go_check_manual_blacklist($url) {
    $content = wxs_go_option('blacklist_manual', '');
    if (empty($content)) {
        return false;
    }
    $lines = explode("\n", trim($content));
    foreach ($lines as $line) {
        $line = trim(ltrim($line, '.'));
        if (!empty($line) && wxs_go_match_domain($url, $line)) {
            return true;
        }
    }
    return false;
}

/**
 * 远程缓存黑名单检查（hashmap isset() O(1) 查找 + 子域名逐级匹配）
 */
function wxs_go_check_cache_blacklist($url) {
    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    if (!$host) {
        return false;
    }

    if (!defined('WXS_GO_BLACKLIST_LOAD')) {
        define('WXS_GO_BLACKLIST_LOAD', true);
    }

    if (!file_exists(WXS_GO_CACHE_FILE)) {
        return false;
    }

    $data = @include WXS_GO_CACHE_FILE;
    if (!is_array($data) || empty($data['domains'])) {
        return false;
    }

    $domains = $data['domains'];

    // 精确匹配
    if (isset($domains[$host])) {
        return true;
    }

    // 逐级去掉子域名尝试匹配（如 a.b.example.com → b.example.com → example.com）
    $parts = explode('.', $host);
    $count = count($parts);
    for ($i = 1; $i < $count - 1; $i++) {
        $parent = implode('.', array_slice($parts, $i));
        if (isset($domains[$parent])) {
            return true;
        }
    }

    return false;
}

/**
 * 本地文件黑名单检查
 * 首次请求时解析文件并生成 PHP hashmap 缓存，后续直接 include 缓存文件
 */
define('WXS_GO_LOCAL_CACHE_FILE', WP_CONTENT_DIR . '/wxs-go-blacklist-local-cache.php');

function wxs_go_check_file_blacklist($url) {
    $blacklist_file = trim(wxs_go_option('blacklist_file', ''));
    if (empty($blacklist_file)) {
        return false;
    }

    $blacklist_file = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $blacklist_file);

    // 扩展名白名单校验（仅允许纯文本文件）
    $allowed_ext = array('txt', 'list', 'conf', 'dat', 'csv');
    $ext = strtolower(pathinfo($blacklist_file, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        return false;
    }

    if (!file_exists($blacklist_file)) {
        return false;
    }

    // 检查缓存是否存在且有效（源文件未变更）
    $need_rebuild = true;
    if (file_exists(WXS_GO_LOCAL_CACHE_FILE)) {
        $cache_mtime  = filemtime(WXS_GO_LOCAL_CACHE_FILE);
        $source_mtime = filemtime($blacklist_file);
        if ($cache_mtime >= $source_mtime) {
            $need_rebuild = false;
        }
    }

    // 重建本地缓存
    if ($need_rebuild) {
        $format = wxs_go_option('blacklist_encoded', 'base64');
        // 大文件逐行读取，避免一次性载入内存
        $domains = wxs_go_parse_blacklist_file_streamed($blacklist_file, $format);

        // 格式验证：解析结果为空视为格式错误，不覆盖已有缓存
        if (empty($domains)) {
            wxs_go_send_webhook_notice('本地黑名单解析失败：未能提取到有效域名（文件：' . $blacklist_file . '，数据格式：' . $format . '）');
            // 如果旧缓存存在，继续使用旧缓存；否则返回 false
            if (!file_exists(WXS_GO_LOCAL_CACHE_FILE)) {
                return false;
            }
        } else {
            wxs_go_write_local_cache_file($domains);
        }
    }

    // 从缓存中查找
    if (!defined('WXS_GO_LOCAL_BLACKLIST_LOAD')) {
        define('WXS_GO_LOCAL_BLACKLIST_LOAD', true);
    }

    $data = @include WXS_GO_LOCAL_CACHE_FILE;
    if (!is_array($data) || empty($data['domains'])) {
        return false;
    }

    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    if (!$host) {
        return false;
    }

    $domains = $data['domains'];

    // 精确匹配
    if (isset($domains[$host])) {
        return true;
    }

    // 逐级子域名匹配
    $parts = explode('.', $host);
    $count = count($parts);
    for ($i = 1; $i < $count - 1; $i++) {
        $parent = implode('.', array_slice($parts, $i));
        if (isset($domains[$parent])) {
            return true;
        }
    }

    return false;
}

/**
 * 流式解析大文件黑名单（逐行读取，不一次性载入内存）
 */
function wxs_go_parse_blacklist_file_streamed($file, $format = 'plain') {
    $domains = array();

    // base64/autoproxy 格式需要整体解码，只能全量读取
    if ($format === 'base64' || $format === 'autoproxy') {
        $content = @file_get_contents($file);
        if (empty($content)) {
            return $domains;
        }
        return wxs_go_parse_blacklist_content($content, $format);
    }

    // 明文格式：逐行读取
    $handle = @fopen($file, 'r');
    if (!$handle) {
        return $domains;
    }

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        $line = ltrim($line, '.');
        if (!empty($line)) {
            $domains[] = strtolower($line);
        }
    }
    fclose($handle);

    return array_unique($domains);
}

/**
 * 写入本地文件黑名单的 PHP hashmap 缓存
 */
function wxs_go_write_local_cache_file($domains) {
    $tmp_file = WXS_GO_LOCAL_CACHE_FILE . '.tmp';

    $handle = @fopen($tmp_file, 'w');
    if (!$handle) {
        return false;
    }

    fwrite($handle, "<?php\n");
    fwrite($handle, "// 本地文件黑名单缓存 - 自动生成，请勿手动编辑\n");
    fwrite($handle, "// 更新时间: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "// 域名数量: " . count($domains) . "\n");
    fwrite($handle, "if (!defined('WXS_GO_LOCAL_BLACKLIST_LOAD')) { http_response_code(404); exit('Not Found'); }\n");
    fwrite($handle, "return array(\n");
    fwrite($handle, "    'updated' => " . time() . ",\n");
    fwrite($handle, "    'count' => " . count($domains) . ",\n");
    fwrite($handle, "    'domains' => array(\n");
    foreach ($domains as $domain) {
        fwrite($handle, "        '" . addslashes($domain) . "'=>1,\n");
    }
    fwrite($handle, "    ),\n");
    fwrite($handle, ");\n");
    fclose($handle);

    // 原子替换
    if (!@rename($tmp_file, WXS_GO_LOCAL_CACHE_FILE)) {
        @unlink(WXS_GO_LOCAL_CACHE_FILE);
        if (!@rename($tmp_file, WXS_GO_LOCAL_CACHE_FILE)) {
            @unlink($tmp_file);
            return false;
        }
    }

    if (function_exists('opcache_invalidate')) {
        opcache_invalidate(WXS_GO_LOCAL_CACHE_FILE, true);
    }

    return true;
}
