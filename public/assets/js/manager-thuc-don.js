function esc(v) {
    return String(v == null ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function money(v) {
    v = Number(v || 0);
    return v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + 'đ';
}

function toast(msg, err) {
    var e = document.getElementById('toast');
    e.textContent = msg;
    e.className = 'toast show' + (err ? ' err' : '');
    clearTimeout(e._t);
    e._t = setTimeout(function () {
        e.className = 'toast';
    }, 2600);
}

function findItem(id) {
    for (var i = 0; i < ITEMS.length; i++) {
        if (String(ITEMS[i].id) === String(id)) {
            return ITEMS[i];
        }
    }
    return null;
}

function toggleMenu(menuId, trigger) {
    var menu = document.getElementById(menuId);
    if (!menu) {
        return;
    }

    var isOpen = menu.className.indexOf('open') !== -1;
    menu.className = isOpen ? 'nav-sub' : 'nav-sub open';
    if (trigger) {
        trigger.className = isOpen
            ? 'nav-parent side-link active'
            : 'nav-parent side-link active open';
    }
}

function showPanel(id) {
    var panels = document.querySelectorAll('.panel');
    for (var i = 0; i < panels.length; i++) {
        panels[i].className = 'panel';
    }
    document.getElementById(id).className = 'panel active';

    var btns = document.querySelectorAll('.nav-action');
    for (var j = 0; j < btns.length; j++) {
        btns[j].className = btns[j].getAttribute('data-panel') === id
            ? 'nav-action active'
            : 'nav-action';
    }

    var menu = document.getElementById('menu-thuc-don');
    var parent = document.querySelector('.nav-parent');
    if (menu) {
        menu.className = 'nav-sub open';
    }
    if (parent) {
        parent.className = 'nav-parent side-link active open';
    }
}

function initCategories() {
    var seen = {};
    var html = '<option value="">Tất cả danh mục</option>';
    var list = '';

    for (var i = 0; i < ITEMS.length; i++) {
        var c = ITEMS[i].danh_muc || '';
        if (c && !seen[c]) {
            seen[c] = 1;
            html += '<option value="' + esc(c) + '">' + esc(c) + '</option>';
            list += '<option value="' + esc(c) + '">';
        }
    }

    document.getElementById('viewCategory').innerHTML = html;
    document.getElementById('editCategory').innerHTML = html;
    document.getElementById('deleteCategory').innerHTML = html;
    document.getElementById('categoryOptions').innerHTML = list;
}

function rowHtml(it, mode) {
    var html = '<tr>';
    html += '<td>' + (it.anh_url ? '<img class="thumb" src="' + esc(it.anh_url) + '" alt="">' : '<div class="thumb"></div>') + '</td>';
    html += '<td><strong>' + esc(it.ten || '-') + '</strong><div class="muted">' + esc(it.mo_ta || '') + '</div><div class="muted">' + money(it.gia || 0) + '</div></td>';
    html += '<td>' + esc(it.danh_muc || '-') + '</td>';
    html += '<td><span class="badge ' + (Number(it.con_mon || 0) ? 'ok' : 'off') + '">' + (Number(it.con_mon || 0) ? 'Đang hiện' : 'Đang ẩn') + '</span> ';
    if (Number(it.noi_bat || 0)) {
        html += '<span class="badge star">Nổi bật</span>';
    }
    html += '</td>';

    if (mode === 'edit') {
        html += '<td><button class="btn secondary" type="button" onclick="chooseEdit(' + it.id + ')">Sửa món</button></td>';
    } else if (mode === 'delete') {
        html += '<td><button class="btn danger" type="button" onclick="deleteItem(' + it.id + ')">Xóa món</button></td>';
    } else {
        html += '<td><button class="btn secondary" type="button" onclick="showPanel(\'panel-edit\');chooseEdit(' + it.id + ')">Sửa</button></td>';
    }

    html += '</tr>';
    return html;
}

function filteredRows(searchId, categoryId, mode) {
    var q = document.getElementById(searchId).value.toLowerCase();
    var cat = document.getElementById(categoryId).value;
    var html = '';
    var count = 0;

    for (var i = 0; i < ITEMS.length; i++) {
        var it = ITEMS[i];
        var haystack = ((it.ten || '') + ' ' + (it.danh_muc || '') + ' ' + (it.mo_ta || '')).toLowerCase();
        if (q && haystack.indexOf(q) === -1) {
            continue;
        }
        if (cat && it.danh_muc !== cat) {
            continue;
        }
        count++;
        html += rowHtml(it, mode);
    }

    return count ? html : '<tr><td colspan="5" class="empty">Không có món phù hợp.</td></tr>';
}

function renderViewList() {
    document.getElementById('viewRows').innerHTML = filteredRows('viewSearch', 'viewCategory', 'view');
}

function renderEditList() {
    document.getElementById('editRows').innerHTML = filteredRows('editSearch', 'editCategory', 'edit');
}

function renderDeleteList() {
    document.getElementById('deleteRows').innerHTML = filteredRows('deleteSearch', 'deleteCategory', 'delete');
}

function chooseEdit(id) {
    var it = findItem(id);
    if (!it) {
        return;
    }

    document.getElementById('editForm').style.display = 'grid';
    document.getElementById('edit_id').value = it.id;
    document.getElementById('edit_ten').value = it.ten || '';
    document.getElementById('edit_danh_muc').value = it.danh_muc || '';
    document.getElementById('edit_mo_ta').value = it.mo_ta || '';
    document.getElementById('edit_anh_url').value = it.anh_url || '';
    document.getElementById('edit_gia').value = Number(it.gia || 0);
    document.getElementById('edit_thu_tu').value = Number(it.thu_tu || 0);
    document.getElementById('edit_con_mon').checked = Number(it.con_mon || 0) === 1;
    document.getElementById('edit_noi_bat').checked = Number(it.noi_bat || 0) === 1;
    document.getElementById('editForm').scrollIntoView({behavior: 'smooth', block: 'start'});
}

function hideEditForm() {
    document.getElementById('editForm').style.display = 'none';
}

function submitForm(form) {
    var fd = new FormData(form);
    if (form.querySelector('[name=con_mon]') && !form.querySelector('[name=con_mon]').checked) {
        fd.set('con_mon', '0');
    }
    if (form.querySelector('[name=noi_bat]') && !form.querySelector('[name=noi_bat]').checked) {
        fd.set('noi_bat', '0');
    }

    fetch(BASE_URL + '/quan-ly/thuc-don/luu', {method: 'POST', body: fd})
        .then(function (r) { return r.json(); })
        .then(function (res) {
            toast(res.thong_bao || 'Đã xử lý', !res.success);
            if (res.success) {
                setTimeout(function () { location.reload(); }, 500);
            }
        })
        .catch(function () {
            toast('Không kết nối được máy chủ', true);
        });
}

function deleteItem(id) {
    var it = findItem(id);
    if (!confirm('Xóa món "' + (it ? it.ten : id) + '"?')) {
        return;
    }

    var fd = new FormData();
    fd.append('id', id);
    fetch(BASE_URL + '/quan-ly/thuc-don/xoa', {method: 'POST', body: fd})
        .then(function (r) { return r.json(); })
        .then(function (res) {
            toast(res.thong_bao || 'Đã xử lý', !res.success);
            if (res.success) {
                setTimeout(function () { location.reload(); }, 500);
            }
        })
        .catch(function () {
            toast('Không kết nối được máy chủ', true);
        });
}

document.getElementById('addForm').onsubmit = function (e) {
    e.preventDefault();
    submitForm(this);
};

document.getElementById('editForm').onsubmit = function (e) {
    e.preventDefault();
    submitForm(this);
};

initCategories();
renderViewList();
renderEditList();
renderDeleteList();
