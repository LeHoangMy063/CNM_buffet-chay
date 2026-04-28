<?php

class BoieuKhienCo
{
    protected $view;
    protected $data = array();
    protected $db; // ✅ THÊM DB

    public function __construct()
    {
        // ✅ KHỞI TẠO DATABASE
        require_once dirname(__FILE__) . '/../core/CosoDuLieu.php';
        $this->db = new CosoDuLieu();
    }

    public function view($view, $data = array())
    {
        $file = dirname(__FILE__) . '/../views/' . $view . '.php';
        if (!file_exists($file)) {
            die('View khong ton tai: ' . $file);
        }
        extract($data);
        require_once $file;
    }

    public function json($data = array())
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    // ===== PHP 5.3 SAFE =====
    public function post($key = '', $default = '')
    {
        if (empty($key)) {
            return isset($_POST) ? $_POST : array();
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    public function get($key = '', $default = '')
    {
        if (empty($key)) {
            return isset($_GET) ? $_GET : array();
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function isGet()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    // ==================== ADMIN AUTH ====================

    protected function isAdminLoggedIn()
    {
        return class_exists('AdminAuthMiddleware')
            ? AdminAuthMiddleware::isAuthenticated()
            : false;
    }

    protected function requireAdmin($redirectAfterLogin = '')
    {
        if (class_exists('AdminAuthMiddleware')) {
            AdminAuthMiddleware::requireLogin($redirectAfterLogin);
        } else {
            // fallback nếu chưa có middleware
            if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                header('Location: ' . BASE_URL . '/admin/login');
                exit;
            }
        }
    }

    protected function getAdminUser()
    {
        if (class_exists('AdminAuthMiddleware')) {
            return AdminAuthMiddleware::getCurrentAdmin();
        }
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    protected function logAdminAction($action, $description = '')
    {
        if (class_exists('AdminAuthMiddleware')) {
            AdminAuthMiddleware::logAction($action, $description);
        }
    }

    // ==================== STAFF AUTH ====================

    protected function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/staff/login');
            exit;
        }
    }

    protected function requireRole($role)
    {
        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== $role) {
            header('Location: ' . BASE_URL . '/staff/login');
            exit;
        }
    }

    // ==================== VALIDATION ====================

    protected function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    protected function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function validateCSRFToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ==================== SESSION NGUOI DUNG (TIENG VIET) ====================

    protected function daDangNhap()
    {
        return isset($_SESSION['nguoi_dung'])
            && !empty($_SESSION['nguoi_dung'])
            && isset($_SESSION['nguoi_dung']['dang_hoat_dong'])
            && $_SESSION['nguoi_dung']['dang_hoat_dong'] == 1;
    }

    protected function layNguoiDung()
    {
        return isset($_SESSION['nguoi_dung']) ? $_SESSION['nguoi_dung'] : null;
    }

    protected function yeuCauDangNhap()
    {
        if (!$this->daDangNhap()) {
            header('Location: ' . BASE_URL . '/dang-nhap');
            exit;
        }
    }

    protected function yeuCauVaiTro($vai_tro)
    {
        if (!$this->daDangNhap()) {
            header('Location: ' . BASE_URL . '/dang-nhap');
            exit;
        }
        $vai_tro_hien_tai = isset($_SESSION['nguoi_dung']['vai_tro'])
            ? $_SESSION['nguoi_dung']['vai_tro']
            : '';
        if ($vai_tro_hien_tai !== $vai_tro) {
            header('Location: ' . BASE_URL . '/dang-nhap');
            exit;
        }
    }

    protected function yeuCauAdminHoacNhanVien()
    {
        if (!$this->daDangNhap()) {
            header('Location: ' . BASE_URL . '/dang-nhap');
            exit;
        }
        $vai_tro = isset($_SESSION['nguoi_dung']['vai_tro'])
            ? $_SESSION['nguoi_dung']['vai_tro']
            : '';
        if ($vai_tro !== 'admin' && $vai_tro !== 'nhan_vien') {
            header('Location: ' . BASE_URL . '/khach/dang-nhap');
            exit;
        }
    }

    public function chuyenHuong($url)
    {
        header('Location: ' . $url);
        exit;
    }
}
