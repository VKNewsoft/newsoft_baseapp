<?php
/**
 * BlockedIpModel - Model untuk manajemen IP yang diblokir
 * 
 * Model ini menangani daftar IP address yang diblokir
 * karena terdeteksi melakukan serangan atau pelanggaran
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;

use CodeIgniter\Model;

class BlockedIpModel extends Model
{
    protected $table = 'blocked_ips';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ip_address', 'blocked_at'];

    /**
     * Unblock IP address (hapus dari daftar blokir)
     * 
     * @param string $ip IP address yang akan di-unblock
     * @return bool Status penghapusan
     */
    public function unblockIp($ip)
    {
        return $this->where('ip_address', $ip)->delete();
    }

    /**
     * Cek apakah IP address sedang diblokir
     * 
     * @param string $ip IP address yang akan dicek
     * @return bool True jika IP diblokir, false jika tidak
     */
    public function isBlocked($ip)
    {
        return $this->where('ip_address', $ip)->countAllResults() > 0;
    }
}