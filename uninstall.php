<?php
/**
 * 外链重定向增强 - 卸载清理
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 清理插件选项
delete_option('wxs_go_redirect_options');
delete_option('wxs_go_remote_status');

// 清理定时任务
wp_clear_scheduled_hook('wxs_go_update_blacklist');

// 清理 PHP 缓存文件
$cache_file = WP_CONTENT_DIR . '/wxs-go-blacklist-cache.php';
if (file_exists($cache_file)) {
    @unlink($cache_file);
}
$tmp_file = $cache_file . '.tmp';
if (file_exists($tmp_file)) {
    @unlink($tmp_file);
}

// 多站点清理
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('wxs_go_redirect_options');
        delete_option('wxs_go_remote_status');
        wp_clear_scheduled_hook('wxs_go_update_blacklist');
        restore_current_blog();
    }
}

wp_cache_flush();
