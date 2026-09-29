/**
 * 前台延保查询交互
 */
(function () {
    'use strict';

    var form = document.getElementById('queryForm');
    var btn = document.getElementById('queryBtn');
    var btnText = document.getElementById('btnText');
    var alertBox = document.getElementById('alertBox');
    var resultSection = document.getElementById('resultSection');
    var resultBody = document.getElementById('resultBody');
    var statusBadge = document.getElementById('statusBadge');

    // 显示提示消息
    function showAlert(message, type) {
        type = type || 'error';
        alertBox.innerHTML = '<div class="alert alert-' + type + '">' + escapeHtml(message) + '</div>';
    }

    function clearAlert() {
        alertBox.innerHTML = '';
    }

    // HTML 转义
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // 计算剩余天数文案
    function daysText(days) {
        if (days === null || days === undefined) return '—';
        if (days < 0) return '已过期 ' + Math.abs(days) + ' 天';
        if (days === 0) return '今天到期';
        return '剩余 ' + days + ' 天';
    }

    // 渲染查询结果
    function renderResult(data) {
        var d = data.data;
        statusBadge.className = 'status-badge ' + d.status_class;
        statusBadge.textContent = d.status;

        var html = '';

        // 延保期限高亮卡片
        html += '<div class="warranty-highlight">';
        html += '<div class="highlight-item"><div class="label">延保期限</div><div class="value">' + escapeHtml(d.warranty_start) + ' 至 ' + escapeHtml(d.warranty_end) + '</div></div>';
        html += '<div class="highlight-item"><div class="label">剩余天数</div><div class="value days">' + daysText(d.remaining_days) + '</div></div>';
        html += '</div>';

        // 详细信息网格
        html += '<div class="info-grid">';

        html += '<div class="info-card"><div class="card-label">📱 适用品类</div><div class="card-value">' + escapeHtml(d.product_category) + '</div>';
        if (d.product_model) html += '<div class="card-sub">型号：' + escapeHtml(d.product_model) + '</div>';
        html += '</div>';

        html += '<div class="info-card"><div class="card-label">🛒 购买渠道</div><div class="card-value">' + escapeHtml(d.purchase_channel || '—') + '</div>';
        if (d.purchase_date) html += '<div class="card-sub">购买日期：' + escapeHtml(d.purchase_date) + '</div>';
        html += '</div>';

        html += '<div class="info-card"><div class="card-label">🔧 服务网点</div><div class="card-value">' + escapeHtml(d.service_outlet || '—') + '</div>';
        if (d.outlet_phone) html += '<div class="card-sub">联系电话：' + escapeHtml(d.outlet_phone) + '</div>';
        html += '</div>';

        if (d.notes) {
            html += '<div class="info-card"><div class="card-label">📝 备注说明</div><div class="card-value">' + escapeHtml(d.notes) + '</div></div>';
        }

        html += '</div>';

        resultBody.innerHTML = html;
        resultSection.style.display = 'block';
    }

    // 渲染未找到状态
    function renderNotFound(service) {
        statusBadge.className = 'status-badge status-unknown';
        statusBadge.textContent = '未找到';

        var html = '<div class="empty-state">';
        html += '<div class="empty-icon">🔍</div>';
        html += '<h3>未查询到相关延保信息</h3>';
        html += '<p>请核对您输入的购买手机号和设备序列号是否正确</p>';
        html += '<div class="service-card">';
        html += '<h4>客服核对建议</h4>';
        html += '<div class="service-item"><span class="icon">📞</span><span>客服热线：' + escapeHtml(service.hotline) + '</span></div>';
        html += '<div class="service-item"><span class="icon">✉️</span><span>客服邮箱：' + escapeHtml(service.email) + '</span></div>';
        html += '<div class="service-item"><span class="icon">🕐</span><span>服务时间：周一至周日 9:00-20:00</span></div>';
        html += '<div class="service-item"><span class="icon">💡</span><span>请准备好购买凭证和设备序列号，以便客服快速核对</span></div>';
        html += '</div></div>';

        resultBody.innerHTML = html;
        resultSection.style.display = 'block';
    }

    // 表单提交
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearAlert();
        resultSection.style.display = 'none';

        var phone = document.getElementById('phone').value.trim();
        var serial = document.getElementById('serial').value.trim();

        // 前端验证
        if (!phone) { showAlert('请输入购买手机号'); return; }
        if (!/^1[3-9]\d{9}$/.test(phone)) { showAlert('手机号格式不正确，请输入11位有效手机号'); return; }
        if (!serial) { showAlert('请输入设备序列号'); return; }
        if (!/^[A-Za-z0-9\-]{4,30}$/.test(serial)) { showAlert('序列号格式不正确（4-30位字母数字或连字符）'); return; }

        // 显示加载状态
        btn.disabled = true;
        btnText.innerHTML = '<span class="loading"></span> 查询中...';

        fetch('api/query.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone: phone, serial_number: serial })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                renderResult(data);
            } else if (data.not_found) {
                renderNotFound(data.service);
            } else {
                showAlert(data.message || '查询失败，请稍后重试');
            }
        })
        .catch(function () {
            showAlert('网络异常，请检查网络连接后重试');
        })
        .finally(function () {
            btn.disabled = false;
            btnText.textContent = '立即查询';
        });
    });
})();
