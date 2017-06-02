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

}
