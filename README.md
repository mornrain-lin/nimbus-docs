# Nimbus Docs

文档与知识库 WordPress 主题。左侧固定目录树 + 右侧正文 + 站内搜索，目录折叠状态本地记忆，文档反馈按钮、代码复制、上下节导航、打印友好样式。零外部资源依赖。

- 文本域：`nimbus-docs`
- 版本：1.0.0
- 需要 WordPress：6.0 及以上
- 需要 PHP：7.4 及以上
- 测试到：WordPress 6.6
- 许可证：MIT

---

## 简介

文档站的痛点不在「写」，在「读」和「评」：

- **读**：长文档需要一个能跳转到任意小节的目录。WordPress 自带的页面层级可以做章节树，但页内小节（h2 / h3）没有目录，读者只能滚动找。
- **找**：默认搜索只返回标题匹配的页面，摘要里的关键词命中不了。
- **评**：文档写得好不好，没有反馈渠道，作者只能凭感觉迭代。
- **打印**：技术文档经常被打印成 PDF，网页样式直接打出来往往很难看。

Nimbus Docs 把这四件事都做进主题：

- 左侧两级目录：**页面树**（按父子层级 + 菜单顺序，服务端渲染）+ **页内锚点**（当前页的 h2 - h4，客户端生成）。当前页自动高亮并展开所有祖先。
- 折叠状态用 `localStorage` 记忆，键名按站点 `home_url()` 哈希区分，刷新和跨页面都不丢。
- 搜索结果页的摘要关键词高亮，区间合并算法不会破坏 HTML 结构。
- 每篇文档底部「这篇文档对你有帮助吗？」两个按钮，AJAX 提交，数据存 postmeta，后台文章列表直接显示👍/👎 与好评率。
- 打印样式：隐藏所有交互元素、展开折叠内容、避免代码块和表格跨页断裂、外部链接自动补 URL。

## 特性

### 目录树

| 层级 | 来源 | 生成方式 |
| --- | --- | --- |
| 页面树 | 全部已发布页面（可指定 ID 筛选） | 服务端 PHP，按 `post_parent` 建树、`menu_order` 排序 |
| 页内锚点 | 当前页 `.nb-content` 的 h2 - h4 | 客户端 JS，扫描标题并生成嵌套列表 |

- 页面树每项带折叠按钮，展开状态按页面 ID 记入 `localStorage`。
- 整棵树有独立的「收起 / 展开」开关，状态同样记忆。
- 当前页链接自动标记 `aria-current="page"` 并高亮，同时展开所有祖先层级。
- 移动端侧栏变为抽屉式，带遮罩、`Esc` 关闭、点击链接后自动收起、视口变宽时自动复位。

### 搜索增强

- 头部搜索框常驻，按 `/` 直接聚焦并全选。
- 搜索结果页显示相对路径（`/docs/getting-started/`）便于判断内容位置。
- 摘要中的关键词以 `<mark>` 标注，支持多关键词与区间合并。
- 404 页的「搜索文档」按钮会聚焦头部搜索框。

### 文档反馈

- 前端：「有帮助 / 没帮助」两个按钮，点击后立即给出选中反馈，再向 `admin-ajax.php` 提交。
- 后端：Nonce 校验 + 值白名单（`up` / `down`）+ 文章存在性与发布状态校验。
- 防刷：同一 IP + 同一文章 1 小时内只计一次（IP 存哈希，混入 `wp_salt()`，不存明文）。
- 数据：`/nimbus_feedback_up` `/nimbus_feedback_down` `/nimbus_feedback_log` 三条 postmeta。
- 后台文章 / 页面列表新增「文档反馈」列，显示 👍 数、👎 数与好评率。
- 反馈成功后触发 `nimbus_feedback_submitted` 动作，可对接 Issue 系统或工单工具。

### 上下节导航

不只按时间顺序取上一篇 / 下一篇，而是**优先在同章节内导航**：

1. 找到当前页面的父页面
2. 拉取所有兄弟页面（按 `menu_order` + 标题排序）
3. 取当前页的前一个 / 后一个
4. 找不到时才回落到全站上一篇 / 下一篇（且要求父页面相同）

对于层级化的文档站，这比时间顺序合理得多。

### 代码增强

- 每个 `<pre>` 自动包裹复制按钮容器（带 Nonce-free 的纯前端交互）。
- `Clipboard API` 优先，非 HTTPS 环境下自动降级到 `execCommand('copy')`。
- 按钮有「复制 → 已复制 / 复制失败」三态，2 秒后复位。
- 标题自动补锚点链接（服务端为已有 id 的 h2 / h3 注入，客户端为缺失 id 的补齐）。
- 代码块加 `no-copy` 或 `language-none` class 可关闭复制按钮。

### 提示框短代码

```
[nimbus_notice type="tip"]默认提示[/nimbus_notice]
[nimbus_notice type="warning" title="升级前必读"]…[/nimbus_notice]
[nimbus_notice type="danger"]…[/nimbus_notice]
[nimbus_notice type="success"]…[/nimbus_notice]
```

`type` 支持 `tip`（默认）/ `warning` / `danger` / `success`，`title` 可自定义。

### 宽对齐支持

- `add_theme_support( 'align-wide' )` 并指定 `contentSize: 780` / `wideSize: 1120`。
- `.alignwide` 突破正文栏宽，`.alignfull` 铺满视口。
- 这**不是**页面构建器的容器，但足以支持「宽表格 + 说明文字」「全宽流程图 + 窄栏代码」这类文档排版。
- 与 Elementor / Divi 等页面构建器无绑定，也不捆绑任何构建器。

### 打印友好

- `@page` 设置 18mm × 16mm 页边距。
- 隐藏头部、侧栏、反馈按钮、上下节导航、复制按钮、锚点链接。
- 展开所有折叠的目录内容。
- 标题 `page-break-after: avoid`，代码块 / 引用 / 图表 / 表格 `page-break-inside: avoid`。
- 外部链接自动在括号内补上 URL，页内锚点链接不补。
- `alignfull` / `alignwide` 在打印时回落到栏宽，不出血。

### 其他

- Widget 区域：侧栏 1 个（目录下方）+ 页脚 2 个。
- 层级页面的面包屑路径。
- `prefers-reduced-motion` 降级、`:focus-visible` 焦点样式。
- PHP 7.4 兼容，未使用 PHP 8 独有语法。

## 截图说明

WordPress 后台的主题截图要求尺寸为 **1200 × 900 像素**（PNG 格式）。

- **文件路径**：`screenshot.png`（放在主题根目录）
- **推荐内容**：以单篇文档页为主视角。左侧是固定的目录树（能看到展开的子项和折叠按钮），右侧是文档正文（含清晰的 h1 / h2 层级、代码块、一个提示框），正文下方是「这篇文档对你有帮助吗？」的两个按钮和上下节导航。顶部是带头部搜索框的固定头部。
- **建议分辨率**：1200 × 900 px，24 位色 PNG，文件体积控制在 200KB 以内。

> 本仓库为纯代码分发，未附带二进制截图文件；安装后请按上述说明自行补充 `screenshot.png`。

## 安装

### 从后台安装（推荐）

1. 把主题目录打包成 `nimbus-docs.zip`，确保压缩包内层是 `nimbus-docs/` 目录。
2. 进入 **外观 → 主题 → 添加新主题 → 上传主题**。
3. 点击 **启用**。

### 通过 FTP / SSH 上传

1. 将 `nimbus-docs` 目录上传到 `wp-content/themes/`。
2. 进入 **外观 → 主题**，点击 **启用**。

### 本地开发

```bash
git clone https://github.com/mornrain/nimbus-docs.git
cd nimbus-docs
php -l functions.php   # 语法自检
```

## 主题配置项

进入 **外观 → 自定义 → 主题设置**。

### 配色

| 配置项 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- |
| 强调色 | 颜色选择器 | `#2f6feb` | 留空使用主题默认蓝色。影响链接、按钮、目录高亮、进度条。 |
| 页面底色 | 颜色选择器 | `#ffffff` | 覆盖站点底色。 |

### 布局

| 配置项 | 类型 | 默认值 | 范围 | 说明 |
| --- | --- | --- | --- | --- |
| 目录栏宽度 | 数字 | `280` | 200 - 420 | 输出为 `--nb-sidebar-width` |
| 正文宽度 | 数字 | `780` | 560 - 1000 | 输出为 `--nb-content-width` |
| 宽元素宽度 | 数字 | `1120` | 900 - 1800 | 输出为 `--nb-wide-width`，供 `.alignwide` 与代码块使用 |

### 目录

| 配置项 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- |
| 指定目录页面 ID | 文本 | 空 | 英文逗号分隔，如 `12,15,18`。留空自动列出全部已发布页面。 |
| 目录默认折叠 | 复选框 | 不勾选 | 访客的选择会记入 `localStorage`，优先于此默认值。 |

### 阅读增强

| 配置项 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- |
| 代码块显示复制按钮 | 复选框 | 勾选 | |
| 标题显示锚点链接 | 复选框 | 勾选 | |
| 显示文档反馈按钮 | 复选框 | 勾选 | |
| 显示上下节导航 | 复选框 | 勾选 | |
| 显示文档路径面包屑 | 复选框 | 勾选 | 仅层级页面显示 |
| 显示页脚 Widget 区域 | 复选框 | 勾选 | |
| 摘要长度（词） | 数字 | `32` | 8 - 100 |

### 菜单与 Widget

- **菜单位置**：主导航（头部右侧）、页脚导航。
- **Widget 区域**：侧栏 1（目录下方）、页脚 1 / 2。

## 模板层级

```
nimbus-docs/
├── style.css                    # 主题头注释 + 全部样式（含打印样式）
├── functions.php                # 装配入口
├── theme.json                   # 块编辑器配置（含 align-wide 尺寸）
├── index.php                    # 通用回退模板
├── home.php                     # 文章列表（首页显示「文章」时使用）
├── page.php                     # 单页面（文档布局）
├── single.php                   # 单篇文章（文档布局）
├── archive.php                  # 归档：分类 / 标签 / 日期 / 作者
├── archive-page.php             # 页面归档：文档总览
├── search.php                   # 搜索结果（带关键词高亮）
├── 404.php                      # 未找到
├── comments.php                 # 评论
├── sidebar.php                  # 侧栏（目录 + Widget）
├── searchform.php               # 搜索表单
├── header.php                   # <head> + 固定头部 + 文档布局开标签
├── footer.php                   # 页脚 + wp_footer + 布局闭标签
├── inc/
│   ├── template-tags.php        # 模板标签、上下节导航、面包屑、分页
│   ├── toc.php                  # 目录树构建与渲染、代码块包装、标题锚点、SVG 图标表
│   ├── feedback.php             # 反馈渲染、AJAX 处理器、后台列、防刷
│   └── customizer.php           # Customizer 面板与设置项
└── assets/
    ├── css/editor-style.css     # 区块编辑器内样式
    └── js/
        ├── toc.js               # 目录状态、页内锚点、移动侧栏、搜索高亮
        ├── code-copy.js         # 代码复制（含降级）
        ├── feedback.js          # 文档反馈提交
        └── customizer-preview.js# Customizer 实时预览
```

> 翻译文件（`.pot` / `.mo` / `.l10n`）放在 `languages/` 目录，该目录在首次翻译时创建。主题已调用 `load_theme_textdomain()`，放入语言包后即可生效。

### 可覆盖的模板

在子主题中创建同名文件即可覆盖，例如 `page.php`、`sidebar.php`、`header.php`。

> **注意**：`header.php` 打开了文档布局的 `<div class="nb-docs-layout">`，`footer.php` 负责闭合。覆盖这两个文件时必须保持标签配对。

## 子主题制作

1. 在 `wp-content/themes/` 下创建目录，例如 `my-nimbus-child`。
2. 目录内只需两个文件：

```php
<?php
/**
 * My Nimbus 子主题样式。
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'my-nimbus-child',
		get_stylesheet_uri(),
		array( 'nimbus-style' ),
		'1.0.0'
	);
} );
```

```css
/*
Theme Name: My Nimbus
Description: Nimbus Docs 的子主题。
Template: nimbus-docs
Version: 1.0.0
*/
```

3. 启用子主题。

### 扩展点

| 名称 | 类型 | 说明 |
| --- | --- | --- |
| `nimbus_get_option( $key, $default )` | 函数 | 读取主题设置 |
| `nimbus_content_width` | filter | 覆盖 WordPress `$content_width` |
| `nimbus_doc_pages` | filter | 过滤左侧目录的页面列表 |
| `nimbus_feedback_submitted` | action | 反馈提交成功，参数：文章 ID、`up`/`down`、统计数组 |
| `nimbus_toc_storage_key()` | 函数 | localStorage 键名 |
| `nimbus_sidebar_toc()` | 函数 | 渲染整个目录树 |
| `nimbus_render_page_toc( $nodes, $depth )` | 函数 | 渲染目录树节点（递归） |
| `nimbus_pager()` | 函数 | 渲染上下节导航 |
| `nimbus_render_feedback()` | 函数 | 渲染反馈区域 |
| `nimbus_feedback_counts( $post_id )` | 函数 | 读取反馈统计 |
| `nimbus_inline_icon( $name )` | 函数 | 内联 SVG 图标（7 个） |

### 只显示特定页面的目录

```php
add_filter( 'nimbus_doc_pages', function ( $pages ) {
	return array_values( array_filter( $pages, function ( $page ) {
		return 'parent' === $page->post_parent;
	} ) );
} );
```

### 反馈后创建 GitHub Issue

```php
add_action( 'nimbus_feedback_submitted', function ( $post_id, $value, $counts ) {
	if ( 'down' !== $value ) {
		return;
	}
	// 调用你的 Issue API，记录「哪篇文档没人看懂」
}, 10, 3 );
```

## FAQ

**Q：目录里的页面顺序怎么定？**
先按 `menu_order`（后台「页面属性 → 顺序」），相同时按标题。如果在 Customizer 里指定了页面 ID 列表，则严格按你给的顺序排列。

**Q：折叠状态存在哪里？会一直保留吗？**
存在浏览器 `localStorage`，键名是 `nimbus-toc-` + `home_url()` 的 MD5 前 8 位。换浏览器或清缓存后会重置为 Customizer 的默认值。

**Q：为什么反馈按钮在正式环境不生效？**
检查 Customizer 的「显示文档反馈按钮」是否勾选。反馈依赖 AJAX 与 Nonce，如果页面被页面缓存缓存过，Nonce 会过期——缓存插件通常会排除 `admin-ajax.php` 相关请求，但整页缓存需要配置为不缓存含 `nonce` 的页面。

**Q：反馈数据会不会被刷？**
同一 IP + 同一文章 1 小时内只计一次。但这是「防误触」级别的防护，不是「防攻击」级别的——如果需要更强的方式，建议在 Nginx / CDN 层加速率限制。

**Q：上下节导航的顺序和侧栏目录不一致？**
不会。两者都按 `menu_order` + 标题排序，逻辑相同。区别是上下节导航只在同一父页面下取相邻项，跨章节跳转需要走侧栏目录。

**Q：支持 Elementor 之类的页面构建器吗？**
可以安装，主题不会报错。但本主题**没有**为任何构建器做容器级适配，只提供了 WordPress 原生的 `align-wide` / `alignfull`。如果需要完整的构建器支持，请用专门的文档主题（如 WS Document）。

**Q：`[nimbus_notice]` 短代码支持嵌套吗？**
支持。多层 `[nimbus_notice]` 会正常嵌套渲染（外层用 `wpautop` + `do_shortcode` 处理内层）。

**Q：怎么隐藏某个页面的反馈和上下节导航？**
最干净的做法是用子主题覆盖 `page.php`，把这两个调用包在条件里：

```php
// 在子主题的 page.php 中
<?php if ( ! is_page( 'changelog' ) ) : ?>
	<?php nimbus_render_feedback(); ?>
<?php endif; ?>
```

或用过滤器统一关闭：

```php
// 关闭全站上下节导航
add_filter( 'nimbus_option_pager_enabled', '__return_false' );
```

用 CSS 按 `body_class` 隐藏也可行但较脆弱，不推荐。

**Q：screenshot.png 必须吗？**
上架 WordPress.org 目录必须提供，尺寸 1200 × 900。本地自用可以不放。

## License

MIT License

Copyright (c) 2026 MornRain

详细条款见 [LICENSE](LICENSE) 文件。

本主题为 MornRain 独立开发，不捆绑任何第三方库。所有图标为内联 SVG，字体全部使用系统字体栈，无任何外部资源请求。
