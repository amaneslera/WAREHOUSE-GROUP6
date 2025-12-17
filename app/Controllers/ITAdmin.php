<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;

class ITAdmin extends BaseController
{
    protected $db;
    protected $backupDir;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->backupDir = WRITEPATH . 'backups';
    }

    private function listBackupFiles(): array
    {
        $dir = $this->backupDir;
        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql');
        if (! is_array($files)) {
            return [];
        }

        $items = [];
        foreach ($files as $path) {
            if (! is_file($path)) {
                continue;
            }

            $name = basename($path);
            $mtime = @filemtime($path);
            $size = @filesize($path);

            $items[] = [
                'name' => $name,
                'path' => $path,
                'created_at' => $mtime ? date('Y-m-d H:i:s', $mtime) : '',
                'mtime' => $mtime ?: 0,
                'size' => $size ?: 0,
                'size_human' => $this->formatBytes($size ?: 0),
            ];
        }

        usort($items, function ($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });

        return $items;
    }

    private function getLatestBackupTimestamp(): ?string
    {
        $files = $this->listBackupFiles();
        if ($files === []) {
            return null;
        }

        return $files[0]['created_at'] ?? null;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $bytes;
        $i = 0;
        while ($size >= 1024 && $i < (count($units) - 1)) {
            $size /= 1024;
            $i++;
        }

        return number_format($size, 2) . ' ' . $units[$i];
    }

    private function runBackupInternal(): array
    {
        $dbConfig = config('Database')->default;
        $hostname = preg_replace('/[^A-Za-z0-9\.\-_]/', '', (string) ($dbConfig['hostname'] ?? 'localhost'));
        $username = preg_replace('/[^A-Za-z0-9\.\-_]/', '', (string) ($dbConfig['username'] ?? 'root'));
        $database = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) ($dbConfig['database'] ?? 'warehouse_db'));
        $port = (int) ($dbConfig['port'] ?? 3306);
        $password = (string) ($dbConfig['password'] ?? '');

        if (! function_exists('exec')) {
            return ['ok' => false, 'message' => 'Server does not allow running backup command (exec disabled).'];
        }

        if (! is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0775, true);
        }

        if (! is_dir($this->backupDir)) {
            return ['ok' => false, 'message' => 'Backup folder is not writable: ' . $this->backupDir];
        }

        $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (! is_file($mysqldump)) {
            return ['ok' => false, 'message' => 'mysqldump.exe not found at ' . $mysqldump . '. Please ensure XAMPP MySQL is installed.'];
        }

        $timestamp = date('Y-m-d_H-i-s');
        $fileName = $database . '_' . $timestamp . '.sql';
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $fileName;

        $pwdEsc = str_replace('"', '\\"', $password);
        $filePathEsc = str_replace('"', '\\"', $filePath);

        $cmd = '"' . $mysqldump . '"';
        $cmd .= ' --host=' . $hostname;
        $cmd .= ' --port=' . $port;
        $cmd .= ' --user=' . $username;
        if ($password !== '') {
            $cmd .= ' --password="' . $pwdEsc . '"';
        }
        $cmd .= ' --single-transaction --routines --triggers';
        $cmd .= ' --databases ' . $database;
        $cmd .= ' --result-file="' . $filePathEsc . '"';

        $output = [];
        $exitCode = 1;
        @exec($cmd . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0 || ! is_file($filePath) || filesize($filePath) === 0) {
            $msg = 'Backup failed.';
            if (is_array($output) && $output !== []) {
                $msg .= ' ' . implode(' ', $output);
            }
            return ['ok' => false, 'message' => $msg];
        }

        return ['ok' => true, 'message' => 'Backup created: ' . $fileName, 'file' => $fileName];
    }

    private function ensureDailyBackupIfDue(): void
    {
        $latest = $this->listBackupFiles();
        $today = date('Y-m-d');

        if ($latest !== [] && isset($latest[0]['mtime']) && date('Y-m-d', (int) $latest[0]['mtime']) === $today) {
            return;
        }

        $result = $this->runBackupInternal();
        if (! ($result['ok'] ?? false)) {
            return;
        }

        $auditModel = new AuditLogModel();
        $auditModel->logAction(
            'backup_auto',
            'backups',
            null,
            null,
            ['file' => $result['file'] ?? null],
            'Automatic daily backup created'
        );
    }

    private function requireItAdmin()
    {
        if (! session('logged_in')) {
            return redirect()->to('/login');
        }

        $role = session('user_role');
        if ($role !== 'it_administrator' && $role !== 'IT_ADMIN') {
            return redirect()->to('/login');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $this->ensureDailyBackupIfDue();

        $userModel = new UserModel();
        $auditModel = new AuditLogModel();

        $totalUsers = $userModel->countAll();

        $activeUsers = null;
        $inactiveUsers = null;
        if ($this->db->fieldExists('is_active', 'users')) {
            $activeUsers = $this->db->table('users')->where('is_active', 1)->countAllResults();
            $inactiveUsers = $this->db->table('users')->where('is_active', 0)->countAllResults();
        }

        $lastLoginAt = null;
        if ($this->db->fieldExists('last_login_at', 'users')) {
            $row = $this->db->table('users')->selectMax('last_login_at')->get()->getRowArray();
            $lastLoginAt = $row['last_login_at'] ?? null;
        }

        $recentActions = $auditModel->getLogsWithUsers([
            'user_id' => session('user_id'),
        ]);

        $data = [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'last_login_at' => $lastLoginAt,
            'recent_actions' => array_slice($recentActions, 0, 10),
        ];

        return view('dashboard/it_admin/index', $data);
    }

    public function backups()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        return view('dashboard/it_admin/backups', [
            'title' => 'Backups',
            'active' => 'backups',
            'backups' => $this->listBackupFiles(),
        ]);
    }

    public function runBackup()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $auditModel = new AuditLogModel();
        $result = $this->runBackupInternal();

        if ($result['ok'] ?? false) {
            $auditModel->logAction(
                'backup_run',
                'backups',
                null,
                null,
                ['file' => $result['file'] ?? null],
                'Manual backup created'
            );
            return redirect()->to('/it-admin/backups')->with('success', $result['message'] ?? 'Backup created.');
        }

        $auditModel->logAction(
            'backup_failed',
            'backups',
            null,
            null,
            ['error' => $result['message'] ?? null],
            'Backup failed'
        );
        return redirect()->to('/it-admin/backups')->with('error', $result['message'] ?? 'Backup failed.');
    }

    public function downloadBackup($file)
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $name = basename((string) $file);
        if (! preg_match('/^[A-Za-z0-9_\-]+\_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $name)) {
            return redirect()->to('/it-admin/backups')->with('error', 'Invalid backup file.');
        }

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $name;
        if (! is_file($path)) {
            return redirect()->to('/it-admin/backups')->with('error', 'Backup file not found.');
        }

        $auditModel = new AuditLogModel();
        $auditModel->logAction(
            'backup_download',
            'backups',
            null,
            null,
            ['file' => $name],
            'Downloaded backup'
        );

        return $this->response->download($path, null);
    }

    public function users()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $users = $userModel->orderBy('id', 'DESC')->findAll();

        $roles = [
            'warehouse_manager' => 'Warehouse Manager',
            'warehouse_staff' => 'Warehouse Staff',
            'inventory_auditor' => 'Inventory Auditor',
            'procurement_officer' => 'Procurement Officer',
            'accounts_payable_clerk' => 'Accounts Payable Clerk',
            'accounts_receivable_clerk' => 'Accounts Receivable Clerk',
            'it_administrator' => 'IT Administrator',
            'top_management' => 'Top Management',
        ];

        return view('dashboard/it_admin/users', [
            'title' => 'User Management',
            'active' => 'users',
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function createUser()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $roles = [
            'warehouse_manager' => 'Warehouse Manager',
            'warehouse_staff' => 'Warehouse Staff',
            'inventory_auditor' => 'Inventory Auditor',
            'procurement_officer' => 'Procurement Officer',
            'accounts_payable_clerk' => 'Accounts Payable Clerk',
            'accounts_receivable_clerk' => 'Accounts Receivable Clerk',
            'it_administrator' => 'IT Administrator',
            'top_management' => 'Top Management',
        ];

        return view('dashboard/it_admin/user_create', [
            'title' => 'Create User',
            'active' => 'users',
            'roles' => $roles,
        ]);
    }

    public function storeUser()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $allowedRoles = [
            'warehouse_manager',
            'warehouse_staff',
            'inventory_auditor',
            'procurement_officer',
            'accounts_payable_clerk',
            'accounts_receivable_clerk',
            'it_administrator',
            'top_management',
        ];

        $rules = [
            'last_name' => 'required|min_length[2]|max_length[100]',
            'first_name' => 'required|min_length[2]|max_length[100]',
            'middle_name' => 'permit_empty|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'role' => 'required|in_list[' . implode(',', $allowedRoles) . ']',
            'password' => 'required|min_length[6]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            $roles = [
                'warehouse_manager' => 'Warehouse Manager',
                'warehouse_staff' => 'Warehouse Staff',
                'inventory_auditor' => 'Inventory Auditor',
                'procurement_officer' => 'Procurement Officer',
                'accounts_payable_clerk' => 'Accounts Payable Clerk',
                'accounts_receivable_clerk' => 'Accounts Receivable Clerk',
                'it_administrator' => 'IT Administrator',
                'top_management' => 'Top Management',
            ];

            return view('dashboard/it_admin/user_create', [
                'title' => 'Create User',
                'active' => 'users',
                'roles' => $roles,
                'validation' => $this->validator,
            ]);
        }

        $userModel = new UserModel();
        $auditModel = new AuditLogModel();

        $insertData = [
            'last_name' => $this->request->getPost('last_name'),
            'first_name' => $this->request->getPost('first_name'),
            'middle_name' => $this->request->getPost('middle_name'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ];

        if ($this->db->fieldExists('is_active', 'users')) {
            $insertData['is_active'] = 1;
        }

        $newId = $userModel->insert($insertData, true);

        $auditModel->logAction(
            'user_create',
            'user_management',
            (int) $newId,
            null,
            ['email' => $insertData['email'], 'role' => $insertData['role'], 'is_active' => $insertData['is_active'] ?? null],
            'Created user #' . $newId
        );

        return redirect()->to('/it-admin/users')->with('success', 'User created successfully.');
    }

    public function editUser($id)
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        if ((int) $id === (int) session('user_id')) {
            return redirect()->to('/it-admin/users')->with('error', 'Please use the dashboard for your own account.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->to('/it-admin/users')->with('error', 'User not found.');
        }

        $roles = [
            'warehouse_manager' => 'Warehouse Manager',
            'warehouse_staff' => 'Warehouse Staff',
            'inventory_auditor' => 'Inventory Auditor',
            'procurement_officer' => 'Procurement Officer',
            'accounts_payable_clerk' => 'Accounts Payable Clerk',
            'accounts_receivable_clerk' => 'Accounts Receivable Clerk',
            'it_administrator' => 'IT Administrator',
            'top_management' => 'Top Management',
        ];

        return view('dashboard/it_admin/user_edit', [
            'title' => 'Edit User',
            'active' => 'users',
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateUser($id)
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        if ((int) $id === (int) session('user_id')) {
            return redirect()->to('/it-admin/users')->with('error', 'You cannot edit your own account here.');
        }

        $allowedRoles = [
            'warehouse_manager',
            'warehouse_staff',
            'inventory_auditor',
            'procurement_officer',
            'accounts_payable_clerk',
            'accounts_receivable_clerk',
            'it_administrator',
            'top_management',
        ];

        $rules = [
            'last_name' => 'required|min_length[2]|max_length[100]',
            'first_name' => 'required|min_length[2]|max_length[100]',
            'middle_name' => 'permit_empty|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . (int) $id . ']',
            'role' => 'required|in_list[' . implode(',', $allowedRoles) . ']',
            'is_active' => 'permit_empty|in_list[0,1]',
            'password' => 'permit_empty|min_length[6]|max_length[255]',
            'password_confirm' => 'permit_empty|matches[password]',
        ];

        if (! $this->validate($rules)) {
            $userModel = new UserModel();
            $user = $userModel->find($id);
            if (! $user) {
                return redirect()->to('/it-admin/users')->with('error', 'User not found.');
            }

            $roles = [
                'warehouse_manager' => 'Warehouse Manager',
                'warehouse_staff' => 'Warehouse Staff',
                'inventory_auditor' => 'Inventory Auditor',
                'procurement_officer' => 'Procurement Officer',
                'accounts_payable_clerk' => 'Accounts Payable Clerk',
                'accounts_receivable_clerk' => 'Accounts Receivable Clerk',
                'it_administrator' => 'IT Administrator',
                'top_management' => 'Top Management',
            ];

            return view('dashboard/it_admin/user_edit', [
                'title' => 'Edit User',
                'active' => 'users',
                'user' => $user,
                'roles' => $roles,
                'validation' => $this->validator,
            ]);
        }

        $userModel = new UserModel();
        $auditModel = new AuditLogModel();

        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->to('/it-admin/users')->with('error', 'User not found.');
        }

        $updateData = [
            'last_name' => $this->request->getPost('last_name'),
            'first_name' => $this->request->getPost('first_name'),
            'middle_name' => $this->request->getPost('middle_name'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
        ];

        if ($this->db->fieldExists('is_active', 'users')) {
            $postedStatus = $this->request->getPost('is_active');
            if ($postedStatus !== null && $postedStatus !== '') {
                $updateData['is_active'] = (int) $postedStatus;
            }
        }

        $newPassword = $this->request->getPost('password');
        if ($newPassword) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $updateData);

        $auditModel->logAction(
            'user_update',
            'user_management',
            (int) $id,
            ['email' => $user['email'] ?? null, 'role' => $user['role'] ?? null, 'is_active' => $user['is_active'] ?? null],
            ['email' => $updateData['email'] ?? null, 'role' => $updateData['role'] ?? null, 'is_active' => $updateData['is_active'] ?? ($user['is_active'] ?? null)],
            'Updated user #' . $id
        );

        return redirect()->to('/it-admin/users')->with('success', 'User updated successfully.');
    }

    public function updateStatus($id)
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        if ((int) $id === (int) session('user_id')) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $rules = [
            'is_active' => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid status value.');
        }

        $userModel = new UserModel();
        $auditModel = new AuditLogModel();

        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $newStatus = (int) $this->request->getPost('is_active');
        $oldStatus = (int) ($user['is_active'] ?? 1);

        if ($this->db->fieldExists('is_active', 'users')) {
            $userModel->update($id, ['is_active' => $newStatus]);

            $auditModel->logAction(
                'user_status_change',
                'user_management',
                (int) $id,
                ['is_active' => $oldStatus],
                ['is_active' => $newStatus],
                ($newStatus === 1 ? 'Activated' : 'Deactivated') . ' user #' . $id
            );
        }

        return redirect()->to('/it-admin/users')->with('success', 'User status updated.');
    }

    public function updateRole($id)
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $allowedRoles = [
            'warehouse_manager',
            'warehouse_staff',
            'inventory_auditor',
            'procurement_officer',
            'accounts_payable_clerk',
            'accounts_receivable_clerk',
            'it_administrator',
            'top_management',
        ];

        $rules = [
            'role' => 'required|in_list[' . implode(',', $allowedRoles) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid role.');
        }

        $userModel = new UserModel();
        $auditModel = new AuditLogModel();

        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $newRole = $this->request->getPost('role');
        $oldRole = $user['role'] ?? null;

        $userModel->update($id, ['role' => $newRole]);

        $auditModel->logAction(
            'user_role_change',
            'user_management',
            (int) $id,
            ['role' => $oldRole],
            ['role' => $newRole],
            'Changed role for user #' . $id
        );

        return redirect()->to('/it-admin/users')->with('success', 'User role updated.');
    }

    public function auditLogs()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $auditModel = new AuditLogModel();

        $filters = [];
        $module = $this->request->getGet('module');
        $action = $this->request->getGet('action');
        $userId = $this->request->getGet('user_id');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        if ($module) {
            $filters['module'] = $module;
        }
        if ($action) {
            $filters['action'] = $action;
        }
        if ($userId) {
            $filters['user_id'] = $userId;
        }
        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $logs = $auditModel->getLogsWithUsers($filters);

        return view('dashboard/it_admin/audit_logs', [
            'title' => 'Audit Logs',
            'active' => 'audit',
            'logs' => $logs,
            'filters' => [
                'module' => $module,
                'action' => $action,
                'user_id' => $userId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function systemStatus()
    {
        if ($redirect = $this->requireItAdmin()) {
            return $redirect;
        }

        $dbOk = false;
        try {
            $this->db->query('SELECT 1');
            $dbOk = true;
        } catch (\Throwable $e) {
            $dbOk = false;
        }

        $lastLoginAt = null;
        if ($this->db->fieldExists('last_login_at', 'users')) {
            $row = $this->db->table('users')->selectMax('last_login_at')->get()->getRowArray();
            $lastLoginAt = $row['last_login_at'] ?? null;
        }

        return view('dashboard/it_admin/system_status', [
            'title' => 'System Status',
            'active' => 'status',
            'db_ok' => $dbOk,
            'backup_timestamp' => $this->getLatestBackupTimestamp(),
            'last_login_at' => $lastLoginAt,
        ]);
    }
}
