# 外链重定向增强 (wxs-go-redirect)

> 配合[子比主题](https://zibll.com)外链重定向功能的增强插件，解决各类缓存插件导致的外链重定向 nonce 鉴权失效问题，提供多风格跳转页模板和域名黑名单拦截。

## 功能

- **Nonce 动态刷新**：JS 异步获取 nonce，解决页面缓存导致 nonce 过期失效的问题
- **域名黑名单拦截**：支持本地/远程订阅黑名单，拦截违规域名跳转
- **多风格跳转模板**：内置 10 套跳转页模板（知乎、稀土掘金、少数派、简书、Gitee、玻璃拟态等风格）
- **贴纸/特色图标**：10 款原创插画贴纸，区分 clear/blocked 状态
- **缓存插件感知**：自动检测已安装的缓存插件并提供清除建议
- **GitHub 自动更新**：通过 GitHub Releases 检测新版本

## 依赖

- **子比主题 (Zibll)** 已安装并启用
- 子比主题设置中同时开启「外链重定向」和「外链重定向鉴权」

## 安装

1. 下载 [最新版本](https://github.com/twsh0305/wxs-go-redirect/releases)
2. 在 WordPress 后台「插件 → 安装插件 → 上传插件」中上传 zip 包
3. 激活插件
4. 进入「外链重定向增强」设置页面进行配置

## 配置

- **插件总开关**：控制所有增强功能的启停
- **Nonce 刷新**：开启前端 JS 动态刷新 nonce
- **域名黑名单**：支持本地列表和远程订阅 URL，定时自动更新
- **调试模式**：可缩短 nonce 生命周期用于开发调试
- **跳转页模板**：在模板列表中选择并预览不同风格

## 模板

| 模板 | 说明 |
|------|------|
| `default` | 默认简洁风格 |
| `zhihu` | 知乎风格 |
| `juejin` | 稀土掘金风格 |
| `sspai` | 少数派风格 |
| `jianshu` | 简书风格 |
| `gitee` | Gitee 风格 |
| `zibll` | 子比主题风格 |
| `glassmorphism` | 玻璃拟态风格 |
| `gradient` | 渐变风格 |
| `matrix` | 矩阵风格 |

## 开源协议

本项目采用 [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html) 开源协议。

- **保留作者版权信息**：所有副本和衍生作品中必须保留原作者版权声明，**不得删除或修改作者信息**
- **Copyleft 传染**：任何基于本项目的修改、衍生作品必须同样以 GPL v3.0 开源
- **禁止闭源商用**：禁止将本项目或其衍生作品闭源后销售获利

> **关于倒卖**：GPL v3.0 虽然允许收费分发，但要求分发者必须同时提供完整源代码且保持 GPL v3.0 协议。任何购买者都有权免费再分发，使得单纯倒卖缺乏商业可行性。若发现他人未经授权删除作者信息后销售，可通过 GPL 协议追究侵权责任。

## 贡献者

<p align="center">
  <a href="https://github.com/twsh0305/wxs-go-redirect/graphs/contributors">
    <img src="https://contrib.rocks/image?repo=twsh0305/wxs-go-redirect&max=30" alt="Contributors">
  </a>
</p>

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=twsh0305/wxs-go-redirect&type=Date)](https://star-history.com/#twsh0305/wxs-go-redirect&Date)

## 作者信息

- QQ：2031301686 | [加好友](https://qm.qq.com/q/aKlJlhh4VU)
- QQ群：399019539 | [加群](https://jq.qq.com/?_wv=1027&k=eiGEOg3i)
- GitHub：[twsh0305](https://github.com/twsh0305)
- 项目地址：[wxs-go-redirect](https://github.com/twsh0305/wxs-go-redirect)
- 介绍文章：[wxsnote.cn/8284.html](https://wxsnote.cn/8284.html)

## 更新日志

### v1.0.0
- 重构 nonce 刷新机制，兼容各类页面缓存插件
- 新增 10 套跳转页模板系统
- 新增域名黑名单远程订阅
- 新增 GitHub Releases 自动更新
- 新增缓存插件感知检测
