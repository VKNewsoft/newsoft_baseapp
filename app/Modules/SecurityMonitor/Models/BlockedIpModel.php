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

namespace App\Modules\SecurityMonitor\Models;

use CodeIgniter\Model;

class BlockedIpModel extends Model
{
    protected $table = 'blocked_ips';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ip_address', 'blocked_at'];

	public function getFilteredBlockedIps(array $filters = [], int $perPage = 15, string $group = 'default', ?int $page = null): array
	{
		$model = $this;

		if (!empty($filters['search'])) {
			$model = $model->like('ip_address', trim((string) $filters['search']));
		}

		if (!empty($filters['date_from'])) {
			$model = $model->where('blocked_at >=', trim((string) $filters['date_from']) . ' 00:00:00');
		}

		if (!empty($filters['date_to'])) {
			$model = $model->where('blocked_at <=', trim((string) $filters['date_to']) . ' 23:59:59');
		}

		return $model->orderBy('blocked_at', 'DESC')->paginate($perPage, $group, $page);
	}

	public function getSummary(array $filters = []): array
	{
		$builder = $this->builder();

		if (!empty($filters['search'])) {
			$builder->like('ip_address', trim((string) $filters['search']));
		}

		if (!empty($filters['date_from'])) {
			$builder->where('blocked_at >=', trim((string) $filters['date_from']) . ' 00:00:00');
		}

		if (!empty($filters['date_to'])) {
			$builder->where('blocked_at <=', trim((string) $filters['date_to']) . ' 23:59:59');
		}

		$row = $builder
			->select('COUNT(*) as total_blocked, SUM(CASE WHEN DATE(blocked_at) = CURDATE() THEN 1 ELSE 0 END) as blocked_today', false)
			->get()
			->getRowArray();

		return [
			'total_blocked' => (int) ($row['total_blocked'] ?? 0),
			'blocked_today' => (int) ($row['blocked_today'] ?? 0),
		];
	}

	public function getDataTableResult(array $filters = [], array $dt = []): array
	{
		$columns = [
			0 => 'id',
			1 => 'ip_address',
			2 => 'blocked_at',
			3 => 'blocked_at',
			4 => 'ip_address',
		];

		$start = max(0, (int) ($dt['start'] ?? 0));
		$length = (int) ($dt['length'] ?? 15);
		$length = $length > 0 ? min($length, 50) : 15;
		$orderIndex = (int) ($dt['order'][0]['column'] ?? 2);
		$orderDir = strtolower((string) ($dt['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
		$orderColumn = $columns[$orderIndex] ?? 'blocked_at';

		$total = $this->countAllResults();
		$model = $this;

		if (!empty($filters['search'])) {
			$model = $model->like('ip_address', trim((string) $filters['search']));
		}
		if (!empty($filters['date_from'])) {
			$model = $model->where('blocked_at >=', trim((string) $filters['date_from']) . ' 00:00:00');
		}
		if (!empty($filters['date_to'])) {
			$model = $model->where('blocked_at <=', trim((string) $filters['date_to']) . ' 23:59:59');
		}

		$filtered = $model->countAllResults(false);
		$rows = $model->orderBy($orderColumn, $orderDir)->findAll($length, $start);

		return [
			'total' => $total,
			'filtered' => $filtered,
			'rows' => $rows,
			'loaded' => count($rows),
		];
	}

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
