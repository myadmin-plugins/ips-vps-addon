<?php

namespace Detain\MyAdminVpsIps;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public static $name = 'Ips Licensing VPS Addon';
	public static $description = 'Allows selling of Ips Server and VPS License Types.  More info at https://www.netenberg.com/ips.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a ips license. Allow 10 minutes for activation.';
	public static $module = 'vps';
	public static $type = 'addon';


	public function __construct() {
	}

	public static function getHooks() {
		return [
			'vps.load_addons' => [__CLASS__, 'Load'],
			'vps.settings' => [__CLASS__, 'getSettings'],
		];
	}

	public static function Load(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('class.Addon');
		$addon = new \Addon();
		$addon->set_module('vps')
			->set_text('Additional IP')
			->set_text_match('Additional IP (.*)')
			->set_cost(VPS_IP_COST)
			->set_require_ip(TRUE)
			->set_enable([__CLASS__, 'Enable'])
			->set_disable([__CLASS__, 'Disable'])
			->register();
		$service->add_addon($addon);
	}

	public static function Enable(\Service_Order $serviceOrder) {
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings($serviceOrder->get_module());
	}

	public static function Disable(\Service_Order $serviceOrder) {
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings($serviceOrder->get_module());
	}

	public static function getSettings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Addon Costs', 'vps_ip_cost', 'VPS Additional IP Cost:', 'This is the cost for purchasing an additional IP on top of a VPS.', $settings->get_setting('VPS_IP_COST'));
		$settings->add_text_setting($module, 'Slice Amounts', 'vps_max_ips', 'Max Addon IP Addresses:', 'Maximum amount of additional IPs you can add to your VPS', $settings->get_setting('VPS_MAX_IPS'));
	}
}
