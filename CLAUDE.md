# MyAdmin VPS IP Address Addon

Composer plugin package that sells additional IP addresses as a VPS addon in the MyAdmin billing system. Type `myadmin-plugin`.

## Commands

```bash
composer install
vendor/bin/phpunit tests/ -v
vendor/bin/phpunit tests/ -v --coverage-clover coverage.xml --whitelist src/
```

```bash
# Static analysis and autoload
composer dump-autoload
make php-cs-fixer
```

```bash
# Commit workflow
caliber refresh && git add CLAUDE.md .claude/
git commit -m "descriptive message"
```

## Architecture

**Namespace**: `Detain\MyAdminVpsIps\` → `src/` · tests: `Detain\MyAdminVpsIps\Tests\` → `tests/`

**CI/Deployment**: `.github/` contains automated testing and deployment workflows. `.idea/` stores IDE configuration including `inspectionProfiles/`, `deployment.xml`, and `encodings.xml`.

**`src/Plugin.php`** — static `Plugin` class, Symfony `GenericEvent` integration:
- `getHooks()` → registers `function.requirements`, `vps.load_addons`, `vps.settings`
- `getRequirements(GenericEvent)` → adds page requirement for `vps_ips` from `src/vps_ips.php`
- `getAddon(GenericEvent)` → builds `AddonHandler` with `VPS_IP_COST`, `set_require_ip(true)`
- `doEnable(\ServiceHandler, $repeatInvoiceId, $regexMatch=false)` → allocates IP via `vps_get_next_ip()`, updates `{prefix}_ips` table, updates `\MyAdmin\Orm\Invoice` and `\MyAdmin\Orm\Repeat_Invoice`
- `doDisable(\ServiceHandler, $repeatInvoiceId, $regexMatch=false)` → releases IP, sends admin email via `admin/vps_ip_canceled.tpl`
- `getSettings(GenericEvent)` → registers `vps_ip_cost` and `vps_max_ips` module settings

**`src/vps_ips.php`** — procedural helpers (loaded via `function_requirements`):
- `vps_ips()` → creates `AddServiceAddon`, sets `allow_multiple=true`, binds `vps_ips_check_current` to `build_summary_header`
- `vps_ips_check_current($addon)` → queries `invoices left join repeat_invoices`, checks `VPS_MAX_IPS`, returns bool

**`tests/`** — PHPUnit 9, config `phpunit.xml.dist`:
- `FileExistenceTest.php` — validates `composer.json` structure, `src/` files, `phpunit.xml.dist`
- `PluginTest.php` — validates static props, `getHooks()` keys/values, method signatures via `ReflectionClass`
- `VpsIpsFunctionsTest.php` — validates function definitions and patterns in `src/vps_ips.php`

## Conventions

- Static plugin props: `$name`, `$description`, `$help`, `$module = 'vps'`, `$type = 'addon'`
- Hook keys follow pattern `{$module}.load_addons`, `{$module}.settings`, `function.requirements`
- DB: never PDO — `get_module_db($module)`, `$db->real_escape()` on user input, `$db->query($sql, __LINE__, __FILE__)`
- Logging: `myadmin_log(self::$module, 'info', $msg, __LINE__, __FILE__, self::$module, $id)`
- IP validation: always `validIp($ip)` before using IP in queries
- History: `$GLOBALS['tf']->history->add($table, $id, 'action', $ip, $custid)`
- i18n: wrap all user-visible strings in `_('...')` for gettext
- Settings: `$settings->add_text_setting($module, _('Group'), 'key', _('Label'), _('Desc'), $current)`
- Admin email on failure: `(new \MyAdmin\Mail())->adminMail($subject, $body, false, 'admin/vps_no_ips.tpl')`
- File docblocks: include `@author` and `@package` tags (enforced by `VpsIpsFunctionsTest`)
- Commit messages: lowercase, descriptive (`fix in ip addon code`, `updates to addon ips activation logic`)
- Run `caliber refresh` before commits; stage `CLAUDE.md .claude/` after

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
