<?php
/**
 * VPS Functionality
 *
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2017
 * @package MyAdmin
 * @category VPS
 */

/**
 * function for the vps_ips addon code
 *
 * @param $addon
 * @return bool
 */
function vps_ips_check_current($addon) {
	$addon->db->query("select * from invoices left join repeat_invoices on repeat_invoices_custid=invoices_custid and repeat_invoices_service=invoices_service and repeat_invoices_id=invoices_extra where invoices_custid={$addon->serviceInfo[$addon->settings['PREFIX'].'_custid']} and invoices_type=1 and invoices_service={$addon->serviceInfo[$addon->settings['PREFIX'].'_id']} and invoices_description like '%Additional IP%' group by invoices_extra", __LINE__, __FILE__);
	//$addon->db->query("select repeat_invoices_id, repeat_invoices_description from repeat_invoices where repeat_invoices_description like 'Additional IP%for {$addon->settings['TBLNAME']} {$addon->serviceInfo[$addon->settings['PREFIX'].'_id']}'", __LINE__, __FILE__);
	$ips = $addon->db->num_rows();
	if ($ips > 0) {
		while ($addon->db->next_record(MYSQL_ASSOC)) {
			$pre = "
			<div class='form-group'>
				<label class='col-sm-7 control-label'>Additional IP:</label>
				<div class='col-sm-5 control-label' style='text-align: left;'>";
			$link_disable = $GLOBALS['tf']->link('index.php', str_replace(['{$module}', '{$rid}'], [$addon->module, $addon->db->Record['invoices_extra']], $addon->disable_link));
			$post = "<a href='{$link_disable}' title='Cancel Additional IP' class='btn btn-xs btn-default' style='padding: 1px; margin-bottom: 3px;'><i class='glyphicon glyphicon-remove'></i></a>" .
				'
				</div>
			</div>';
			if (preg_match('/Additional IP (.*) for '.$addon->settings['TBLNAME'].' '.$addon->serviceInfo[$addon->settings['PREFIX'].'_id'].'$/', $addon->db->Record['repeat_invoices_description'], $matches)) {
				add_output($pre.$matches[1].$post);
			} elseif (preg_match('/Additional IP (.*) for '.$addon->settings['TBLNAME'].' '.$addon->serviceInfo[$addon->settings['PREFIX'].'_id'].'$/', $addon->db->Record['invoices_description'], $matches)) {
				//add_output($pre.'Inactive '.$matches[1].$post);
			} else {
				add_output($pre.'Unpaid'.$post);
			}
		}
	}
	$nextip = vps_get_next_ip($addon->serviceInfo[$addon->settings['PREFIX'].'_server']);
	if ($nextip === false) {
		$addon->alert('<i class="fa fa-warning"></i> No available free ips on this server. Please contact support to order additional ips..');
		return false;
	} elseif ($ips >= VPS_MAX_IPS) {
		if ($GLOBALS['tf']->ima == 'admin') {
			$addon->alert('<i class="fa fa-warning"></i> VPS already has the maximum number of IPs normally allowed, but allowing this because user is admin.');
		} else {
			$addon->alert('<i class="fa fa-warning"></i> VPS already has the maximum number of IPs allowed.  If you require additional IPs please contact support.');
			return false;
		}
	}
	return true;
}

function vps_ips() {
	function_requirements('class.AddServiceAddon');
	$addon = new AddServiceAddon();
	$addon->allow_multiple = true;
	$addon->get_service_master = true;
	$addon->load(__FUNCTION__, 'Additional IP', 'vps', VPS_IP_COST, 'ip');
	$addon->bind_event('vps_ips_check_current', 'build_summary_header');
	$addon->process();
}
