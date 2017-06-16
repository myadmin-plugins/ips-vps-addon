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

	public static function Hooks() {
		return [
			'vps.load_addons' => ['Detain\MyAdminVpsIps\Plugin', 'Load'],
			'vps.settings' => ['Detain\MyAdminVpsIps\Plugin', 'Settings'],
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
			->set_require_ip(true)
			->set_enable(['Detain\MyAdminVpsIps\Plugins', 'Enable'])
			->set_disable(['Detain\MyAdminVpsIps\Plugins', 'Disable'])
			->register();
		$service_order->add_addon($addon);
	}

	public static function Enable($service_order) {
		$service_info = $service_order->get_service_info();
		$settings = get_module_settings($service_order->get_module());
	}

	public static function Disable($service_order) {
		$service_info = $service_order->get_service_info();
		$settings = get_module_settings($service_order->get_module());
	}

	public static function Settings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Addon Costs', 'vps_ip_cost', 'VPS Additional IP Cost:', 'This is the cost for purchasing an additional IP on top of a VPS.', $settings->get_setting('VPS_IP_COST'));
		$settings->add_text_setting($module, 'Slice Amounts', 'vps_max_ips', 'Max Addon IP Addresses:', 'Maximum amount of additional IPs you can add to your VPS', $settings->get_setting('VPS_MAX_IPS'));
	}
}
