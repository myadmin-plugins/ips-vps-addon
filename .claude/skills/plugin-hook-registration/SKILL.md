---
name: plugin-hook-registration
description: Creates or modifies the Plugin class hook registration pattern in src/Plugin.php. Implements getHooks() returning function.requirements, {module}.load_addons, {module}.settings entries pointing to static callbacks. Use when user says 'add a hook', 'register event', 'new plugin method', or modifies getHooks(). Do NOT use for modifying src/vps_ips.php procedural functions or AddonHandler configuration inside getAddon().
---
# Plugin Hook Registration

## Critical

- All methods in `Plugin` MUST be `public static` — the event dispatcher calls them statically via `[__CLASS__, 'methodName']` arrays
- Hook keys for `load_addons` and `settings` MUST be built dynamically: `self::$module.'.load_addons'` — never hardcode the module name in the key
- Every method referenced in `getHooks()` MUST actually exist on the class — `PluginTest::testAllHookCallbackMethodsExist()` enforces this
- `getRequirements`, `getAddon`, and `getSettings` take `GenericEvent $event` as their sole parameter — `doEnable`/`doDisable` take `(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)`
- `PluginTest` asserts exactly **6 methods** and **5 static properties** — adding or removing either requires updating the count assertion in `tests/PluginTest.php`

## Instructions

1. **Declare the namespace and import** at the top of `src/Plugin.php`:
   ```php
   namespace Detain\MyAdminVpsIps;
   use Symfony\Component\EventDispatcher\GenericEvent;
   ```
   Verify the namespace matches the `autoload.psr-4` entry in `composer.json` before proceeding.

2. **Define static class properties** — all five must be present and public:
   ```php
   public static $name = 'Additional IPs VPS Addon';
   public static $description = 'Allows selling of additional IP Addresses as a VPS Addon.';
   public static $help = '';
   public static $module = 'vps';
   public static $type = 'addon';
   ```

3. **Implement `getHooks()`** — return exactly 3 entries keyed as shown:
   ```php
   public static function getHooks()
   {
       return [
           'function.requirements'        => [__CLASS__, 'getRequirements'],
           self::$module.'.load_addons'   => [__CLASS__, 'getAddon'],
           self::$module.'.settings'      => [__CLASS__, 'getSettings'],
       ];
   }
   ```
   Verify each value's `[1]` element is the exact method name of a method that exists on this class.

4. **Add a new hook entry** (when requested): add both the key→callback pair in `getHooks()` AND a corresponding `public static function` with the right signature:
   - Event-dispatched hooks (requirements, addon loading, settings): `public static function myMethod(GenericEvent $event)`
   - Enable/disable lifecycle hooks: `public static function doSomething(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)`

5. **Register a page requirement** inside `getRequirements(GenericEvent $event)`:
   ```php
   $loader = $event->getSubject(); // \MyAdmin\Plugins\Loader
   $loader->add_page_requirement('function_name', $filePath);
   ```

6. **Register module settings** inside `getSettings(GenericEvent $event)`:
   ```php
   $settings = $event->getSubject(); // \MyAdmin\Settings
   $settings->setTarget('module');
   $settings->add_text_setting(self::$module, _('Group'), 'setting_key', _('Label'), _('Description'), $settings->get_setting('SETTING_CONST'));
   $settings->setTarget('global'); // always reset to global at the end
   ```

7. **Run tests** to validate:
   ```bash
   vendor/bin/phpunit tests/PluginTest.php -v
   ```
   All assertions must pass, especially `testGetHooksContainsExpectedKeys`, `testGetHooksValuesAreCallableArrays`, and `testAllHookCallbackMethodsExist`.

## Examples

**User says:** "Add a hook for vps.refresh that calls a doRefresh method"

**Actions taken:**
1. Add entry to `getHooks()`:
   ```php
   self::$module.'.refresh' => [__CLASS__, 'doRefresh'],
   ```
2. Add the method (uses `GenericEvent` signature since it's event-dispatched):
   ```php
   public static function doRefresh(GenericEvent $event)
   {
       $service = $event->getSubject();
       $settings = get_module_settings(self::$module);
       myadmin_log(self::$module, 'info', self::$name.' Refresh', __LINE__, __FILE__, self::$module, 0);
   }
   ```
3. Update `testClassHasExpectedNumberOfMethods` in `tests/PluginTest.php` from `assertCount(6, ...)` to `assertCount(7, ...)`
4. Run `vendor/bin/phpunit tests/PluginTest.php -v`

**Result:** `getHooks()` now returns 4 entries; all PluginTest assertions pass.

## Common Issues

- **`testGetHooksValuesAreCallableArrays` fails with "Hook 'vps.refresh' should reference Plugin class":** The callback `[0]` element must be `__CLASS__` or the fully-qualified `Plugin::class` string — not a string like `'Plugin'`.
- **`testAllHookCallbackMethodsExist` fails:** The method name in the `getHooks()` array does not match the actual method defined on the class. Check for typos (`doRefesh` vs `doRefresh`).
- **`testClassHasExpectedNumberOfMethods` fails with count mismatch:** Every new `public static function` added to `Plugin` must be reflected by updating the `assertCount(N, ...)` in `tests/PluginTest.php:364`.
- **`testGetSettingsParameterTypeHint` fails:** The `getSettings` (or any event hook) method parameter must be `GenericEvent $event`, not an untyped `$event`. The `use Symfony\Component\EventDispatcher\GenericEvent;` import must be present.
- **Settings not appearing in admin UI:** Verify `$settings->setTarget('module')` is called before `add_text_setting()` and `$settings->setTarget('global')` is called after — omitting the reset leaves global settings broken.
