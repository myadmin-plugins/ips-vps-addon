---
name: phpunit-plugin-tests
description: Writes PHPUnit 9 tests under tests/ in namespace Detain\MyAdminVpsIps\Tests\. Use when user says 'add test', 'write test', 'test coverage', or adds methods to src/Plugin.php or src/vps_ips.php. Covers ReflectionClass-based static property/method signature tests (PluginTest pattern) and file-content string-assertion tests (VpsIpsFunctionsTest pattern). Do NOT use for integration tests requiring a live MyAdmin instance or database.
---
# phpunit-plugin-tests

## Critical

- Namespace MUST be `Detain\MyAdminVpsIps\Tests\` — wrong namespace causes autoload miss and silent test skip.
- Never instantiate classes that depend on MyAdmin globals (`get_module_db`, `$GLOBALS['tf']`, etc.) — use `ReflectionClass` for structural assertions instead.
- Run tests with: `vendor/bin/phpunit tests/ -v`
- Every test method MUST have a docblock comment (`/** Tests that... */`).
- Files must start with `<?php` and include `@author` + `@package` docblock tags (enforced by the file docblock header test).

## Instructions

1. **Determine test type.** If testing `src/Plugin.php` class structure → use `ReflectionClass` pattern. If testing a procedural `src/*.php` file → use file-content string assertion pattern.

2. **Create the test file** at `tests/` with the appropriate class name. Boilerplate:
   ```php
   <?php
   namespace Detain\MyAdminVpsIps\Tests;
   use Detain\MyAdminVpsIps\Plugin;
   use PHPUnit\Framework\TestCase;
   use ReflectionClass;

   /**
    * Tests for the Plugin class.
    * @author Your Name
    * @package Detain\MyAdminVpsIps\Tests
    */
   class PluginTest extends TestCase
   {
       private $reflection;
       protected function setUp(): void
       {
           $this->reflection = new ReflectionClass(Plugin::class);
       }
   }
   ```
   Verify the file path exists before proceeding.

3. **Static property tests** (for Plugin class). Use `assertSame` for values; use `ReflectionProperty` for visibility:
   ```php
   public function testModuleProperty(): void
   {
       $this->assertSame('vps', Plugin::$module);
   }
   public function testModulePropertyIsPublicStatic(): void
   {
       $property = $this->reflection->getProperty('module');
       $this->assertTrue($property->isPublic());
       $this->assertTrue($property->isStatic());
   }
   ```

4. **Method signature tests** (for Plugin class). Check visibility, static, param names, type hints, and defaults:
   ```php
   public function testDoEnableMethodSignature(): void
   {
       $method = $this->reflection->getMethod('doEnable');
       $this->assertTrue($method->isPublic());
       $this->assertTrue($method->isStatic());
       $params = $method->getParameters();
       $this->assertCount(3, $params);
       $this->assertSame('regexMatch', $params[2]->getName());
       $this->assertTrue($params[2]->isDefaultValueAvailable());
       $this->assertFalse($params[2]->getDefaultValue());
   }
   public function testDoEnableFirstParamTypeHint(): void
   {
       $params = $this->reflection->getMethod('doEnable')->getParameters();
       $this->assertSame('ServiceHandler', $params[0]->getType()->getName());
   }
   ```

5. **Hooks tests** (for `getHooks()`). Assert keys follow `{$module}.event_name`, values are `[Plugin::class, 'methodName']`:
   ```php
   public function testGetHooksContainsExpectedKeys(): void
   {
       $hooks = Plugin::getHooks();
       $this->assertArrayHasKey('function.requirements', $hooks);
       $this->assertArrayHasKey(Plugin::$module . '.load_addons', $hooks);
   }
   public function testGetHooksValuesAreCallableArrays(): void
   {
       foreach (Plugin::getHooks() as $key => $value) {
           $this->assertIsArray($value);
           $this->assertSame(Plugin::class, $value[0]);
       }
   }
   ```

6. **File content tests** (for procedural `src/*.php`). Load file in `setUp()` then assert strings/regexes:
   ```php
   private $sourceFile;
   private $contents;
   protected function setUp(): void
   {
       $this->sourceFile = dirname(__DIR__) . '/src/vps_ips.php';
       $this->contents = file_get_contents($this->sourceFile);
   }
   public function testFunctionIsDefined(): void
   {
       $this->assertStringContainsString('function vps_ips(', $this->contents);
   }
   public function testFunctionSignatureRegex(): void
   {
       $this->assertMatchesRegularExpression('/function\s+vps_ips\s*\(\s*\)/', $this->contents);
   }
   ```

7. **Verify tests pass**: `vendor/bin/phpunit tests/ -v`. All added tests must show `OK`.

## Examples

**User says:** "Add tests for the new `getVersion()` static method on Plugin."

**Actions:**
1. Read `src/Plugin.php` to confirm `public static function getVersion(): string` exists.
2. Add to the Plugin test class:
   ```php
   public function testGetVersionMethodSignature(): void
   {
       $method = $this->reflection->getMethod('getVersion');
       $this->assertTrue($method->isPublic());
       $this->assertTrue($method->isStatic());
       $this->assertCount(0, $method->getParameters());
   }
   public function testGetVersionReturnsString(): void
   {
       $this->assertIsString(Plugin::getVersion());
   }
   ```
3. Update count assertion: `$this->assertCount(7, $methods);` (was 6).
4. Run `vendor/bin/phpunit tests/ -v`.

**Result:** Two new passing tests, existing count test updated to match.

## Common Issues

- **`Class 'Detain\MyAdminVpsIps\Plugin' not found`**: Run `composer dump-autoload` first; check namespace matches `src/Plugin.php` exactly.
- **`testClassHasExpectedNumberOfMethods` fails after adding a method**: Update `$this->assertCount(N, $methods)` in the Plugin test class to the new total.
- **`assertMatchesRegularExpression` not found**: Requires PHPUnit 9+. Check `composer.json` has `"phpunit/phpunit": "^9"`; use `assertRegExp` only as fallback for PHPUnit 8.
- **`ReflectionProperty::getType()` returns null on untyped property**: Static props in Plugin have no PHP type declaration — assert with `assertIsString(Plugin::$name)` not via reflection type.
- **File content test reads stale cache**: `setUp()` calls `file_get_contents` fresh each run — if assertions fail, verify the actual `src/` file was saved and not an editor buffer.
