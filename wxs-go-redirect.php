<?php
/**
 * Plugin Name: 外链重定向增强
 * Description: 配合子比主题外链重定向+鉴权功能，解决各类缓存插件导致的外链重定向 nonce 鉴权失效问题，提供多风格跳转页模板和域名黑名单拦截。依赖子比主题同时开启「外链重定向」和「外链重定向鉴权」。
 * Version: 1.0.0
 * Requires at least: 6.4
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * Author: 天无神话
 * Author URI: https://wxsnote.cn
 * License: GPL v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wxs-go-redirect
 */

if (!defined('ABSPATH')) {
    exit;
}

define('wxs_go_url', plugins_url('', __FILE__));
define('wxs_go_path', plugin_dir_path(__FILE__));
define('wxs_go_ver', '1.0.0');
define('wxs_go_file', __FILE__);

// 加载GitHub更新检测
$wxs_go_updater_file = wxs_go_path . 'src/Update/GitHubReleaseUpdater.php';
if (file_exists($wxs_go_updater_file)) {
    require_once $wxs_go_updater_file;
    WXS_Go_GitHubReleaseUpdater::init();
}

// 插件列表页「设置」入口
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wxs_go_plugin_action_links');
function wxs_go_plugin_action_links($links) {
    $settings = '<a href="' . esc_url(admin_url('admin.php?page=wxs-go-redirect')) . '">设置</a>';
    array_unshift($links, $settings);
    return $links;
}

// 主题依赖检测
if (!function_exists('wp_get_theme')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$wxs_go_theme = wp_get_theme();
if ($wxs_go_theme->get_stylesheet() !== 'zibll') {
    add_action('admin_notices', 'wxs_go_notice_no_theme');
    return;
}
function wxs_go_notice_no_theme() {
    echo '<div class="notice notice-error"><p><strong>【外链重定向增强】</strong>依赖 Zibll 子比主题，请先安装并启用。</p></div>';
}

// 插件选项读取
function wxs_go_option($key = '', $default = null) {
    static $options = null;
    if ($options === null) {
        $options = get_option('wxs_go_redirect_options', array());
    }
    if ($key === '') {
        return $options;
    }
    return isset($options[$key]) ? $options[$key] : $default;
}

// 条件判断：子比主题是否满足前置条件
function wxs_go_theme_conditions_met() {
    // 必须同时开启「外链重定向」和「外链重定向鉴权」
    return (_pz('go_link_s') && _pz('go_link_nonce_s'));
}

// 全局启用判断
function wxs_go_is_enabled() {
    // 插件总开关 + 子比前置条件同时满足
    return (wxs_go_option('enable', true) && wxs_go_theme_conditions_met());
}

// 后台禁用原因提示
add_action('admin_notices', 'wxs_go_notice_disabled_reason');
function wxs_go_notice_disabled_reason() {
    // 仅在插件设置页或插件列表页显示
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    // 插件总开关关闭
    if (!wxs_go_option('enable', true)) {
        echo '<div class="notice notice-warning"><p><strong>【外链重定向增强】</strong>插件总开关已关闭，所有增强功能处于禁用状态。</p></div>';
        return;
    }

    // 子比条件不满足
    if (!wxs_go_theme_conditions_met()) {
        $reasons = array();
        if (!_pz('go_link_s')) {
            $reasons[] = '「外链重定向」未开启';
        }
        if (!_pz('go_link_nonce_s')) {
            $reasons[] = '「外链重定向鉴权」未开启';
        }
        echo '<div class="notice notice-warning"><p><strong>【外链重定向增强】</strong>功能已禁用，原因：子比主题 ' . implode('、', $reasons) . '。请在子比主题设置中同时开启这两项。</p></div>';
    }
}

// 激活钩子
register_activation_hook(__FILE__, 'wxs_go_activation');
function wxs_go_activation() {
    if (!get_option('wxs_go_redirect_options')) {
        $defaults = array(
            'enable'            => '1',
            'blacklist_source'  => 'file',
            'blacklist_file'    => WP_CONTENT_DIR . '/blacklisted-domain.txt',
            'blacklist_encoded' => 'base64',
            'blacklist_manual'  => '',
            'blacklist_extra'   => '',
            'countdown'         => '5',
        );
        update_option('wxs_go_redirect_options', $defaults);
    }
}

// 加载核心函数
$wxs_go_func_file = wxs_go_path . 'inc/functions.php';
if (file_exists($wxs_go_func_file)) {
    require_once $wxs_go_func_file;
}

// 加载跳转页路由
$wxs_go_page_file = wxs_go_path . 'inc/go-page.php';
if (file_exists($wxs_go_page_file)) {
    require_once $wxs_go_page_file;
}

// 加载 CSF 选项
add_action('after_setup_theme', 'wxs_go_init_options');
function wxs_go_init_options() {
    $options_file = wxs_go_path . 'inc/options.php';
    if (file_exists($options_file)) {
        require_once $options_file;
    }
}
