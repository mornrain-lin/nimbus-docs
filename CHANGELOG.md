# Changelog

本项目遵循 [Semantic Versioning](https://semver.org/lang/zh-CN/) 规范。

## [Unreleased]

### 新增

- 补齐模板层级：新增 `front-page.php`（静态首页输出页面内容 + 文档总览；首页显示「文章」时输出文章流）与 `home.php`（文章列表）。

### 修复

- `archive-page.php` 修复双层循环：原代码先`while ( have_posts() ) { the_post(); }` 消耗掉主查询，紧接着调用的 `nimbus_page_list()` 再进循环，导致页面列表永远为空。
- `archive-page.php` 的 `<h1>` 改用 `get_the_archive_title()`：原 `the_title()` 在循环外调用，取不到归档标题。
- `nimbus_page_list()` 新增可选 `$posts` 参数，供首页这类主查询已被消费的场景直接渲染指定文章数组，规避同类双循环问题。
- `nimbus_page_list()` 中的 `the_title()` / `the_permalink()` 改为 `esc_html( get_the_title() )` / `esc_url( get_permalink() )`；`get_post_type_object()` 结果增加 `instanceof` 判断，避免对象为 null 时致命错误。
- `nimbus_pager()` 的 `get_previous_post( true, ... )` 改为 `get_previous_post()`：原写法会触发 `_deprecated_argument()`，且第三个参数并不是文章类型。
- `--nb-muted` 由 `#8b949e` 调整为 `#6f767e`，正文对比度从 3.08:1 提升到 4.60:1（WCAG AA）。
- 资源版本号改用 `filemtime()`。

## [1.0.0] - 2026-10-02

### 新增

- 完整模板层级：`index.php` / `page.php` / `single.php` / `archive.php` / `archive-page.php` / `search.php` / `404.php` / `comments.php` / `sidebar.php` / `searchform.php` / `header.php` / `footer.php`。
- `theme.json`：2 个字体栈、4 档字号、8 组配色，`align-wide` 指定 `contentSize: 780` / `wideSize: 1120`。
- 文档布局：左侧固定目录栏（桌面端 sticky，移动端抽屉式带遮罩）+ 右侧正文栏。
- 两级目录树：
  - 页面树：服务端按 `post_parent` 建树、`menu_order` + 标题排序渲染，支持指定页面 ID 筛选。
  - 页内锚点：客户端扫描当前页 h2 - h4 生成嵌套列表，自动补齐缺失的标题 id。
  - 当前页自动高亮并展开所有祖先层级，标记 `aria-current="page"`。
- 目录折叠状态用 `localStorage` 记忆（键名按 `home_url()` 哈希区分）：单项展开状态 + 整树收起状态，刷新与跨页面均保留。
- 站内搜索增强：头部搜索框常驻、`/` 快捷键聚焦并全选、结果页显示相对路径、摘要关键词 `<mark>` 高亮（多关键词 + 区间合并算法）。
- 文档反馈：每篇文档底部「有帮助 / 没帮助」按钮，AJAX 提交，Nonce 校验 + 值白名单 + 文章状态校验，同一 IP 与文章 1 小时内只计一次（IP 存哈希）。数据写入 `_nimbus_feedback_up` / `_nimbus_feedback_down` / `_nimbus_feedback_log`，后台文章与页面列表新增「文档反馈」列显示👍 / 👎 与好评率。触发 `nimbus_feedback_submitted` 动作。
- 上下节导航：优先在同父页面的兄弟页面中按顺序取相邻项，找不到时才回落到全站上一篇 / 下一篇（且要求父页面相同）。
- 代码块增强：每个 `<pre>` 自动包裹复制按钮容器，Clipboard API 优先、非 HTTPS 降级到 `execCommand`，按钮有「复制 / 已复制 / 复制失败」三态。`no-copy` 或 `language-none` class 可关闭。
- 标题锚点：服务端为已有 id 的 h2 / h3 注入锚点链接，客户端为缺失 id 的补齐。
- 提示框短代码 `[nimbus_notice]`：`type` 支持 `tip` / `warning` / `danger` / `success`，支持嵌套。
- 宽对齐支持：`.alignwide` 突破正文栏宽，`.alignfull` 铺满视口，不依赖任何页面构建器。
- 打印友好样式：`@page` 页边距、隐藏交互元素、展开折叠内容、标题避免跨页断裂、代码块 / 引用 / 图表 / 表格避免页内断裂、外部链接自动补 URL。
- 面包屑：层级页面显示完整路径。
- Widget 区域：侧栏 1 个（目录下方）+ 页脚 2 个。
- 所有设置项支持 `nimbus_option_{key}` 过滤器覆盖。
- 全站转义输出，PHP 7.4 兼容，零外部资源依赖，无第三方库。
