# 家电延保查询系统

基于 PHP + SQLite 的家电延保查询管理系统。用户输入购买手机号和设备序列号即可查询延保期限、服务网点、购买渠道和适用品类；后台管理员可录入和修改延保信息。

## 功能特性

### 前台查询
- 企业蓝白风格界面
- 输入购买手机号 + 设备序列号查询
- 展示延保期限、剩余天数、适用品类、购买渠道、服务网点
- 查不到时给出客服核对建议（客服热线、邮箱、服务时间）
- 响应式设计，支持移动端

### 后台管理
- 管理员登录/登出
- 延保记录列表（支持搜索、状态筛选）
- 新增/编辑/删除延保记录
- 统计卡片：总数、在保中、即将到期、已过期
- 延保状态自动计算（在保中/即将到期/已过期）

## 环境要求

- PHP 8.0+
- PDO SQLite 扩展
- mbstring 扩展

## 安装部署

1. 将项目放到 Web 服务器目录（如 Apache/Nginx 站点目录）

2. 访问安装脚本初始化数据库：
   ```
   http://your-domain/install.php
   ```
   安装脚本会自动创建数据库表、默认管理员账号和示例数据。

3. 删除 `install.php`（安全建议）

## 默认账号

- 后台地址：`/admin/login.php`
- 用户名：`admin`
- 密码：`admin123`

## 目录结构

```
/workspace/
├── index.php              # 前台查询首页
├── install.php            # 系统安装脚本
├── api/
│   └── query.php          # 延保查询 API
├── admin/
│   ├── index.php          # 后台管理首页（记录列表）
│   ├── login.php          # 管理员登录
│   ├── logout.php         # 管理员登出
│   ├── edit.php           # 新增/编辑延保记录
│   └── delete.php         # 删除延保记录
├── includes/
│   ├── config.php         # 配置文件
│   ├── db.php             # 数据库连接
│   ├── functions.php      # 辅助函数
│   └── auth.php           # 管理员认证
├── assets/
│   ├── style.css          # 前台样式
│   ├── admin.css          # 后台样式
│   └── app.js             # 前台交互脚本
└── data/
    └── warranty.db        # SQLite 数据库（自动生成）
```

## 技术说明

- 数据库使用 SQLite，无需额外配置数据库服务
- 前端使用原生 JavaScript + Fetch API，无框架依赖
- 后台使用 Session 认证，密码使用 password_hash 加密
- 表单包含 CSRF 防护
- 输入验证：手机号格式、序列号格式、日期逻辑校验
