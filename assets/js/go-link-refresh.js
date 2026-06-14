/**
 * 外链重定向增强 - Nonce 动态刷新
 *
 * 策略：
 * - nonce 值基于 WP tick，同一 tick 内 nonce 不变，跳过 AJAX 避免无意义请求
 * - MutationObserver 监听 DOM，新出现的 golink 自动刷新
 */
(function () {
    'use strict';

    var CONFIG = window.wxs_go_config || {};
    if (!CONFIG.enable) {
        return;
    }

    var AJAX_URL     = CONFIG.ajax_url || '/wp-admin/admin-ajax.php';
    var TICK_DUR     = CONFIG.tick_duration || 43200;   // 秒
    var PAGE_TICK    = CONFIG.nonce_tick || 0;           // 页面生成时的 tick

    // 缓存：本次会话已获取的最新 nonce 及对应 tick
    var _cachedNonce = null;
    var _cachedTick  = PAGE_TICK;

    // Tick 计算

    function now() {
        return Math.floor(Date.now() / 1000);
    }

    function currentTick() {
        return Math.ceil(now() / TICK_DUR);
    }

    /**
     * nonce 是否需要刷新？
     * 当前 tick > 缓存 tick 时 nonce 值才会变化
     */
    function needsRefresh() {
        return _cachedNonce === null || currentTick() > _cachedTick;
    }

    // AJAX 获取最新 nonce

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

    // 链接查找与更新

    function findGoLinks(root) {
        root = root || document;
        return root.querySelectorAll('a[href*="golink="][href*="nonce="]');
    }

    function updateLinks(nonce) {
        if (!nonce) return 0;
        var links = findGoLinks();
        var updated = 0;
        for (var i = 0; i < links.length; i++) {
            var href = links[i].getAttribute('href');
            if (!href) continue;
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
        if (findGoLinks().length === 0) return;
        if (!needsRefresh()) return;

        fetchNonce(function (nonce) {
            _cachedNonce = nonce;
            _cachedTick  = currentTick();
            updateLinks(nonce);
        });
    }

    /**
     * 对已有/新增链接应用当前 nonce
     * 如果 nonce 已过期则触发 refresh，否则直接用缓存值
     */
    function applyCached() {
        if (findGoLinks().length === 0) return;
        if (needsRefresh()) {
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
                    if (el.matches && el.matches('a[href*="golink="][href*="nonce="]')) {
                        debounceCheck();
                        return;
                    }
                    // 新增节点的子元素中包含 golink
                    if (el.querySelectorAll && el.querySelectorAll('a[href*="golink="][href*="nonce="]').length > 0) {
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

    // 定时轮询：tick 翻页时自动刷新 nonce

    function scheduleTickRefresh() {
        var nextTickTime = _cachedTick * TICK_DUR; // 当前 tick 的结束时间（秒）
        var delay = (nextTickTime - now() + 1) * 1000; // 距离下次 tick 的毫秒数
        if (delay < 1000) delay = 1000; // 最少 1 秒

        setTimeout(function () {
            refresh();
            scheduleTickRefresh(); // 刷新后重新调度
        }, delay);
    }

    // 初始化

    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                refresh();
                watchDOM();
                scheduleTickRefresh();
            });
        } else {
            refresh();
            watchDOM();
            scheduleTickRefresh();
        }
    }

    init();
})();
