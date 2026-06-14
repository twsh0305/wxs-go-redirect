<?php
/**
 * GitHub Releases自动更新-外链重定向增强
 *
 * @package wxs-go-redirect
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 将GitHub Releases集成到WordPress插件更新流程中。
 */
final class WXS_Go_GitHubReleaseUpdater
{
    private const API_URL       = 'https://api.github.com/repos/twsh0305/wxs-go-redirect/releases/latest';
    private const RELEASES_URL  = 'https://github.com/twsh0305/wxs-go-redirect/releases';
    private const CACHE_KEY     = 'wxs_go_latest_release';
    private const CACHE_TTL     = 1800; // 30 分钟
    private const CACHE_ERR_TTL = 600;  // 10 分钟

    /**
     * 注册更新检测钩子。在插件主文件中调用
     */
    public static function init()
    {
        add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'filterUpdateTransient'));
        add_filter('plugins_api', array(__CLASS__, 'filterPluginInfo'), 10, 3);
        add_filter('upgrader_source_selection', array(__CLASS__, 'fixSourceDirectory'), 10, 4);
        // 保存设置后清除 Release 缓存，使镜像/源切换立即生效
        add_action('csf_wxs_go_redirect_options_saved', array(__CLASS__, 'flushReleaseCache'));
    }

    /**
     * 清除Release缓存
     */
    public static function flushReleaseCache()
    {
        delete_site_transient(self::CACHE_KEY);
        delete_site_transient(self::CACHE_KEY . '_err');
    }

    /**
     * 获取镜像前缀。返回空字符串表示官方直连。
     *
     * @return string
     */
    private static function mirrorPrefix()
    {
        if (!function_exists('wxs_go_option')) {
            return '';
        }
        $mirror = wxs_go_option('update_mirror', '');
        if ($mirror === 'custom') {
            $mirror = wxs_go_option('update_mirror_custom', '');
        }
        $mirror = trim((string) $mirror);
        if ($mirror === '' || !preg_match('#^https?://#i', $mirror)) {
            return '';
        }
        return trailingslashit($mirror);
    }

    /**
     * 为GitHub官方资源URL套上镜像前缀。
     *
     * 仅对GitHub官方域名生效，其他地址原样返回，避免误代理。
     *
     * @param string $url 原始GitHub URL
     * @return string
     */
    private static function applyMirror($url)
    {
        $prefix = self::mirrorPrefix();
        if ($prefix === '' || !is_string($url) || $url === '') {
            return $url;
        }
        // 仅代理GitHub官方域名
        if (!preg_match('#^https?://(api\.github\.com|github\.com|codeload\.github\.com|objects\.githubusercontent\.com|raw\.githubusercontent\.com)/#i', $url)) {
            return $url;
        }
        return $prefix . $url;
    }

    /**
     * 注入更新信息到WP更新transient
     */
    public static function filterUpdateTransient($transient)
    {
        if (!is_object($transient)) {
            return $transient;
        }

        $release    = self::getRelease();
        $pluginFile = plugin_basename(wxs_go_file);

        if (!$release || version_compare($release['version'], wxs_go_ver, '<=')) {
            // 无更新：写入no_update
            if (!isset($transient->no_update) || !is_array($transient->no_update)) {
                $transient->no_update = array();
            }
            $transient->no_update[$pluginFile] = self::buildUpdateObject(
                $release ? $release : self::currentRelease()
            );
            return $transient;
        }

        // 有更新：写入response
        if (!isset($transient->response) || !is_array($transient->response)) {
            $transient->response = array();
        }
        $transient->response[$pluginFile] = self::buildUpdateObject($release);

        return $transient;
    }

    /**
     * 提供查看详情弹窗内容
     */
    public static function filterPluginInfo($result, $action, $args)
    {
        if ($action !== 'plugin_information' || !is_object($args) || ($args->slug ?? '') !== self::slug()) {
            return $result;
        }

        $release = self::getRelease() ?: self::currentRelease();
        $info    = self::buildUpdateObject($release);

        $info->sections = array(
            'description'  => self::buildDescription(),
            'installation' => self::buildInstallation(),
            'faq'          => self::buildFaq(),
            'screenshots'  => self::buildScreenshots(),
            'changelog'    => self::formatChangelog($release['body'] ?? ''),
        );
        $info->banners = self::buildBanners();
        $info->icons   = self::buildIcons();

        return $info;
    }

    /**
     * 获取可用更新
     */
    public static function getAvailableUpdate()
    {
        $release = self::getRelease();
        if (!$release || version_compare($release['version'], wxs_go_ver, '<=')) {
            return null;
        }
        return $release;
    }

    /**
     * 强制刷新并检测更新
     */
    public static function refreshAvailableUpdate()
    {
        delete_site_transient(self::CACHE_KEY);
        delete_site_transient(self::CACHE_KEY . '_err');
        return self::getAvailableUpdate();
    }

    /**
     * 请求GitHub API并缓存结果
     */
    private static function getRelease()
    {
        $cached = get_site_transient(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        if (get_site_transient(self::CACHE_KEY . '_err')) {
            return null;
        }

        $response = wp_remote_get(self::applyMirror(self::API_URL), array(
            'timeout' => 10,
            'headers' => array(
                'Accept'     => 'application/vnd.github+json',
                'User-Agent' => self::slug() . '/' . wxs_go_ver,
            ),
        ));

        if (is_wp_error($response)) {
            set_site_transient(self::CACHE_KEY . '_err', 1, self::CACHE_ERR_TTL);
            return null;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $body   = (string) wp_remote_retrieve_body($response);
        if ($status < 200 || $status >= 300 || $body === '') {
            set_site_transient(self::CACHE_KEY . '_err', 1, self::CACHE_ERR_TTL);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || !empty($data['draft']) || !empty($data['prerelease'])) {
            return null;
        }

        $version = self::normalizeVersion(
            isset($data['tag_name']) && is_string($data['tag_name']) ? $data['tag_name'] : ''
        );
        if ($version === '') {
            return null;
        }

        $release = array(
            'version'      => $version,
            'tag'          => isset($data['tag_name']) && is_string($data['tag_name']) ? $data['tag_name'] : $version,
            'name'         => isset($data['name']) && is_string($data['name']) && $data['name'] !== '' ? $data['name'] : 'v' . $version,
            'body'         => isset($data['body']) && is_string($data['body']) ? $data['body'] : '',
            'published_at' => isset($data['published_at']) && is_string($data['published_at']) ? $data['published_at'] : '',
            'package'      => self::resolvePackageUrl($data),
            'homepage'     => isset($data['html_url']) && is_string($data['html_url']) ? esc_url_raw($data['html_url']) : self::RELEASES_URL,
        );

        delete_site_transient(self::CACHE_KEY . '_err');

        set_site_transient(self::CACHE_KEY, $release, self::CACHE_TTL);
        return $release;
    }

    /**
     * 读取插件主文件头部信息。
     *
     * @return array{requires:string,tested:string,requires_php:string}
     */
    private static function pluginHeaders()
    {
        static $headers = null;
        if ($headers !== null) {
            return $headers;
        }

        if (!function_exists('get_file_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $data = get_file_data(wxs_go_file, array(
            'RequiresWP'  => 'Requires at least',
            'TestedWP'    => 'Tested up to',
            'RequiresPHP' => 'Requires PHP',
        ));

        $headers = array(
            'requires'     => isset($data['RequiresWP']) && $data['RequiresWP'] !== '' ? $data['RequiresWP'] : '6.4',
            'tested'       => isset($data['TestedWP']) && $data['TestedWP'] !== '' ? $data['TestedWP'] : '6.9',
            'requires_php' => isset($data['RequiresPHP']) && $data['RequiresPHP'] !== '' ? $data['RequiresPHP'] : '7.4',
        );
        return $headers;
    }

    /**
     * 构造WP更新对象
     */
    private static function buildUpdateObject(array $release)
    {
        $headers = self::pluginHeaders();

        $object = new stdClass();
        $object->id             = self::RELEASES_URL;
        $object->slug           = self::slug();
        $object->plugin         = plugin_basename(wxs_go_file);
        $object->version        = (string) ($release['version'] ?? wxs_go_ver);
        $object->new_version    = (string) ($release['version'] ?? wxs_go_ver);
        $object->url            = (string) ($release['homepage'] ?? self::RELEASES_URL);
        $object->package        = (string) ($release['package'] ?? '');
        $object->tested         = $headers['tested'];
        $object->requires       = $headers['requires'];
        $object->requires_php   = $headers['requires_php'];
        $object->last_updated   = (string) ($release['published_at'] ?? '');
        $object->added          = (string) ($release['published_at'] ?? '');
        $object->name           = '外链重定向增强';
        $object->author         = '<a href="https://wxsnote.cn">天无神话</a>';
        $object->author_profile = 'https://wxsnote.cn';
        $object->homepage       = self::RELEASES_URL;
        // 右侧栏贡献者
        $object->contributors   = array(
            'twsh0305' => array(
                'profile'     => 'https://wxsnote.cn',
                'avatar'      => 'https://q.qlogo.cn/headimg_dl?dst_uin=2031301686&spec=140&img_type=jpg',
                'display_name' => '天无神话',
            ),
        );
        // 右侧栏捐赠链接
        $object->donate_link    = 'https://wxsnote.cn/zanzhu';
        $object->icons          = self::buildIcons();
        $object->banners        = self::buildBanners();

        return $object;
    }

    /**
     * 本地版本信息
     */
    private static function currentRelease()
    {
        return array(
            'version'      => wxs_go_ver,
            'homepage'     => self::RELEASES_URL,
            'package'      => '',
            'body'         => '',
            'published_at' => '',
        );
    }

    /**
     * 从Release附件或zipball中解析下载链接
     *
     * 优先级：
     * 1. Release Assets中的.zip文件
     * 2. GitHub自动生成的zipball_url
     */
    private static function resolvePackageUrl(array $data)
    {
        $assets = isset($data['assets']) && is_array($data['assets']) ? $data['assets'] : array();
        foreach ($assets as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $name = isset($asset['name']) && is_string($asset['name']) ? strtolower($asset['name']) : '';
            $url  = isset($asset['browser_download_url']) && is_string($asset['browser_download_url']) ? $asset['browser_download_url'] : '';
            if ($url !== '' && substr($name, -4) === '.zip') {
                return self::applyMirror(esc_url_raw($url));
            }
        }

        return isset($data['zipball_url']) && is_string($data['zipball_url']) ? self::applyMirror(esc_url_raw($data['zipball_url'])) : '';
    }

    /**
     * 去掉tag的v前缀，校验semver格式
     */
    private static function normalizeVersion($version)
    {
        $version = trim($version);
        $version = preg_replace('/^v/i', '', $version);
        return is_string($version) && preg_match('/^\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/', $version)
            ? $version
            : '';
    }

    /**
     * 格式化Release Body为HTML更新说明志
     */
    private static function formatChangelog($body)
    {
        $body = trim($body);
        if ($body === '') {
            return '暂无更新说明。';
        }
        return nl2br(esc_html($body));
    }

    /**
     * 构建图标数组
     */
    private static function buildIcons()
    {
        $icon = wxs_go_url . '/assets/img/default.png';
        return array(
            'default' => $icon,
        );
    }

    /**
     * 构建详情弹窗顶部banner横幅图。
     */
    private static function buildBanners()
    {
        $banners = array();
        $low  = wxs_go_path . 'assets/img/banner-772x250.png';
        $high = wxs_go_path . 'assets/img/banner-1544x500.png';
        if (file_exists($low)) {
            $banners['low'] = wxs_go_url . '/assets/img/banner-772x250.png';
        }
        if (file_exists($high)) {
            $banners['high'] = wxs_go_url . '/assets/img/banner-1544x500.png';
        }
        return $banners;
    }

    /**
     * 详情弹窗描述标签内容
     */
    private static function buildDescription()
    {
        return '<p>配合 <strong>子比主题</strong> 外链重定向 + 鉴权功能的增强插件，解决各类缓存插件导致的外链重定向nonce鉴权失效问题，并提供多风格跳转页模板和域名黑名单拦截。</p>'
            . '<h4>主要功能</h4>'
            . '<ul style="list-style:disc;margin-left:20px;">'
            . '<li><strong>Nonce动态刷新</strong>：JS异步获取最新nonce，解决页面缓存导致的链接失效。</li>'
            . '<li><strong>域名黑名单拦截</strong>：支持本地文件 / 远程订阅 / 手动编辑多种来源。</li>'
            . '<li><strong>多风格跳转模板</strong>：内置 10 套跳转页模板，可自由扩展。</li>'
            . '<li><strong>特色图标贴纸</strong>：10 款原创插画，区分正常 / 拦截状态。</li>'
            . '<li><strong>缓存插件感知</strong>：自动检测已安装缓存插件并给出建议。</li>'
            . '<li><strong>GitHub自动更新</strong>：支持镜像加速、下载包内容校验。</li>'
            . '</ul>'
            . '<h4>依赖</h4>'
            . '<p>需安装并启用 <strong>子比主题（Zibll）</strong>，且在主题设置中同时开启外链重定向和外链重定向鉴权。</p>';
    }

    /**
     * 详情弹窗安装标签内容
     */
    private static function buildInstallation()
    {
        return '<ol style="margin-left:20px;">'
            . '<li>确认已安装并启用 <strong>子比主题（Zibll）</strong>。</li>'
            . '<li>在子比主题设置中开启外链重定向和外链重定向鉴权。</li>'
            . '<li>在插件 → 安装插件 → 上传插件中上传本插件 zip 包。</li>'
            . '<li>启用插件后，进入后台外链重定向增强菜单进行配置。</li>'
            . '<li>如站点启用了页面缓存，建议开启JS动态刷新重定向链接。</li>'
            . '</ol>';
    }

    /**
     * 详情弹窗常见问题标签内容
     */
    private static function buildFaq()
    {
        return '<h4>为什么外链点击后提示链接失效？</h4>'
            . '<p>页面被缓存后nonce过期所致。请开启JS动态刷新重定向链接，并清除一次缓存。</p>'
            . '<h4>插件提示依赖子比主题？</h4>'
            . '<p>本插件依赖子比主题的外链重定向功能，需先安装启用子比主题并开启相关选项。</p>'
            . '<h4>国内服务器更新插件超时怎么办？</h4>'
            . '<p>到外链重定向增强 → 更新设置中选择一个GitHub加速镜像，或填写自定义镜像地址。</p>'
            . '<h4>如何新增跳转页模板？</h4>'
            . '<p>将带模板头注释的 PHP 文件放入插件 <code>templates/</code> 目录即可自动识别。</p>';
    }

    /**
     * 详情弹窗屏幕截图标签内容。
     */
    private static function buildScreenshots()
    {
        // 截图描述：键为截图序号，值为说明文字
        $captions = array(
            1 => '插件基本设置 - 总开关与Nonce动态刷新',
            2 => '跳转页模板选择 - 多风格可视化切换',
            3 => '域名黑名单设置 - 本地 / 远程订阅',
            4 => '跳转页前台效果 - 倒计时与安全提示',
        );

        $html = '';
        for ($i = 1; $i <= 6; $i++) {
            $file = wxs_go_path . 'assets/img/screenshot-' . $i . '.png';
            if (!file_exists($file)) {
                continue;
            }
            $url     = wxs_go_url . '/assets/img/screenshot-' . $i . '.png';
            $caption = isset($captions[$i]) ? $captions[$i] : '';
            $html   .= '<li><img src="' . esc_url($url) . '" alt="' . esc_attr($caption) . '" style="max-width:100%;height:auto;">'
                . ($caption !== '' ? '<p>' . esc_html($caption) . '</p>' : '')
                . '</li>';
        }
        if ($html === '') {
            return '<p>暂无截图。</p>';
        }
        return '<ol>' . $html . '</ol>';
    }

    /**
     * 返回插件slug
     */
    private static function slug()
    {
        return dirname(plugin_basename(wxs_go_file));
    }

    /**
     * 修正zipball解压目录名，并校验下载包内容。
     */
    public static function fixSourceDirectory($source, $remote_source, $upgrader, $hook_extra)
    {
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== plugin_basename(wxs_go_file)) {
            return $source;
        }

        $main_file = trailingslashit($source) . 'wxs-go-redirect.php';
        if (!file_exists($main_file)) {
            return new WP_Error(
                'wxs_go_invalid_package',
                '更新失败：下载的安装包中未找到插件主文件，可能下载到了错误或损坏的资源，已中止更新以保护现有插件。'
            );
        }

        if (!function_exists('get_file_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $data = get_file_data($main_file, array(
            'Name'       => 'Plugin Name',
            'TextDomain' => 'Text Domain',
        ));

        $name_ok   = isset($data['Name']) && $data['Name'] === '外链重定向增强';
        $domain_ok = isset($data['TextDomain']) && $data['TextDomain'] === 'wxs-go-redirect';
        if (!$name_ok && !$domain_ok) {
            return new WP_Error(
                'wxs_go_invalid_package',
                '更新失败：下载包内容与本插件不匹配（插件标识校验未通过），已中止更新以保护现有插件。请检查更新源或镜像地址是否正确。'
            );
        }

        $slug = self::slug();
        if (basename($source) === $slug) {
            return $source;
        }

        $new_source = trailingslashit(dirname($source)) . $slug;
        if (!rename($source, $new_source)) {
            return $source;
        }

        return $new_source;
    }
}
