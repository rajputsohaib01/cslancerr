<?php

namespace App\Models;

use CodeIgniter\Model;

class Ion_auth_model extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'username', 'email', 'password', 'salt', 'name', 'user_type',
        'phone', 'image', 'cover', 'device_id', 'devicetype',
        'active', 'token', 'last_login', 'referal_code', 'otp', 'otp_verified'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_on';
    protected $updatedField = 'last_login';

    public function login(string $emailOrUsername, string $password): ?array
{
    $emailOrUsername = trim($emailOrUsername);
    $password = trim($password);

    log_message('debug', 'Login: Input identity = ' . $emailOrUsername);
    log_message('debug', 'Login: Input password = ' . $password);

    $user = $this->where('email', $emailOrUsername)
                 ->orWhere('username', $emailOrUsername)
                 ->first();

    log_message('debug', 'Login: Fetched user = ' . print_r($user, true));

    if (!$user) {
        log_message('debug', 'Login: No matching user found');
        return null;
    }

    $storedHash = $user['password'];

    // Try modern Bcrypt
    $valid = password_verify($password, $storedHash);

    // Fallback: try legacy
    if (!$valid && method_exists($this, 'hash_password_db')) {
        $valid = $this->hash_password_db($user['id'], $password);
        log_message('debug', 'Login: Legacy hash used');
    }

    if ($valid) {
        // Optional: migrate legacy hash to bcrypt
        if (password_needs_rehash($storedHash, PASSWORD_BCRYPT)) {
            $this->update($user['id'], ['password' => password_hash($password, PASSWORD_BCRYPT)]);
            log_message('debug', 'Login: Password rehashed to bcrypt');
        }

        $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return $user;
    }

    log_message('debug', 'Login: Password verification failed');
    return null;
}


    public function register(array $data): bool
{
    if (!isset($data['password'])) {
        return false;
    }

    // Ensure password is hashed
    if (!password_get_info($data['password'])['algo']) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    $data['active'] = $data['active'] ?? 1;
    $data['created_on'] = date('Y-m-d H:i:s');

    return (bool) $this->insert($data);
}

    

    public function user()
    {
        return $this->where('id', session()->get('user_id'))->first();
    }

    public function logout()
    {
        session()->destroy();
        return true;
    }

    // public function errors()
    // {
    //     return 'Invalid login credentials';
    // }

    public function messages()
    {
        return 'Login successful';
    }
}
