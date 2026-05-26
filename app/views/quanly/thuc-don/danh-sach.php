<section class="panel active" id="panel-list">
    <div class="panel-head">
        <div>
            <h3>Danh sách món</h3>
            <p>Tìm kiếm, sửa hoặc xóa món trực tiếp tại đây.</p>
        </div>
        <button class="btn" type="button" onclick="showAddForm()">Thêm món</button>
    </div>
    <div class="panel-body">
        <div class="toolbar">
            <input class="input" id="listSearch" type="search" placeholder="Tìm tên món, danh mục, mô tả..." oninput="renderList()">
            <select class="select" id="listCategory" onchange="renderList()">
                <option value="">Tất cả danh mục</option>
                <?php echo managerCategoryOptions(); ?>
            </select>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Món ăn</th>
                        <th>Danh mục</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="listRows">
                    <?php if (empty($danhSachMon)) : ?>
                        <tr>
                            <td colspan="5" class="empty">Không có món phù hợp.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($danhSachMon as $mon) : ?>
                            <tr>
                                <td>
                                    <?php if (!empty($mon['anh_url'])) : ?>
                                        <img class="thumb" src="<?php echo htmlspecialchars($mon['anh_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                    <?php else : ?>
                                        <div class="thumb"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars(managerText(isset($mon['ten']) ? $mon['ten'] : '-'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div class="muted"><?php echo htmlspecialchars(managerText(isset($mon['mo_ta']) ? $mon['mo_ta'] : ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars(managerCategoryText(isset($mon['danh_muc']) ? $mon['danh_muc'] : '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if (!empty($mon['con_mon'])) : ?>
                                        <span class="badge ok">Đang hiện</span>
                                    <?php else : ?>
                                        <span class="badge off">Đang ẩn</span>
                                    <?php endif; ?>
                                    <?php if (!empty($mon['noi_bat'])) : ?>
                                        <span class="badge star">Nổi bật</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn secondary" type="button" onclick="chooseEdit(<?php echo (int)$mon['id']; ?>)">Sửa</button>
                                    <button class="btn danger" type="button" onclick="deleteItem(<?php echo (int)$mon['id']; ?>)">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal" id="editModal" aria-hidden="true">
    <div class="modal-backdrop" onclick="hideEditForm()"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-head">
            <h3 id="editModalTitle">Sửa món</h3>
            <button class="modal-close" type="button" onclick="hideEditForm()" aria-label="Đóng">&times;</button>
        </div>
        <form class="form-grid edit-form" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <label>Tên món
                <input class="input" type="text" name="ten" id="edit_ten" required>
            </label>
            <label>Danh mục
                <select class="select" name="danh_muc" id="edit_danh_muc" required>
                    <?php echo managerCategoryOptions(); ?>
                </select>
            </label>
            <label>Mô tả
                <textarea name="mo_ta" id="edit_mo_ta" rows="3"></textarea>
            </label>
            <label>URL ảnh
                <input class="input" type="url" name="anh_url" id="edit_anh_url">
            </label>
            <div class="checks">
                <label><input type="checkbox" name="con_mon" id="edit_con_mon" value="1"> Hiển thị/còn món</label>
                <label><input type="checkbox" name="noi_bat" id="edit_noi_bat" value="1"> Nổi bật</label>
            </div>
            <div class="form-actions">
                <button class="btn secondary" type="button" onclick="hideEditForm()">Hủy</button>
                <button class="btn" type="submit">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
