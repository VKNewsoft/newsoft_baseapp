<?php
/**
 * SecurityLogModel - Model untuk log serangan keamanan
 * 
 * Model ini mencatat dan menganalisis serangan seperti:
 * - SQL Injection
 * - XSS
 * - CSRF
 * - Path Traversal
 * - Brute Force
 * 
 * @package App\Models
 * @year 2020-2025
 */

namespace App\Models;

use CodeIgniter\Model;

class SecurityLogModel extends Model
{
    protected $table = 'security_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ip_address', 'attack_type', 'request_uri', 'user_agent', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Mendapatkan statistik serangan berdasarkan tipe
     * Diurutkan dari yang paling banyak
     * 
     * @param int $limit Jumlah maksimal hasil
     * @return array Statistik serangan per tipe
     */
    public function getLogsWithCount($limit = 10)
    {
        return $this->select('attack_type, COUNT(*) as total')
                   ->groupBy('attack_type')
                   ->orderBy('total', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Hitung total semua serangan yang tercatat
     * 
     * @return int Total serangan
     */
    public function getTotalAttacks()
    {
        return $this->countAllResults();
    }

    /**
     * Hitung serangan yang terjadi hari ini
     * 
     * @return int Jumlah serangan hari ini
     */
    public function getAttacksToday()
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
    }

    /**
     * Mendapatkan statistik serangan per hari
     * Data dikembalikan untuk N hari terakhir dengan nilai 0 untuk hari tanpa serangan
     * 
     * @param int $days Jumlah hari ke belakang (default 7)
     * @return array Array dengan key tanggal dan value jumlah serangan
     */
    public function getAttacksByDay($days = 7)
    {
        $result = $this->select('DATE(created_at) as date, COUNT(*) as total')
                       ->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")))
                       ->groupBy('DATE(created_at)')
                       ->orderBy('date')
                       ->findAll();

        // Buat array dengan semua tanggal, default value 0
        $data = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data[$date] = 0;
        }

        // Isi dengan data aktual dari database
        foreach ($result as $row) {
            $data[$row['date']] = (int)$row['total'];
        }

        return $data;
    }

    /**
     * Pencarian log serangan dengan filter
     * 
     * @param string|null $ip IP address untuk filter (LIKE search)
     * @param string|null $type Tipe serangan
     * @param string|null $startDate Tanggal mulai (Y-m-d)
     * @param string|null $endDate Tanggal akhir (Y-m-d)
     * @return array Hasil pencarian dengan pagination
     */
    public function search($ip = null, $type = null, $startDate = null, $endDate = null)
    {
        if ($ip) $this->like('ip_address', $ip);
        if ($type) $this->where('attack_type', $type);
        if ($startDate) $this->where('created_at >=', $startDate);
        if ($endDate) $this->where('created_at <=', $endDate . ' 23:59:59');
        
        return $this->orderBy('created_at', 'DESC')->paginate(10);
    }
}