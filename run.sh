#!/bin/bash
# 启动 PHP 内置开发服务器
export LD_LIBRARY_PATH=/tmp/phproot/usr/lib/aarch64-linux-gnu:$LD_LIBRARY_PATH
PHP=/tmp/phproot/usr/bin/php8.2
INI=/tmp/phproot/etc/php/8.2/cli/php.ini
cd /workspace
$PHP -c $INI -S 0.0.0.0:8080
