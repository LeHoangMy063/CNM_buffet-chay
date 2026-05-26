<?php $pageTitle = 'Thuc Don';
require dirname(__FILE__) . '/_dau-trang.php'; ?>
<div style="margin-bottom:1rem;text-align:right">
    <button class="btn-admin btn-green" onclick="openForm()">+ Them Mon Moi</button>
</div>

<div class="card">
    <div class="card-header">
        <h3>Thuc don (<?php echo count($items) ?> mon)</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Ten mon</th>
                <th>Danh muc</th>
                <th>Mo ta</th>
                <th>Noi bat</th>
                <th>Hien thi</th>
                <th>Thu tu</th>
                <th>Thao tac</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)) { ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:#6b7280;padding:2rem">Chua co mon an</td>
                </tr>
                <?php } else {
                foreach ($items as $item) { ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['name']) ?></strong></td>
                        <td><?php echo htmlspecialchars($item['category']) ?></td>
                        <td style="color:#6b7280;font-size:0.8rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($item['description'] ? $item['description'] : '-') ?></td>
                        <td><?php echo $item['is_featured'] ? '&#11088;' : '-' ?></td>
                        <td>
                            <select class="inline" onchange="toggleAvailable(<?php echo $item['id'] ?>, this.value)">
                                <option value="1" <?php echo $item['is_available'] ? 'selected' : '' ?>>Co</option>
                                <option value="0" <?php echo !$item['is_available'] ? 'selected' : '' ?>>An</option>
                            </select>
                        </td>
                        <td><?php echo $item['sort_order'] ?></td>
                        <td>
                            <button class="btn-admin btn-sm btn-gray" onclick='openForm(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)'>Sua</button>
                            <button class="btn-admin btn-sm btn-red" onclick="deleteItem(<?php echo $item['id'] ?>)">Xoa</button>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="menuModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.4);z-index:200;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:2rem;max-width:480px;width:90%;max-height:90vh;overflow-y:auto">
        <h3 style="font-family:'Playfair Display',serif;color:#2d6a4f;margin-bottom:1.5rem" id="formTitle">Them Mon Moi</h3>
        <form id="menuForm">
            <input type="hidden" name="id" id="fi_id">
            <div style="margin-bottom:1rem">
                <label style="font-size:0.85rem;color:#6b7280;display:block;margin-bottom:0.3rem">Ten mon *</label>
                <input type="text" name="name" id="fi_name" required style="width:100%;padding:0.75rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:0.95rem;font-family:'Be Vietnam Pro',sans-serif">
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-size:0.85rem;color:#6b7280;display:block;margin-bottom:0.3rem">Danh muc *</label>
                <select name="category" id="fi_cat" required style="width:100%;padding:0.75rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:0.95rem;font-family:'Be Vietnam Pro',sans-serif">
                    <option>Khai vi</option>
                    <option>Mon chinh</option>
                    <option>Trang mieng</option>
                    <option>Do uong</option>
                </select>
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-size:0.85rem;color:#6b7280;display:block;margin-bottom:0.3rem">Mo ta</label>
                <textarea name="description" id="fi_desc" rows="2" style="width:100%;padding:0.75rem;border:1.5px solid #e5e7eb;border-radius:8px;font-size:0.9rem;font-family:'Be Vietnam Pro',sans-serif;resize:none"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                <div>
                    <label style="font-size:0.85rem;color:#6b7280;display:block;margin-bottom:0.3rem">Thu tu</label>
                    <input type="number" name="sort_order" id="fi_sort" value="0" style="width:100%;padding:0.75rem;border:1.5px solid #e5e7eb;border-radius:8px">
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;padding-top:1.5rem">
                    <input type="checkbox" name="is_available" id="fi_avail" value="1" checked style="width:16px;height:16px">
                    <label for="fi_avail" style="font-size:0.85rem">Hien thi</label>
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;padding-top:1.5rem">
                    <input type="checkbox" name="is_featured" id="fi_feat" value="1" style="width:16px;height:16px">
                    <label for="fi_feat" style="font-size:0.85rem">Noi bat</label>
                </div>
            </div>
            <div style="display:flex;gap:0.8rem">
                <button type="submit" class="btn-admin btn-green" style="flex:1">Luu</button>
                <button type="button" class="btn-admin btn-gray" onclick="closeForm()">Huy</button>
            </div>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById('menuModal');

    function openForm(item) {
        item = item || null;
        modal.style.display = 'flex';
        document.getElementById('formTitle').textContent = item ? 'Chinh Sua Mon' : 'Them Mon Moi';
        document.getElementById('fi_id').value = item ? item.id : '';
        document.getElementById('fi_name').value = item ? item.name : '';
        document.getElementById('fi_cat').value = item ? item.category : 'Khai vi';
        document.getElementById('fi_desc').value = item ? item.description : '';
        document.getElementById('fi_sort').value = item ? item.sort_order : 0;
        document.getElementById('fi_avail').checked = !item || item.is_available == 1;
        document.getElementById('fi_feat').checked = item && item.is_featured == 1;
    }

    function closeForm() {
        modal.style.display = 'none';
    }
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeForm();
    });

    document.getElementById('menuForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var fd = new FormData(e.target);
        if (!document.getElementById('fi_avail').checked) fd.delete('is_available');
        if (!document.getElementById('fi_feat').checked) fd.delete('is_featured');
        fetch('<?php echo BASE_URL ?>/admin/menu/save', {
                method: 'POST',
                body: fd
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message);
                    closeForm();
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                } else showToast(data.message, true);
            });
    });

    function toggleAvailable(id, val) {
        apiPost('<?php echo BASE_URL ?>/admin/menu/save', {
                id: id,
                is_available: val,
                name: 'x',
                category: 'Khai vi'
            })
            .then(function() {
                showToast('Da cap nhat');
            });
    }

    function deleteItem(id) {
        if (!confirm('An mon nay?')) return;
        apiPost('<?php echo BASE_URL ?>/admin/menu/delete', {
                id: id
            })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message);
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                }
            });
    }
</script>

<?php require dirname(__FILE__) . '/_cuoi-trang.php'; ?>