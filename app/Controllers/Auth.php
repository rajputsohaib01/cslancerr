<?php
// app/Controllers/Auth.php

namespace App\Controllers;

use App\Models\Ion_auth_model; // or rename to AuthModel
use CodeIgniter\Controller;
use Config\Services;

class Auth extends Controller
{
    protected $ionAuth;
    protected $session;
    protected $request;
    protected $validation;

    public function __construct()
    {
        helper(['url', 'form', 'text']);
        $this->session = session();
        $this->request = Services::request();
        $this->validation = Services::validation();
        $this->ionAuth = new Ion_auth_model(); // Uses the updated login() method
    }

	public function login()
	{
		if (!$this->request->is('post')) {
			return view('auth/login');
		}
	
		$emailOrUsername = $this->request->getPost('identity') 
							?? $this->request->getPost('email') 
							?? $this->request->getPost('username') 
							?? '';
		$password = $this->request->getPost('password') ?? '';
	
		$user = $this->ionAuth->login($emailOrUsername, $password); // use model's login()
	
		if ($user) {
			$this->session->set('user_id', $user['id']);
			return $this->response->setJSON([
				'status' => 'success',
				'redirect' => base_url('dashboard')
			]);
		}
	
		return $this->response->setJSON([
			'status' => 'error',
			'message' => 'Invalid login credentials'
		]);
	}
	


	public function register()
{
    if (!$this->request->is('post')) {
        return view('auth/register');
    }

    $data = [
        'username' => trim($this->request->getPost('username')),
        'email'    => trim($this->request->getPost('email')),
        'password' => $this->request->getPost('password'),
        'name'     => trim($this->request->getPost('name')),
    ];

    // Simple validation
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Username, email, and password are required.'
        ]);
    }

    // Email uniqueness
    if ($this->ionAuth->where('email', $data['email'])->first()) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Email already registered.'
        ]);
    }

    // Username uniqueness
    if ($this->ionAuth->where('username', $data['username'])->first()) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Username already taken.'
        ]);
    }

    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    $data['active']   = 1;
    $data['created_on'] = date('Y-m-d H:i:s');

	if (!$this->ionAuth->register($data)) {
		return $this->response->setJSON([
			'status' => 'error',
			'message' => 'Registration failed. Try again.'
		]);
	}
	

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Registration successful.',
        'redirect' => base_url('auth/login')
    ]);
}


    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url('login'))->with('message', 'Logged out successfully.');
    }
}
