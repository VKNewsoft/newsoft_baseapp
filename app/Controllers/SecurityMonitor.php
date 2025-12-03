<?php
/**
 * SecurityMonitor.php
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

namespace App\Controllers;

use App\Models\SecurityLogModel;
use App\Models\BlockedIpModel;

class Securitymonitor extends \App\Controllers\BaseController
{
    public function __construct() {
		
		parent::__construct();
		
		$this->logModel = new SecurityLogModel();
        $this->blockModel = new BlockedIpModel();
        $this->data['title'] = 'Security Aplikasi';
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/identitas.js');
		
		helper(['cookie', 'form']);
	}

    public function index()
    {
        $cache = \Config\Services::cache();
        
        // Cache stats untuk 2 menit (mengurangi query berulang)
        $stats = $cache->get('security_stats');
        if (!$stats) {
            $stats = [
                'total_attacks' => $this->logModel->getTotalAttacks(),
                'today_attacks' => $this->logModel->getAttacksToday(),
                'blocked_count' => $this->blockModel->countAllResults()
            ];
            $cache->save('security_stats', $stats, 120); // 2 menit
        }
        
        $this->data = array_merge($this->data, $stats);
        
        // Pagination untuk recent logs
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 15;
        
        $this->data['recent_logs'] = $this->logModel
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);
        
        $this->data['pager'] = $this->logModel->pager;
        
        $this->view('security_monitor/index.php', $this->data);
    }
    
    // AJAX endpoint untuk chart data (lazy loading)
    public function chartData()
    {
        $cache = \Config\Services::cache();
        
        $chartData = $cache->get('security_charts');
        if (!$chartData) {
            $attacksLast7Days = $this->logModel->getAttacksByDay(6);
            $attackTypes = $this->logModel->getLogsWithCount(5); // Limit to top 5
            
            $chartData = [
                'timeline' => [
                    'labels' => array_keys($attacksLast7Days),
                    'data' => array_values($attacksLast7Days)
                ],
                'types' => [
                    'labels' => array_column($attackTypes, 'attack_type'),
                    'data' => array_column($attackTypes, 'total')
                ]
            ];
            
            $cache->save('security_charts', $chartData, 300); // 5 menit
        }
        
        return $this->response->setJSON($chartData);
    }

    public function blocked()
    {
        $search = $this->request->getGet('search');
        $model = $this->blockModel;

        if ($search) {
            $model = $model->like('ip_address', $search);
        }

        $this->data += [
            'blocked_ips' => $model->orderBy('blocked_at', 'DESC')->paginate(15),
            'pager' => $model->pager,
            'search' => $search ?? ''
        ];

        // return view('security_monitor/blocked', $data);
         $this->view('security_monitor/blocked.php', $this->data);
    }

    public function unblock()
    {
        $ip = $this->request->getPost('ip');
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->blockModel->unblockIp($ip);
            return $this->response->setJSON(['success' => true, 'message' => "IP $ip berhasil dibuka."]);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'IP tidak valid.']);
    }
}