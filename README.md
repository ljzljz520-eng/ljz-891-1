# 家电延保查询系统（PHP + SQLite）

企业蓝白风格的家电延保信息查询与后台管理系统，开箱即用（SQLite 免安装数据库）。

## 功能

### 前台（index.php）
- 输入 **购买手机号 + 设备序列号** 进行查询
- 查询结果展示：延保期限（含有效期状态 / 剩余天数）、服务网点、购买渠道、适用品类、产品型号、服务说明
- 查无结果时展示 **客服核对建议**（信息核对指引、400 客服热线）
- 手机 / 序列号格式校验，输出全部经 htmlspecialchars 转义防 XSS

### 后台（/admin/）
- 管理员登录（Session + 密码哈希 password_hash 校验）
- 延保信息 **录入 / 修改 / 删除**，表单完整校验（手机号格式、日期、手机号+序列号唯一）
- 列表搜索（手机号 / 序列号 / 产品 / 品类）、分页、有效 / 到期状态标签
- 全部写操作带 CSRF Token 防护

## 默认账号
- 用户名：`admin`
- 密码：`admin123`
> 生产环境请修改：登录后可在 SQLite 中更新 admins 表，或重新初始化 data/warranty.db

## 运行

使用内置 PHP 服务器即可运行（PHP 8.x，需 pdo_sqlite 扩展）：

```bash
php -S 0.0.0.0:8080
```

- 前台查询：http://localhost:8080/index.php
- 后台管理：http://localhost:8080/admin/login.php

生产环境可部署到 Nginx + PHP-FPM，将站点根目录指向本目录。

## 体验数据（首次访问自动生成）
| 手机号 | 序列号 | 状态 |
|---|---|---|
| 13800138000 | SN20240315001 | 有效（冰箱） |
| 13912345678 | AC2024-KFR35-8866 | 有效（空调） |
| 13711112222 | WM2022-88001234 | 已到期（洗衣机） |

删除 `data/warranty.db` 后刷新页面可重新初始化。

## 目录结构
```
├── index.php          前台查询页
├── db.php             SQLite 连接、自动建表、示例数据
├── assets/style.css   企业蓝白风格样式
└── admin/
    ├── auth.php       登录校验 / CSRF / Flash
    ├── login.php      管理员登录
    ├── logout.php     退出
    ├── index.php      延保列表（搜索 / 分页 / 删除）
    ├── edit.php       录入 / 修改
    └── delete.php     删除处理
```
