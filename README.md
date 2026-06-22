# World Cup H5

A mobile-first bilingual web app for browsing World Cup teams, players, clubs, fixtures, score predictions, and prediction accuracy.

一款面向手机浏览器的中英文世界杯 Web 应用，可查询球队、球员及效力俱乐部，浏览赛程和赛果，提交个人比分预测并统计预测准确率。

## English

### Highlights

- Browse 40 highly ranked national teams with flag logos.
- View up to 16 core players per team, including shirt number, English and Chinese names, position, and club.
- Switch between English and Chinese; English is the default language.
- Display the complete 104-match tournament schedule in Singapore Time (UTC+8).
- Synchronize fixtures and completed scores from ESPN's structured scoreboard data.
- Log in with a display name only, without a password.
- Save a score prediction for every upcoming match and view predictions from other users.
- Track exact-score accuracy and correct-result predictions.
- Run on IIS and PHP on Windows Server, with optional scheduled score updates through Node.js.

### Technology

- Frontend: HTML, CSS, and vanilla JavaScript
- Shared predictions: PHP and JSON file storage
- Score synchronization: Node.js and ESPN scoreboard data
- Deployment: Microsoft IIS on Windows Server

### Deployment

1. Copy the repository files to your IIS website directory, for example `C:\PHPWeb\worldcup`.
2. Give the IIS application-pool identity modify permission on the `data` directory. The API creates it automatically when needed.
3. Make sure PHP is enabled for the website.
4. Open the site and verify that `index.html`, `matches.json`, and `api.php` are accessible.

Do not publish `config.php`, the `data` directory, logs, or backups.

### Updating Scores

Run manually:

```powershell
.\update-worldcup-now.ps1
```

Or double-click `update-worldcup-now.cmd`.

Install the scheduled updates from an elevated PowerShell window:

```powershell
.\install-espn-score-tasks.ps1 -SiteDir "C:\PHPWeb\worldcup"
```

This creates daily updates at 08:05 and 12:05 server time. Set the Windows Server time zone to Singapore Standard Time.

## 中文

### 主要功能

- 展示世界排名靠前的 40 支国家队及国旗标志。
- 每队最多展示 16 名核心球员，包括球衣号码、中英文姓名、位置及效力俱乐部。
- 支持右上角中英文切换，默认语言为英文。
- 展示完整 104 场世界杯赛程，时间统一转换为新加坡时间（UTC+8）。
- 通过 ESPN 结构化记分牌数据同步赛程和已完成比赛比分。
- 用户只需输入显示名称即可登录，无需密码。
- 可为每场未开始比赛填写预测比分，并查看其他用户的预测。
- 自动统计准确比分命中率及比赛结果命中次数。
- 支持部署在 Windows Server、IIS 和 PHP 环境，并可通过 Node.js 定时更新比分。

### 技术架构

- 前端：HTML、CSS、原生 JavaScript
- 共享预测：PHP 与 JSON 文件存储
- 比分同步：Node.js 与 ESPN 记分牌数据
- 部署环境：Windows Server 与 Microsoft IIS

### 服务器部署

1. 将仓库文件复制到 IIS 网站目录，例如 `C:\PHPWeb\worldcup`。
2. 给 IIS 应用程序池账户授予 `data` 目录修改权限；首次使用时 API 也可以自动创建该目录。
3. 确认网站已启用 PHP。
4. 打开网站并检查 `index.html`、`matches.json` 和 `api.php` 是否正常访问。

请勿公开上传 `config.php`、`data` 用户数据目录、日志和备份文件。

### 更新比分

手动运行：

```powershell
.\update-worldcup-now.ps1
```

也可以双击 `update-worldcup-now.cmd`。

使用管理员 PowerShell 安装定时任务：

```powershell
.\install-espn-score-tasks.ps1 -SiteDir "C:\PHPWeb\worldcup"
```

定时任务会在服务器本地时间每天 08:05 和 12:05 更新。服务器时区应设为新加坡标准时间。

## Data Notice / 数据说明

This is an independent fan project and is not affiliated with or endorsed by FIFA or ESPN. Team and player information is maintained for this project and may require corrections after official squad announcements. Match data remains subject to the upstream provider's availability and terms.

本项目为独立球迷项目，与 FIFA 或 ESPN 不存在隶属或官方授权关系。球队和球员资料由项目维护，官方名单公布后可能仍需修正；比赛数据的可用性取决于上游数据来源及其使用条款。

