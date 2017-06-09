<?php

namespace Detain\MyAdminVpsIps;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public function __construct() {
	}

	public static function Load(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('class.Addon');
		$addon = new \Addon();
		$addon->set_module('vps')->set_text('Additional IP')->set_text_match('Additional IP (.*)')
			->set_cost(VPS_IP_COST)->set_require_ip(true)->set_enable(function() {
			})->set_disable(function() {
			})->register();
		$service->add_addon($addon);
	}

	public static function Settings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Addon Costs', 'vps_ip_cost', 'VPS Additional IP Cost:', 'This is the cost for purchasing an additional IP on top of a VPS.', $settings->get_setting('VPS_IP_COST'));
		$settings->add_text_setting($module, 'Slice Amounts', 'vps_max_ips', 'Max Addon IP Addresses:', 'Maximum amount of additional IPs you can add to your VPS', $settings->get_setting('VPS_MAX_IPS'));
	}
}
