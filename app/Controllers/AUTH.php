<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AUTH extends Controller
{
    protected $builder;
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('users');
    }

    public function login()
    {
        helper(['form']);
        $data = [];

        if ($this->request->is('post')) {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]|max_length[255]'
            ];

            if ($this->validate($rules)) {
                $email    = $this->request->getPost('email');
                $password = $this->request->getPost('password');

                $user = $this->builder
                    ->where('email', $email)
                    ->get()
                    ->getRowArray();

                // Check if user exists
                if (!$user) {
                    session()->setFlashdata('error', 'Invalid email or password.');
                    return view('auth/login', $data);
                }

                // Verify password
                if (!password_verify($password, $user['password'])) {
                    session()->setFlashdata('error', 'Invalid email or password.');
                    return view('auth/login', $data);
                }

                if ($this->db->fieldExists('is_active', 'users') && isset($user['is_active']) && (int) $user['is_active'] === 0) {
                    session()->setFlashdata('error', 'Account is deactivated. Please contact the administrator.');
                    return view('auth/login', $data);
                }

                if ($this->db->fieldExists('last_login_at', 'users')) {
                    $this->builder->where('id', $user['id'])->update([
                        'last_login_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // Login successful - set session data
                session()->set([
                    'user_id'      => $user['id'],
                    'user_email'   => $user['email'],
                    'user_role'    => $user['role'],
                    'user_lname'   => $user['last_name'],
                    'user_fname'   => $user['first_name'],
                    'user_mname'   => $user['middle_name'],
                    'logged_in'    => true
                ]);

                // Role-based redirection
                switch ($user['role']) {
                        case 'warehouse_manager':
                            return redirect()->to('/inventory');
                        case 'warehouse_staff':
                            return redirect()->to('/dashboard/staff');
                        case 'inventory_auditor':
                            return redirect()->to('/dashboard/auditor');
                        case 'procurement_officer':
                            return redirect()->to('/dashboard/procurement');
                        case 'accounts_payable_clerk':
                            return redirect()->to('/dashboard/apclerk');
                        case 'accounts_receivable_clerk':
                            return redirect()->to('/dashboard/arclerk');
                        case 'it_administrator':
                            return redirect()->to('/dashboard/it');
                        case 'top_management':
                            return redirect()->to('/dashboard/top');
                        default:
                            return redirect()->to('/');
                }
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/login', $data);
    }

    public function register()
    {
        helper(['form']);
        $data = [];

        if ($this->request->is('post')) {
            $rules = [
                'last_name'         => 'required|min_length[2]|max_length[100]',
                'first_name'        => 'required|min_length[2]|max_length[100]',
                'middle_name'       => 'permit_empty|max_length[100]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[6]|max_length[255]',
                'password_confirm'  => 'matches[password]'
            ];

            if ($this->validate($rules)) {
                $model = new UserModel();
                $model->save([
                    'last_name'   => $this->request->getVar('last_name'),
                    'first_name'  => $this->request->getVar('first_name'),
                    'middle_name' => $this->request->getVar('middle_name'),
                    'email'       => $this->request->getVar('email'),
                    'password'    => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                    'role'        => 'warehouse_staff'
                ]);
                session()->setFlashdata('success', 'Registration successful. Please login.');
                return redirect()->to('/login');
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/register', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    // Debug method to check users in database
    public function testdb()
    {
        $users = $this->builder->get()->getResultArray();
        echo '<h3>Users in database:</h3>';
        echo '<pre>';
        foreach($users as $user) {
            echo "ID: " . $user['id'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Role: " . $user['role'] . "\n";
            echo "First Name: " . $user['first_name'] . "\n";
            echo "Last Name: " . $user['last_name'] . "\n";
            echo "Password (hashed): " . substr($user['password'], 0, 20) . "...\n";
            echo "---\n";
        }
        echo '</pre>';
    }
}
