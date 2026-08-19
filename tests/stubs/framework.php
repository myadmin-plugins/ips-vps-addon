<?php

declare(strict_types=1);

/**
 * Stand-ins for the MyAdmin framework services that src/Plugin.php and
 * src/vps_ips.php call out to.
 *
 * MyAdmin itself is not a dependency of this package, so these stubs are what make
 * it possible to dispatch the plugin's hooks and run the addon's summary callback
 * for real inside the test suite. Everything observable is recorded on FrameworkSpy.
 */

namespace MyAdmin {
    use Detain\MyAdminVpsIps\Tests\Support\FrameworkSpy;

    class App
    {
        /**
         * The role of the logged-in user. The addon relaxes the per-VPS IP limit for
         * admins, so tests drive this through FrameworkSpy::$ima.
         *
         * @return string
         */
        public static function ima()
        {
            return FrameworkSpy::$ima;
        }

        /**
         * @param  string $page
         * @param  string $query
         * @return string
         */
        public static function link($page, $query = '')
        {
            return $query === '' ? $page : $page . '?' . $query;
        }

        /**
         * @param  string $function
         * @return bool
         */
        public static function functionRequirements($function)
        {
            FrameworkSpy::$requirements[] = $function;
            return true;
        }

        /**
         * @return object
         */
        public static function history()
        {
            return new class () {
                /**
                 * @param  mixed ...$args
                 * @return void
                 */
                public function add(...$args)
                {
                }
            };
        }
    }
}

namespace {
    use Detain\MyAdminVpsIps\Tests\Support\FrameworkSpy;

    if (!function_exists('add_output')) {
        /**
         * @param  string $output
         * @return void
         */
        function add_output($output)
        {
            FrameworkSpy::$output[] = $output;
        }
    }

    if (!function_exists('myadmin_log')) {
        /**
         * @param  mixed ...$args
         * @return void
         */
        function myadmin_log(...$args)
        {
            FrameworkSpy::$logs[] = $args;
        }
    }

    if (!function_exists('vps_get_next_ip')) {
        /**
         * @param  mixed $server
         * @return string|false the next free IP on the server, or false when full
         */
        function vps_get_next_ip($server)
        {
            FrameworkSpy::$nextIpLookups[] = $server;
            return FrameworkSpy::$nextIp;
        }
    }

    if (!function_exists('vps_get_free_ips')) {
        /**
         * @param  mixed $server
         * @return array<int, string> every free IP on the server, empty when full
         */
        function vps_get_free_ips($server)
        {
            FrameworkSpy::$freeIpLookups[] = $server;
            return FrameworkSpy::$freeIps;
        }
    }

    if (!function_exists('validIp')) {
        /**
         * @param  string $ip
         * @return bool
         */
        function validIp($ip)
        {
            return filter_var($ip, FILTER_VALIDATE_IP) !== false;
        }
    }

    // gettext is not guaranteed to be enabled on every CI leg.
    if (!function_exists('_')) {
        /**
         * @param  string $string
         * @return string
         */
        function _($string)
        {
            return $string;
        }
    }

    if (!class_exists('AddonHandler')) {
        /**
         * Records the addon configuration the plugin builds, in place of MyAdmin's
         * real handler. Every setter is fluent, as the plugin chains all of them.
         */
        class AddonHandler
        {
            /** @var array<string, mixed> */
            public $config = [];

            /** @var bool */
            public $registered = false;

            public function setModule($module)
            {
                $this->config['module'] = $module;
                return $this;
            }

            public function set_text($text)
            {
                $this->config['text'] = $text;
                return $this;
            }

            public function set_text_match($match)
            {
                $this->config['text_match'] = $match;
                return $this;
            }

            public function set_cost($cost)
            {
                $this->config['cost'] = $cost;
                return $this;
            }

            public function set_require_ip($require)
            {
                $this->config['require_ip'] = $require;
                return $this;
            }

            public function setEnable($callback)
            {
                $this->config['enable'] = $callback;
                return $this;
            }

            public function setDisable($callback)
            {
                $this->config['disable'] = $callback;
                return $this;
            }

            public function register()
            {
                $this->registered = true;
                return $this;
            }
        }
    }
}
