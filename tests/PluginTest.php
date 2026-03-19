<?php

namespace Detain\MyAdminVpsIps\Tests;

use Detain\MyAdminVpsIps\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for the Plugin class.
 */
class PluginTest extends TestCase
{
    /**
     * @var ReflectionClass
     */
    private $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    /**
     * Tests that the Plugin class can be instantiated.
     */
    public function testCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Tests that the $name static property has the expected value.
     */
    public function testNameProperty(): void
    {
        $this->assertSame('Additional IPs VPS Addon', Plugin::$name);
    }

    /**
     * Tests that the $description static property has the expected value.
     */
    public function testDescriptionProperty(): void
    {
        $this->assertSame(
            'Allows selling of additional IP Addresses as a VPS Addon.',
            Plugin::$description
        );
    }

    /**
     * Tests that the $help static property is an empty string.
     */
    public function testHelpPropertyIsEmptyString(): void
    {
        $this->assertSame('', Plugin::$help);
    }

    /**
     * Tests that the $module static property is set to 'vps'.
     */
    public function testModuleProperty(): void
    {
        $this->assertSame('vps', Plugin::$module);
    }

    /**
     * Tests that the $type static property is set to 'addon'.
     */
    public function testTypeProperty(): void
    {
        $this->assertSame('addon', Plugin::$type);
    }

    /**
     * Tests that the Plugin class has a public static $name property.
     */
    public function testNamePropertyIsPublicStatic(): void
    {
        $property = $this->reflection->getProperty('name');
        $this->assertTrue($property->isPublic());
        $this->assertTrue($property->isStatic());
    }

    /**
     * Tests that the Plugin class has a public static $description property.
     */
    public function testDescriptionPropertyIsPublicStatic(): void
    {
        $property = $this->reflection->getProperty('description');
        $this->assertTrue($property->isPublic());
        $this->assertTrue($property->isStatic());
    }

    /**
     * Tests that the Plugin class has a public static $help property.
     */
    public function testHelpPropertyIsPublicStatic(): void
    {
        $property = $this->reflection->getProperty('help');
        $this->assertTrue($property->isPublic());
        $this->assertTrue($property->isStatic());
    }

    /**
     * Tests that the Plugin class has a public static $module property.
     */
    public function testModulePropertyIsPublicStatic(): void
    {
        $property = $this->reflection->getProperty('module');
        $this->assertTrue($property->isPublic());
        $this->assertTrue($property->isStatic());
    }

    /**
     * Tests that the Plugin class has a public static $type property.
     */
    public function testTypePropertyIsPublicStatic(): void
    {
        $property = $this->reflection->getProperty('type');
        $this->assertTrue($property->isPublic());
        $this->assertTrue($property->isStatic());
    }

    /**
     * Tests that the constructor has no required parameters.
     */
    public function testConstructorHasNoRequiredParameters(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    /**
     * Tests that getHooks returns an array.
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Tests that getHooks contains the expected hook keys.
     */
    public function testGetHooksContainsExpectedKeys(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertArrayHasKey('function.requirements', $hooks);
        $this->assertArrayHasKey('vps.load_addons', $hooks);
        $this->assertArrayHasKey('vps.settings', $hooks);
    }

    /**
     * Tests that getHooks returns exactly 3 hooks.
     */
    public function testGetHooksReturnsExactlyThreeHooks(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertCount(3, $hooks);
    }

    /**
     * Tests that getHooks values are callable arrays referencing the Plugin class.
     */
    public function testGetHooksValuesAreCallableArrays(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $key => $value) {
            $this->assertIsArray($value, "Hook '{$key}' should be an array");
            $this->assertCount(2, $value, "Hook '{$key}' should have 2 elements");
            $this->assertSame(Plugin::class, $value[0], "Hook '{$key}' should reference Plugin class");
            $this->assertIsString($value[1], "Hook '{$key}' method name should be a string");
        }
    }

    /**
     * Tests that the function.requirements hook points to getRequirements.
     */
    public function testRequirementsHookPointsToGetRequirements(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getRequirements'], $hooks['function.requirements']);
    }

    /**
     * Tests that the vps.load_addons hook points to getAddon.
     */
    public function testLoadAddonsHookPointsToGetAddon(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getAddon'], $hooks['vps.load_addons']);
    }

    /**
     * Tests that the vps.settings hook points to getSettings.
     */
    public function testSettingsHookPointsToGetSettings(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertSame([Plugin::class, 'getSettings'], $hooks['vps.settings']);
    }

    /**
     * Tests that the load_addons hook key is dynamically built from the module property.
     */
    public function testLoadAddonsHookKeyUsesModuleProperty(): void
    {
        $hooks = Plugin::getHooks();
        $expectedKey = Plugin::$module . '.load_addons';
        $this->assertArrayHasKey($expectedKey, $hooks);
    }

    /**
     * Tests that the settings hook key is dynamically built from the module property.
     */
    public function testSettingsHookKeyUsesModuleProperty(): void
    {
        $hooks = Plugin::getHooks();
        $expectedKey = Plugin::$module . '.settings';
        $this->assertArrayHasKey($expectedKey, $hooks);
    }

    /**
     * Tests that getRequirements method exists and is public static.
     */
    public function testGetRequirementsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Tests that getRequirements parameter is type-hinted to GenericEvent.
     */
    public function testGetRequirementsParameterTypeHint(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $params = $method->getParameters();
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame('Symfony\Component\EventDispatcher\GenericEvent', $type->getName());
    }

    /**
     * Tests that getAddon method exists and is public static.
     */
    public function testGetAddonMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getAddon');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Tests that getAddon parameter is type-hinted to GenericEvent.
     */
    public function testGetAddonParameterTypeHint(): void
    {
        $method = $this->reflection->getMethod('getAddon');
        $params = $method->getParameters();
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame('Symfony\Component\EventDispatcher\GenericEvent', $type->getName());
    }

    /**
     * Tests that doEnable method exists and is public static.
     */
    public function testDoEnableMethodSignature(): void
    {
        $method = $this->reflection->getMethod('doEnable');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $params = $method->getParameters();
        $this->assertCount(3, $params);
        $this->assertSame('serviceOrder', $params[0]->getName());
        $this->assertSame('repeatInvoiceId', $params[1]->getName());
        $this->assertSame('regexMatch', $params[2]->getName());
    }

    /**
     * Tests that doEnable third parameter has a default value of false.
     */
    public function testDoEnableRegexMatchDefaultValue(): void
    {
        $method = $this->reflection->getMethod('doEnable');
        $params = $method->getParameters();
        $this->assertTrue($params[2]->isDefaultValueAvailable());
        $this->assertFalse($params[2]->getDefaultValue());
    }

    /**
     * Tests that doDisable method exists and is public static.
     */
    public function testDoDisableMethodSignature(): void
    {
        $method = $this->reflection->getMethod('doDisable');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $params = $method->getParameters();
        $this->assertCount(3, $params);
        $this->assertSame('serviceOrder', $params[0]->getName());
        $this->assertSame('repeatInvoiceId', $params[1]->getName());
        $this->assertSame('regexMatch', $params[2]->getName());
    }

    /**
     * Tests that doDisable third parameter has a default value of false.
     */
    public function testDoDisableRegexMatchDefaultValue(): void
    {
        $method = $this->reflection->getMethod('doDisable');
        $params = $method->getParameters();
        $this->assertTrue($params[2]->isDefaultValueAvailable());
        $this->assertFalse($params[2]->getDefaultValue());
    }

    /**
     * Tests that getSettings method exists and is public static.
     */
    public function testGetSettingsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
    }

    /**
     * Tests that getSettings parameter is type-hinted to GenericEvent.
     */
    public function testGetSettingsParameterTypeHint(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $params = $method->getParameters();
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame('Symfony\Component\EventDispatcher\GenericEvent', $type->getName());
    }

    /**
     * Tests that the Plugin class has exactly 5 static properties.
     */
    public function testClassHasExpectedNumberOfStaticProperties(): void
    {
        $properties = $this->reflection->getProperties(\ReflectionProperty::IS_STATIC);
        $this->assertCount(5, $properties);
    }

    /**
     * Tests that the Plugin class has exactly 6 methods.
     */
    public function testClassHasExpectedNumberOfMethods(): void
    {
        $methods = $this->reflection->getMethods();
        $this->assertCount(6, $methods);
    }

    /**
     * Tests that all hook callback methods actually exist on the Plugin class.
     */
    public function testAllHookCallbackMethodsExist(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $hookName => $callback) {
            $this->assertTrue(
                $this->reflection->hasMethod($callback[1]),
                "Method '{$callback[1]}' referenced by hook '{$hookName}' does not exist"
            );
        }
    }

    /**
     * Tests that doEnable first parameter is type-hinted to ServiceHandler.
     */
    public function testDoEnableFirstParamTypeHint(): void
    {
        $method = $this->reflection->getMethod('doEnable');
        $params = $method->getParameters();
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame('ServiceHandler', $type->getName());
    }

    /**
     * Tests that doDisable first parameter is type-hinted to ServiceHandler.
     */
    public function testDoDisableFirstParamTypeHint(): void
    {
        $method = $this->reflection->getMethod('doDisable');
        $params = $method->getParameters();
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame('ServiceHandler', $type->getName());
    }

    /**
     * Tests that the Plugin class is in the expected namespace.
     */
    public function testClassNamespace(): void
    {
        $this->assertSame('Detain\MyAdminVpsIps', $this->reflection->getNamespaceName());
    }

    /**
     * Tests that the Plugin class is not abstract.
     */
    public function testClassIsNotAbstract(): void
    {
        $this->assertFalse($this->reflection->isAbstract());
    }

    /**
     * Tests that the Plugin class is not final.
     */
    public function testClassIsNotFinal(): void
    {
        $this->assertFalse($this->reflection->isFinal());
    }

    /**
     * Tests that the Plugin class does not implement any interfaces.
     */
    public function testClassImplementsNoInterfaces(): void
    {
        $this->assertCount(0, $this->reflection->getInterfaces());
    }

    /**
     * Tests that the Plugin class has no parent class.
     */
    public function testClassHasNoParent(): void
    {
        $this->assertFalse($this->reflection->getParentClass());
    }

    /**
     * Tests that all static properties are string type.
     */
    public function testAllStaticPropertiesAreStrings(): void
    {
        $this->assertIsString(Plugin::$name);
        $this->assertIsString(Plugin::$description);
        $this->assertIsString(Plugin::$help);
        $this->assertIsString(Plugin::$module);
        $this->assertIsString(Plugin::$type);
    }

    /**
     * Tests that doEnable second parameter has no type hint (mixed).
     */
    public function testDoEnableSecondParamHasNoTypeHint(): void
    {
        $method = $this->reflection->getMethod('doEnable');
        $params = $method->getParameters();
        $this->assertNull($params[1]->getType());
    }

    /**
     * Tests that doDisable second parameter has no type hint (mixed).
     */
    public function testDoDisableSecondParamHasNoTypeHint(): void
    {
        $method = $this->reflection->getMethod('doDisable');
        $params = $method->getParameters();
        $this->assertNull($params[1]->getType());
    }
}
