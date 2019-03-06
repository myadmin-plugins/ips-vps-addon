<?php

namespace Detain\MyAdminVpsIps;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminVpsIps
 */
class Plugin
{
	public static $name = 'Additional IPs VPS Addon';
	public static $description = 'Allows selling of additional IP Addresses as a VPS Addon.';
	public static $help = '';
	public static $module = 'vps';
	public static $type = 'addon';

	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @return array
	 */
	public static function getHooks()
	{
		return [
			'function.requirements' => [__CLASS__, 'getRequirements'],
			self::$module.'.load_addons' => [__CLASS__, 'getAddon'],
			self::$module.'.settings' => [__CLASS__, 'getSettings']
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event)
	{
        /**
         * @var \MyAdmin\Plugins\Loader $this->loader
         */
        $loader = $event->getSubject();
		$loader->add_page_requirement('vps_ips', '/../vendor/detain/myadmin-ips-vps-addon/src/vps_ips.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getAddon(GenericEvent $event)
	{
		/**
		 * @var \ServiceHandler $service
		 */
		$service = $event->getSubject();
		function_requirements('class.AddonHandler');
		$addon = new \AddonHandler();
		$addon->setModule(self::$module)
			->set_text('Additional IP')
			->set_text_match('Additional IP (.*)')
			->set_cost(VPS_IP_COST)
			->set_require_ip(true)
			->setEnable([__CLASS__, 'doEnable'])
			->setDisable([__CLASS__, 'doDisable'])
			->register();
		$service->addAddon($addon);
	}

	/**
	 * @param \ServiceHandler $serviceOrder
	 * @param                $repeatInvoiceId
	 * @param bool           $regexMatch
	 */
	public static function doEnable(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)
	{
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		$db = get_module_db(self::$module);
		myadmin_log(self::$module, 'info', self::$name.' Activation', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
		if ($regexMatch === false) {
			$ip = vps_get_next_ip($serviceInfo[$settings['PREFIX'].'_server']);
			myadmin_log(self::$module, 'info', 'Trying To Give '.$settings['TITLE'].' '.$serviceInfo[$settings['PREFIX'].'_id'].' Repeat Invoice '.$repeatInvoiceId.' IP '.($ip === false ? '<ip allocation failed>' : $ip), __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
			if ($ip) {
				$GLOBALS['tf']->history->add(self::$module.'queue', $serviceInfo[$settings['PREFIX'].'_id'], 'add_ip', $ip, $serviceInfo[$settings['PREFIX'].'_custid']);
				$description = 'Additional IP '.$ip.' for '.$settings['TBLNAME'].' '.$serviceInfo[$settings['PREFIX'].'_id'];
				$rdescription = '(Repeat Invoice: '.$repeatInvoiceId.') '.$description;
				$db->query("update {$settings['PREFIX']}_ips set ips_main=0,ips_used=1,ips_{$settings['PREFIX']}={$serviceInfo[$settings['PREFIX'].'_id']} where ips_ip='{$ip}'", __LINE__, __FILE__);
				$db->query("update invoices set invoices_description='{$rdescription}' where invoices_type=1 and invoices_extra='{$repeatInvoiceId}'", __LINE__, __FILE__);
				$db->query("update repeat_invoices set repeat_invoices_description='{$description}' where repeat_invoices_id='{$repeatInvoiceId}'", __LINE__, __FILE__);
			} else {
				$db->query('SELECT * FROM '.$settings['PREFIX'].'_masters WHERE '.$settings['PREFIX'].'_id='.$serviceInfo[$settings['PREFIX'].'_server'], __LINE__, __FILE__);
				$db->next_record(MYSQL_ASSOC);
				$headers = '';
				$headers .= 'MIME-Version: 1.0'.PHP_EOL;
				$headers .= 'Content-type: text/html; charset=UTF-8'.PHP_EOL;
				$headers .= 'From: '.TITLE.' <'.EMAIL_FROM.'>'.PHP_EOL;
				$subject = '0 Free IPs On '.$settings['TBLNAME'].' Server '.$db->Record[$settings['PREFIX'].'_name'];
				admin_mail($subject, $settings['TBLNAME']." {$serviceInfo[$settings['PREFIX'].'_id']} Has Pending IPS<br>\n".$subject, $headers, false, 'admin/vps_no_ips.tpl');
			}
		} else {
			$ip = $regexMatch;
			$GLOBALS['tf']->history->add(self::$module.'queue', $serviceInfo[$settings['PREFIX'].'_id'], 'ensure_addon_ip', $ip, $serviceInfo[$settings['PREFIX'].'_custid']);
			$db->query("update {$settings['PREFIX']}_ips set ips_main=0,ips_used=1,ips_{$settings['PREFIX']}={$serviceInfo[$settings['PREFIX'].'_id']} where ips_ip='{$ip}'", __LINE__, __FILE__);
		}
	}

	/**
	 * @param \ServiceHandler $serviceOrder
	 * @param                $repeatInvoiceId
	 * @param bool           $regexMatch
	 */
	public static function doDisable(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)
	{
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		myadmin_log(self::$module, 'info', self::$name.' Deactivation', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
		if ($regexMatch !== false) {
			$ip = $regexMatch;
			$GLOBALS['tf']->history->add(self::$module.'queue', $serviceInfo[$settings['PREFIX'].'_id'], 'remove_ip', $ip, $serviceInfo[$settings['PREFIX'].'_custid']);
		} else {
			$ip = 'None Assigned Yet';
		}
		//$db->query("update {$settings['PREFIX']}_ips set ips_main=0,ips_used=0,ips_{$settings['PREFIX']}=0 where ips_ip='{$ip}'", __LINE__, __FILE__);
		add_output('IP Removed And Canceled');
		$email = $settings['TBLNAME'].' ID: '.$serviceInfo[$settings['PREFIX'].'_id'].'<br>'.$settings['TBLNAME'].' Hostname: '.$serviceInfo[$settings['PREFIX'].'_hostname'].'<br>Repeat Invoice: '.$repeatInvoiceId.'<br>Description: '.self::$name.'<br>IP: '.$ip;
		$subject = $settings['TBLNAME'].' '.$serviceInfo[$settings['PREFIX'].'_id'].' Canceled IP '.$ip;
		$headers = '';
		$headers .= 'MIME-Version: 1.0'.PHP_EOL;
		$headers .= 'Content-type: text/html; charset=UTF-8'.PHP_EOL;
		$headers .= 'From: '.$settings['TITLE'].' <'.$settings['EMAIL_FROM'].'>'.PHP_EOL;
		admin_mail($subject, $email, $headers, false, 'admin/vps_ip_canceled.tpl');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
    public static function getSettings(GenericEvent $event)
    {
        /**
         * @var \MyAdmin\Settings $settings
         **/
        $settings = $event->getSubject();
		$settings->add_text_setting(self::$module, _('Addon Costs'), 'vps_ip_cost', _('VPS Additional IP Cost'), _('This is the cost for purchasing an additional IP on top of a VPS.'), $settings->get_setting('VPS_IP_COST'));
		$settings->add_text_setting(self::$module, _('Slice Amounts'), 'vps_max_ips', _('Max Addon IP Addresses'), _('Maximum amount of additional IPs you can add to your VPS'), $settings->get_setting('VPS_MAX_IPS'));
	}
}
