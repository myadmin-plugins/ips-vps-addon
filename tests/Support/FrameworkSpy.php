<?php

declare(strict_types=1);

namespace Detain\MyAdminVpsIps\Tests\Support;

/**
 * Records what the addon pushes out into the MyAdmin framework, and holds the
 * framework state the addon reads back (the logged-in role, the next free IP).
 *
 * MyAdmin is not a dependency of this package, so this is what lets a test assert
 * on what the addon actually did instead of on how its source happens to be written.
 */
final class FrameworkSpy
{
    /**
     * Markup passed to add_output(), in order.
     *
     * @var array<int, string>
     */
    public static $output = [];

    /**
     * Positional argument lists of every myadmin_log() call.
     *
     * @var array<int, array<int, mixed>>
     */
    public static $logs = [];

    /**
     * Class/function names passed to function_requirements().
     *
     * @var array<int, string>
     */
    public static $requirements = [];

    /**
     * Server ids passed to vps_get_next_ip().
     *
     * @var array<int, mixed>
     */
    public static $nextIpLookups = [];

    /**
     * What vps_get_next_ip() hands back: an IP, or false when the server is full.
     *
     * @var string|false
     */
    public static $nextIp = '192.0.2.7';

    /**
     * The role of the logged-in user, as \MyAdmin\App::ima() reports it.
     *
     * @var string
     */
    public static $ima = 'client';

    /**
     * Reset every recording and put the framework state back to its defaults.
     */
    public static function reset(): void
    {
        self::$output = [];
        self::$logs = [];
        self::$requirements = [];
        self::$nextIpLookups = [];
        self::$nextIp = '192.0.2.7';
        self::$ima = 'client';
    }

    /**
     * All recorded output joined together, for substring assertions.
     */
    public static function outputText(): string
    {
        return implode("\n", self::$output);
    }
}
