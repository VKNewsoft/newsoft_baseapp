<?php
/**
 * rbac_helper.php
 * Role-Based Access Control Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

/**
 * @deprecated This function is deprecated. Use proper Query Builder methods instead.
 * Legacy function for row-level security based on user ownership
 */
function where_own($column = null) 
{
	global $list_action, $check_role_action;
	
	if (!$column)
		$column = $check_role_action['field'];
		
	if ($list_action['read_data'] == 'own') {
		$session = \Config\Services::session();
		$userId = $session->get('user')['id_user'] ?? 0;
		return ' WHERE ' . $column . ' = ' . $userId;
	}
	
	return ' WHERE 1 = 1 ';
}