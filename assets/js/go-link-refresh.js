/**
 * 外链重定向增强 - Nonce 动态刷新
 *
 * 兼容两种鉴权模式：
 * - 9.1 及以前：全局 WP nonce，同一 tick 内不变，跳过无意义请求
 * - 9.2+：HMAC 目标地址签名，每个链接独立且约 1 小时过期，需按链接批量续签
 */
(function () {
    'use strict';

    var CONFIG = window.wxs_go_config || {};
    if (!CONFIG.enable) {
        return;
    }

    var AJAX_URL      = CONFIG.ajax_url || '/wp-admin/admin-ajax.php';
    var SIGN_MODE     = !!CONFIG.sign_mode;
    var TICK_DUR      = CONFIG.tick_duration || 43200;   // 秒
    var PAGE_TICK     = CONFIG.nonce_tick || 0;          // 页面生成时的 tick
    var REFRESH_DUR   = (CONFIG.refresh_interval || 1800) * 1000;
    var LINK_SELECTOR = 'a[href*="golink="][href*="nonce="]';

    // 缓存：9.1 模式下的最新 nonce 及对应 tick
    var _cachedNonce = null;
    var _cachedTick  = PAGE_TICK;

    // Tick 计算（仅 9.1 模式使用）

    function now() {
        return Math.floor(Date.now() / 1000);
    }

    function currentTick() {
        return Math.ceil(now() / TICK_DUR);
    }

    /**
     * nonce 是否需要刷新？
     * 9.2+ 签名绑定目标地址且可能随页面缓存过期，需按链接重新获取
     */
    function needsRefresh() {
        if (SIGN_MODE) {
            return true;
        }
        return _cachedNonce === null || currentTick() > _cachedTick;
    }

    // 链接查找与解析

    function findGoLinks(root) {
        root = root || document;
        return root.querySelectorAll(LINK_SELECTOR);
    }

    /**
     * 从 href 中提取 golink 原始值（base64，可能含 + / = 等字符）
     */
    function rawGolink(href) {
        if (!href) return '';
        var m = href.match(/[?&]golink=([^&]*)/);
        if (!m) return '';
        try {
            return decodeURIComponent(m[1]);
        } catch (e) {
            return m[1];
        }
    }

    // AJAX 获取

    /**
     * 9.1：获取全局 nonce
     */
    function fetchNonce(callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', AJAX_URL + '?action=wxs_go_nonce&_=' + now(), true);
        xhr.timeout = 5000;
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success && resp.data && resp.data.nonce) {
                        callback(resp.data.nonce);
                    }
                } catch (e) {}
            }
        };
        xhr.onerror = function () {};
        xhr.ontimeout = function () {};
        xhr.send();
    }

    /**
     * 9.2+：按目标地址批量生成签名
     */
    function fetchSigns(golinks, callback) {
        var query = 'action=wxs_go_nonce&_=' + now();
        for (var i = 0; i < golinks.length; i++) {
            query += '&golinks%5B%5D=' + encodeURIComponent(golinks[i]);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', AJAX_URL + '?' + query, true);
        xhr.timeout = 5000;
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success && resp.data && resp.data.nonces) {
                        callback(resp.data.nonces);
                    }
                } catch (e) {}
            }
        };
        xhr.onerror = function () {};
        xhr.ontimeout = function () {};
        xhr.send();
    }

    // 链接更新

    /**
     * @param {string|Object} nonces 9.1 为单值字符串，9.2+ 为 {golink: 签名}
     */
    function updateLinks(nonces) {
        var links = findGoLinks();
        var updated = 0;

        for (var i = 0; i < links.length; i++) {
            var href = links[i].getAttribute('href');
            if (!href) continue;

            var nonce = '';
            if (SIGN_MODE) {
                nonce = nonces[rawGolink(href)] || '';
            } else {
                nonce = nonces;
            }
            if (!nonce) continue;

            var newHref = href.replace(/([?&])nonce=[^&]*/, '$1nonce=' + encodeURIComponent(nonce));
            if (newHref !== href) {
                links[i].setAttribute('href', newHref);
                updated++;
            }
        }

        if (updated > 0 && window.console) {
            console.log('[外链重定向] 已刷新 ' + updated + ' 个链接的 nonce');
        }
        return updated;
    }

    // 刷新调度

    function refresh() {
        var links = findGoLinks();
        if (links.length === 0) return;

        if (SIGN_MODE) {
            // 收集去重后的目标地址，一次请求批量签名
            var golinks = [];
            var seen = {};
            for (var i = 0; i < links.length; i++) {
                var g = rawGolink(links[i].getAttribute('href'));
                if (g && !seen[g]) {
                    seen[g] = 1;
                    golinks.push(g);
                }
            }
            if (golinks.length === 0) return;
            fetchSigns(golinks, updateLinks);
            return;
        }

        if (!needsRefresh()) return;
        fetchNonce(function (nonce) {
            _cachedNonce = nonce;
            _cachedTick  = currentTick();
            updateLinks(nonce);
        });
    }

    /**
     * 对已有/新增链接应用当前 nonce
     * 9.1 模式下 nonce 未过期时直接用缓存值，避免重复请求
     */
    function applyCached() {
        if (findGoLinks().length === 0) return;
        if (SIGN_MODE || needsRefresh()) {
            refresh();
        } else if (_cachedNonce) {
            updateLinks(_cachedNonce);
        }
    }

    // DOM 动态监听

    function watchDOM() {
        if (typeof MutationObserver === 'undefined') return;

        var timer = null;

        function debounceCheck() {
            if (timer) clearTimeout(timer);
            timer = setTimeout(applyCached, 200);
        }

        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var nodes = mutations[i].addedNodes;
                for (var j = 0; j < nodes.length; j++) {
                    var el = nodes[j];
                    if (el.nodeType !== 1) continue; // 跳过文本节点

                    // 新增节点本身是 golink 链接
                    if (el.matches && el.matches(LINK_SELECTOR)) {
                        debounceCheck();
                        return;
                    }
                    // 新增节点的子元素中包含 golink
                    if (el.querySelectorAll && el.querySelectorAll(LINK_SELECTOR).length > 0) {
                        debounceCheck();
                        return;
                    }
                }
            }
        });

        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }

    // 定时刷新：9.2+ 按固定间隔续签，9.1 按 tick 翻页刷新

    function scheduleRefresh() {
        if (SIGN_MODE) {
            setTimeout(function () {
                refresh();
                scheduleRefresh();
            }, REFRESH_DUR);
            return;
        }

        var nextTickTime = _cachedTick * TICK_DUR; // 当前 tick 的结束时间（秒）
        var delay = (nextTickTime - now() + 1) * 1000; // 距离下次 tick 的毫秒数
        if (delay < 1000) delay = 1000; // 最少 1 秒

        setTimeout(function () {
            refresh();
            scheduleRefresh(); // 刷新后重新调度
        }, delay);
    }

    // 初始化

    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                refresh();
                watchDOM();
                scheduleRefresh();
            });
        } else {
            refresh();
            watchDOM();
            scheduleRefresh();
        }
    }

    init();
})();
