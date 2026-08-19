<?php

namespace Detain\MyAdminVpsIps\Tests;

use Detain\MyAdminVpsIps\Tests\Support\DbDouble;
use Detain\MyAdminVpsIps\Tests\Support\FrameworkSpy;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the vps_ips.php functions file.
 */
class VpsIpsFunctionsTest extends TestCase
{
    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @var string
     */
    private $contents;

    /**
     * MyAdmin pulls this file in through function_requirements() at request time;
     * the tests below call its functions directly.
     */
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__) . '/src/vps_ips.php';
    }

    protected function setUp(): void
    {
        $this->sourceFile = dirname(__DIR__) . '/src/vps_ips.php';
        $this->contents = file_get_contents($this->sourceFile);
    }

    /**
     * Tests that the vps_ips.php source file exists.
     */
    public function testSourceFileExists(): void
    {
        $this->assertFileExists($this->sourceFile);
    }

    /**
     * Tests that vps_ips_check_current function is defined in the source file.
     */
    public function testVpsIpsCheckCurrentFunctionIsDefined(): void
    {
        $this->assertStringContainsString('function vps_ips_check_current(', $this->contents);
    }

    /**
     * Tests that vps_ips function is defined in the source file.
     */
    public function testVpsIpsFunctionIsDefined(): void
    {
        $this->assertStringContainsString('function vps_ips(', $this->contents);
    }

    /**
     * Tests that vps_ips_check_current accepts an $addon parameter.
     */
    public function testVpsIpsCheckCurrentAcceptsAddonParameter(): void
    {
        $this->assertMatchesRegularExpression('/function\s+vps_ips_check_current\s*\(\s*\$addon\s*\)/', $this->contents);
    }

    /**
     * Tests that vps_ips function takes no parameters.
     */
    public function testVpsIpsFunctionTakesNoParameters(): void
    {
        $this->assertMatchesRegularExpression('/function\s+vps_ips\s*\(\s*\)/', $this->contents);
    }

    /**
     * Tests that vps_ips references AddServiceAddon.
     */
    public function testVpsIpsReferencesAddServiceAddon(): void
    {
        $this->assertStringContainsString('AddServiceAddon', $this->contents);
    }

    /**
     * Tests that vps_ips sets allow_multiple to true.
     */
    public function testVpsIpsSetsAllowMultiple(): void
    {
        $this->assertStringContainsString('allow_multiple = true', $this->contents);
    }

    /**
     * Tests that vps_ips sets get_service_master to true.
     */
    public function testVpsIpsSetsGetServiceMaster(): void
    {
        $this->assertStringContainsString('get_service_master = true', $this->contents);
    }

    /**
     * Tests that vps_ips loads with the correct addon text.
     */
    public function testVpsIpsLoadsWithCorrectText(): void
    {
        $this->assertStringContainsString("'Additional IP'", $this->contents);
    }

    /**
     * Tests that vps_ips loads with the vps module.
     */
    public function testVpsIpsLoadsWithVpsModule(): void
    {
        $this->assertMatchesRegularExpression("/load\s*\([^)]*'vps'/", $this->contents);
    }

    /**
     * Tests that vps_ips loads with ip type.
     */
    public function testVpsIpsLoadsWithIpType(): void
    {
        $this->assertMatchesRegularExpression("/load\s*\([^)]*'ip'\s*\)/", $this->contents);
    }

    /**
     * Tests that vps_ips binds the check_current event.
     */
    public function testVpsIpsBindsCheckCurrentEvent(): void
    {
        $this->assertStringContainsString("bind_event('vps_ips_check_current'", $this->contents);
    }

    /**
     * Tests that vps_ips binds to build_summary_header event.
     */
    public function testVpsIpsBindsToBuildSummaryHeader(): void
    {
        $this->assertStringContainsString("'build_summary_header'", $this->contents);
    }

    /**
     * Tests that vps_ips calls process().
     */
    public function testVpsIpsCallsProcess(): void
    {
        $this->assertStringContainsString('$addon->process()', $this->contents);
    }

    /**
     * Tests that vps_ips_check_current can return false.
     */
    public function testVpsIpsCheckCurrentCanReturnFalse(): void
    {
        $this->assertStringContainsString('return false;', $this->contents);
    }

    /**
     * Tests that vps_ips_check_current can return true.
     */
    public function testVpsIpsCheckCurrentCanReturnTrue(): void
    {
        $this->assertStringContainsString('return true;', $this->contents);
    }

    /**
     * Tests that vps_ips_check_current references VPS_MAX_IPS constant.
     */
    public function testVpsIpsCheckCurrentReferencesMaxIpsConstant(): void
    {
        $this->assertStringContainsString('VPS_MAX_IPS', $this->contents);
    }

    /**
     * Tests that vps_ips references VPS_IP_COST constant.
     */
    public function testVpsIpsReferencesIpCostConstant(): void
    {
        $this->assertStringContainsString('VPS_IP_COST', $this->contents);
    }

    /**
     * Tests that vps_ips_check_current queries invoices joined with repeat_invoices.
     */
    public function testVpsIpsCheckCurrentQueriesInvoices(): void
    {
        $this->assertStringContainsString('invoices left join repeat_invoices', $this->contents);
    }

    /**
     * Tests that the file uses proper PHP opening tag.
     */
    public function testFileHasProperPhpOpeningTag(): void
    {
        $this->assertStringStartsWith('<?php', $this->contents);
    }

    // ---------------------------------------------------------------
    //  vps_ips_check_current() behaviour
    //
    //  These replace an assertion that grepped the source for the string
    //  "ima == 'admin'". It proved nothing about whether the limit is actually
    //  enforced, and it broke as soon as the role lookup was legitimately migrated
    //  from $GLOBALS['tf']->ima to the \MyAdmin\App::ima() facade. The function is
    //  now called for real and asserted on by what it returns and warns.
    // ---------------------------------------------------------------

    /**
     * A customer at the per-VPS IP limit is refused and told to contact support.
     */
    public function testCheckCurrentRefusesClientAtTheIpLimit(): void
    {
        FrameworkSpy::reset();
        FrameworkSpy::$ima = 'client';
        $addon = $this->addon($this->invoiceRows(VPS_MAX_IPS));

        $this->assertFalse(vps_ips_check_current($addon));
        $this->assertCount(1, $addon->alerts);
        $this->assertStringContainsString('maximum number of IPs allowed', $addon->alerts[0]);
        $this->assertStringContainsString('contact support', $addon->alerts[0]);
    }

    /**
     * An admin ordering on a customer's behalf is allowed past the limit, but is
     * warned that the limit was exceeded.
     */
    public function testCheckCurrentLetsAdminPastTheIpLimitWithAWarning(): void
    {
        FrameworkSpy::reset();
        FrameworkSpy::$ima = 'admin';
        $addon = $this->addon($this->invoiceRows(VPS_MAX_IPS));

        $this->assertTrue(vps_ips_check_current($addon));
        $this->assertCount(1, $addon->alerts);
        $this->assertStringContainsString('allowing this because user is admin', $addon->alerts[0]);
    }

    /**
     * Below the limit the order proceeds with no warnings at all, and free IPs are
     * looked up on the server the VPS actually lives on.
     */
    public function testCheckCurrentAllowsOrderBelowTheIpLimit(): void
    {
        FrameworkSpy::reset();
        FrameworkSpy::$ima = 'client';
        $addon = $this->addon($this->invoiceRows(VPS_MAX_IPS - 1));

        $this->assertTrue(vps_ips_check_current($addon));
        $this->assertSame([], $addon->alerts);
        $this->assertSame([12], FrameworkSpy::$freeIpLookups);
    }

    /**
     * The availability check must stay side-effect free. It renders a summary header,
     * so it asks which IPs are free rather than claiming one through
     * vps_get_next_ip(), which rewrites addon invoice descriptions as it goes.
     */
    public function testCheckCurrentDoesNotAllocateAnIpToTestAvailability(): void
    {
        FrameworkSpy::reset();
        $addon = $this->addon($this->invoiceRows(VPS_MAX_IPS - 1));

        $this->assertTrue(vps_ips_check_current($addon));
        $this->assertSame([], FrameworkSpy::$nextIpLookups);
    }

    /**
     * A server with no free IP left cannot fill the order for anyone - not even an
     * admin - so it is refused before the per-VPS limit is even considered.
     */
    public function testCheckCurrentRefusesWhenServerHasNoFreeIp(): void
    {
        foreach (['client', 'admin'] as $role) {
            FrameworkSpy::reset();
            FrameworkSpy::$ima = $role;
            FrameworkSpy::$freeIps = [];
            $addon = $this->addon([]);

            $this->assertFalse(vps_ips_check_current($addon), "{$role} must be refused when the server is full");
            $this->assertCount(1, $addon->alerts);
            $this->assertStringContainsString('No available free ips on this server', $addon->alerts[0]);
        }
    }

    /**
     * The summary lists each additional IP the customer already has, with a cancel
     * link carrying that IP's repeat invoice id. An IP whose repeat invoice has not
     * been created yet is flagged as unpaid instead.
     */
    public function testCheckCurrentSummarisesExistingIps(): void
    {
        FrameworkSpy::reset();
        $addon = $this->addon([
            [
                'invoices_extra' => 10,
                'repeat_invoices_description' => 'Additional IP 192.0.2.5 for VPS 501',
                'invoices_description' => 'Additional IP 192.0.2.5 for VPS 501',
            ],
            [
                'invoices_extra' => 11,
                'repeat_invoices_description' => '',
                'invoices_description' => '',
            ],
        ]);

        $this->assertTrue(vps_ips_check_current($addon));
        $this->assertCount(2, FrameworkSpy::$output);
        $this->assertStringContainsString('192.0.2.5', FrameworkSpy::$output[0]);
        $this->assertStringContainsString('rid=10', FrameworkSpy::$output[0]);
        $this->assertStringContainsString('Cancel Additional IP', FrameworkSpy::$output[0]);
        $this->assertStringContainsString('Unpaid', FrameworkSpy::$output[1]);
        $this->assertStringContainsString('rid=11', FrameworkSpy::$output[1]);
    }

    /**
     * The customer's existing additional IPs are counted from their own paid
     * invoices for this VPS only.
     */
    public function testCheckCurrentCountsOnlyThisCustomersInvoicesForThisVps(): void
    {
        FrameworkSpy::reset();
        $addon = $this->addon([]);
        vps_ips_check_current($addon);

        $this->assertCount(1, $addon->db->queries);
        $sql = $addon->db->queries[0];
        $this->assertStringContainsString('invoices_custid=777', $sql);
        $this->assertStringContainsString('invoices_service=501', $sql);
        $this->assertStringContainsString("invoices_description like '%Additional IP%'", $sql);
    }

    /**
     * Invoice rows in the shape vps_ips_check_current() reads them, none of which
     * match the "Additional IP ... for VPS 501" description pattern.
     *
     * @return array<int, array<string, mixed>>
     */
    private function invoiceRows(int $count): array
    {
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'invoices_extra' => 100 + $i,
                'repeat_invoices_description' => '',
                'invoices_description' => '',
            ];
        }
        return $rows;
    }

    /**
     * The AddServiceAddon instance MyAdmin binds this callback to.
     *
     * @param array<int, array<string, mixed>> $rows existing additional-IP invoices
     * @return object
     */
    private function addon(array $rows)
    {
        return new class ($rows) {
            /** @var DbDouble */
            public $db;

            /** @var array<string, string> */
            public $settings = ['PREFIX' => 'vps', 'TBLNAME' => 'VPS'];

            /** @var array<string, mixed> */
            public $serviceInfo = ['vps_id' => 501, 'vps_custid' => 777, 'vps_server' => 12];

            /** @var string */
            public $module = 'vps';

            /** @var string */
            public $disable_link = 'choice=none.disable_addon&module={$module}&rid={$rid}';

            /** @var array<int, string> */
            public $alerts = [];

            /**
             * @param array<int, array<string, mixed>> $rows
             */
            public function __construct(array $rows)
            {
                $this->db = new DbDouble($rows);
            }

            /**
             * @param  string $message
             * @return void
             */
            public function alert($message)
            {
                $this->alerts[] = $message;
            }
        };
    }

    /**
     * Tests that the source file references function_requirements.
     */
    public function testSourceFileReferencesRequirements(): void
    {
        $this->assertStringContainsString("function_requirements('class.AddServiceAddon')", $this->contents);
    }

    /**
     * Tests that exactly two functions are defined in the file.
     */
    public function testFileDefinesTwoFunctions(): void
    {
        preg_match_all('/^function\s+\w+\s*\(/m', $this->contents, $matches);
        $this->assertCount(2, $matches[0]);
    }

    /**
     * Tests that the file contains a proper docblock header.
     */
    public function testFileHasDocblockHeader(): void
    {
        $this->assertStringContainsString('@author', $this->contents);
        $this->assertStringContainsString('@package', $this->contents);
    }
}
