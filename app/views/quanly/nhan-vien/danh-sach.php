<section class="panel active" id="panel-staff">
    <div class="panel-head">
        <div>
            <h3>Danh sách nhân viên</h3>
            <p>Xem, sửa hoặc xóa tài khoản nhân viên trong hệ thống.</p>
        </div>
        <button class="btn" type="button" onclick="showStaffForm()">Thêm nhân viên</button>
    </div>
    <div class="panel-body">
        <div class="toolbar staff-toolbar">
            <input class="input" id="staffSearch" type="search" placeholder="Tìm họ tên, tên đăng nhập, email, số điện thoại..." oninput="renderStaffList()">
            <select class="select" id="staffRoleFilter" onchange="renderStaffList()">
                <option value="">Tất cả vai trò</option>
                <option value="quanly">Quản lý</option>
                <option value="nhanvien">Nhân viên</option>
                <option value="bep">Bếp</option>
            </select>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Liên hệ</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="staffRows">
                    <?php if (empty($danhSachNhanVien)) : ?>
                        <tr>
                            <td colspan="6" class="empty">Chưa có nhân viên phù hợp.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($danhSachNhanVien as $nhanVien) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(managerText(isset($nhanVien['ho_ten']) ? $nhanVien['ho_ten'] : '-'), ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div class="muted"><?php echo htmlspecialchars(isset($nhanVien['ten_dang_nhap']) ? $nhanVien['ten_dang_nhap'] : '', ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars(isset($nhanVien['email']) ? $nhanVien['email'] : '', ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="muted"><?php echo htmlspecialchars(isset($nhanVien['so_dien_thoai']) ? $nhanVien['so_dien_thoai'] : '', ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars(managerRoleText(isset($nhanVien['vai_tro']) ? $nhanVien['vai_tro'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if (!empty($nhanVien['dang_hoat_dong'])) : ?>
                                        <span class="badge ok">Đang hoạt động</span>
                                    <?php else : ?>
                                        <span class="badge off">Đã khóa</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(isset($nhanVien['ngay_tao']) ? managerFormatDateShort($nhanVien['ngay_tao']) : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <button class="btn secondary" type="button" onclick="editStaff(<?php echo (int)$nhanVien['id']; ?>)">Sửa</button>
                                    <button class="btn danger" type="button" onclick="deleteStaff(<?php echo (int)$nhanVien['id']; ?>)">Xóa</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal" id="staffDetailModal" aria-hidden="true">
    <div class="modal-backdrop" onclick="hideStaffDetail()"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="staffDetailTitle">
        <div class="modal-head">
            <h3 id="staffDetailTitle">Thông tin nhân viên</h3>
            <button class="modal-close" type="button" onclick="hideStaffDetail()" aria-label="Đóng">&times;</button>
        </div>
        <div class="staff-detail" id="staffDetailBody"></div>
    </div>
</div>

<div class="modal" id="staffModal" aria-hidden="true">
    <div class="modal-backdrop" onclick="hideStaffForm()"></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="staffModalTitle">
        <div class="modal-head">
            <h3 id="staffModalTitle">Nhân viên</h3>
            <button class="modal-close" type="button" onclick="hideStaffForm()" aria-label="Đóng">&times;</button>
        </div>
        <form class="form-grid" id="staffForm">
            <input type="hidden" name="id" id="staff_id">
            <div class="grid2">
                <label>Họ tên
                    <input class="input" type="text" name="ho_ten" id="staff_ho_ten" required>
                </label>
                <label>Tên đăng nhập
                    <input class="input" type="text" name="ten_dang_nhap" id="staff_ten_dang_nhap" required>
                </label>
            </div>
            <div class="grid2">
                <label>Email
                    <input class="input" type="email" name="email" id="staff_email">
                </label>
                <label>Số điện thoại
                    <input class="input" type="text" name="so_dien_thoai" id="staff_so_dien_thoai">
                </label>
            </div>
            <div class="grid2">
                <label>Vai trò
                    <select class="select" name="vai_tro" id="staff_vai_tro" required>
                        <option value="nhanvien">Nhân viên</option>
                        <option value="quanly">Quản lý</option>
                        <option value="bep">Bếp</option>
                    </select>
                </label>
                <label>Trạng thái
                    <select class="select" name="dang_hoat_dong" id="staff_dang_hoat_dong">
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Đã khóa</option>
                    </select>
                </label>
            </div>
            <label>Mật khẩu
                <input class="input" type="password" name="mat_khau" id="staff_mat_khau" autocomplete="new-password">
                <span class="field-hint">Để trống nếu không đổi mật khẩu khi sửa.</span>
            </label>
            <div class="form-actions">
                <button class="btn secondary" type="button" onclick="hideStaffForm()">Hủy</button>
                <button class="btn" type="submit">Lưu nhân viên</button>
            </div>
        </form>
    </div>
</div>
