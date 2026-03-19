<?php

namespace Detain\MyAdminVpsIps\Tests;

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

    /**
     * Tests that vps_ips_check_current checks admin access for exceeding max IPs.
     */
    public function testVpsIpsCheckCurrentChecksAdminAccess(): void
    {
        $this->assertStringContainsString("ima == 'admin'", $this->contents);
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
