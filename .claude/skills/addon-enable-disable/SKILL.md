---
name: addon-enable-disable
description: Implements doEnable/doDisable handlers on the Plugin class in src/Plugin.php for MyAdmin VPS addon plugins. Handles IP allocation via vps_get_next_ip(), {prefix}_ips table updates, Invoice/Repeat_Invoice ORM updates, and admin email on failure. Use when user says 'enable addon', 'disable addon', 'IP allocation', 'add enable handler', or 'changes activation logic'. Do NOT use for creating new addons from scratch or for non-VPS module addons.
---
# addon-enable-disable

## Critical

- **Never use PDO.** Always `get_module_db(self::$module)` — two handles (`$db`, `$db2`) are normal for nested queries.
- **Always validate IPs** with `validIp($ip)` before using in any query.
- **Always escape** user-supplied or external values with `$db->real_escape()` before interpolation; use `make_insert_query()` for inserts.
- **Log every state transition** with `myadmin_log(self::$module, 'info', $msg, __LINE__, __FILE__, self::$module, $id)`.
- **Always record history** with `$GLOBALS['tf']->history->add($table, $id, 'action', $value, $custid)` at each branch.
- **Send admin email on IP allocation failure** — never silently fail.

## Instructions

1. **Resolve service context** at the top of both methods:
   ```php
   $serviceInfo = $serviceOrder->getServiceInfo();
   $settings = get_module_settings(self::$module);   // gives PREFIX, TABLE, TBLNAME, TITLE
   $id = $serviceInfo[$settings['PREFIX'].'_id'];
   $db  = get_module_db(self::$module);
   $db2 = get_module_db(self::$module);              // second handle for sub-queries
   ```
   Verify `$settings['PREFIX']`, `$settings['TABLE']`, `$settings['TBLNAME']` are non-empty before proceeding.

2. **Log activation start** immediately:
   ```php
   myadmin_log(self::$module, 'info', self::$name.' Activation RegexMatch:'.var_export($regexMatch, true), __LINE__, __FILE__, self::$module, $id);
   ```

3. **Handle `$regexMatch` (pre-assigned IP) branch** — only when `$regexMatch !== false`:
   ```php
   $ip = $regexMatch;
   if (validIp($ip)) {
       $db2->query("select * from {$settings['PREFIX']}_ips where ips_ip='{$ip}' and ips_{$settings['PREFIX']} > 0 and ips_{$settings['PREFIX']} != {$id}", __LINE__, __FILE__);
       if ($db2->num_rows() == 0) {
           $db2->query("select * from {$settings['TABLE']} where {$settings['PREFIX']}_ip='{$ip}' and {$settings['PREFIX']}_status='active' and {$settings['PREFIX']}_id != '{$id}'", __LINE__, __FILE__);
           if ($db2->num_rows() == 0) {
               $needsIp = false;
               $GLOBALS['tf']->history->add(self::$module.'queue', $id, 'ensure_addon_ip', $ip, $serviceInfo[$settings['PREFIX'].'_custid']);
               $db->query("update {$settings['PREFIX']}_ips set ips_main=0,ips_used=1,ips_{$settings['PREFIX']}={$id} where ips_ip='{$ip}'", __LINE__, __FILE__);
           }
       }
       $db2->free();
   }
   ```

4. **Allocate a new IP** when `$needsIp === true`:
   ```php
   $ip = vps_get_next_ip($serviceInfo[$settings['PREFIX'].'_server']);
   myadmin_log(self::$module, 'info', 'Trying To Give '.$settings['TITLE'].' '.$id.' Repeat Invoice '.$repeatInvoiceId.' IP '.($ip === false ? '<ip allocation failed>' : $ip), __LINE__, __FILE__, self::$module, $id);
   ```

5. **On successful allocation** — update `{prefix}_ips`, then update ORM objects:
   ```php
   $GLOBALS['tf']->history->add(self::$module.'queue', $id, 'add_ip', $ip, $serviceInfo[$settings['PREFIX'].'_custid']);
   $description  = 'Additional IP '.$ip.' for '.$settings['TBLNAME'].' '.$id;
   $rdescription = '(Repeat Invoice: '.$repeatInvoiceId.') '.$description;
   $db->query("update {$settings['PREFIX']}_ips set ips_main=0,ips_used=1,ips_{$settings['PREFIX']}={$id} where ips_ip='{$ip}'", __LINE__, __FILE__);
   $invoiceObj = new \MyAdmin\Orm\Invoice();
   $invoices = $invoiceObj->find([['type','=',1],['extra','=',$repeatInvoiceId]]);
   foreach ($invoices as $invoiceId) {
       $invoiceObj->load_real($invoiceId);
       if ($invoiceObj->loaded === true) { $invoiceObj->setDescription($rdescription)->save(); }
   }
   $repeatInvoiceObj = new \MyAdmin\Orm\Repeat_Invoice();
   $repeatInvoiceObj->load_real($repeatInvoiceId);
   if ($repeatInvoiceObj->loaded === true) { $repeatInvoiceObj->setDescription($description)->save(); }
   ```

6. **On allocation failure** — query master record, send admin email, log history, and chat-notify:
   ```php
   $db->query('SELECT * FROM '.$settings['PREFIX'].'_masters WHERE '.$settings['PREFIX'].'_id='.$serviceInfo[$settings['PREFIX'].'_server'], __LINE__, __FILE__);
   $db->next_record(MYSQL_ASSOC);
   $subject = '0 free IPs on '.$settings['TBLNAME'].' server '.$db->Record[$settings['PREFIX'].'_name'].' while trying to activate '.$settings['TBLNAME'].' '.$id;
   (new \MyAdmin\Mail())->adminMail($subject, $settings['TBLNAME']." {$id} Has Pending IPS<br>\n".$subject, false, 'admin/vps_no_ips.tpl');
   $GLOBALS['tf']->history->add($settings['TABLE'], $id, 'allocate_ip_failed', '', $serviceInfo[$settings['PREFIX'].'_custid']);
   chatNotify($subject, 'int-dev');
   ```

7. **Implement `doDisable`** — log, record history (with IP from `$regexMatch` or `'None Assigned Yet'`), call `add_output()`, then send admin email:
   ```php
   myadmin_log(self::$module, 'info', self::$name.' Deactivation', __LINE__, __FILE__, self::$module, $serviceInfo[$settings['PREFIX'].'_id']);
   $ip = ($regexMatch !== false) ? $regexMatch : 'None Assigned Yet';
   if ($regexMatch !== false) {
       $GLOBALS['tf']->history->add(self::$module.'queue', $serviceInfo[$settings['PREFIX'].'_id'], 'remove_ip', $ip, $serviceInfo[$settings['PREFIX'].'_custid']);
   }
   add_output('IP Removed And Canceled');
   $email   = $settings['TBLNAME'].' ID: '.$serviceInfo[$settings['PREFIX'].'_id'].'<br>'.$settings['TBLNAME'].' Hostname: '.$serviceInfo[$settings['PREFIX'].'_hostname'].'<br>Repeat Invoice: '.$repeatInvoiceId.'<br>Description: '.self::$name.'<br>IP: '.$ip;
   $subject = $settings['TBLNAME'].' '.$serviceInfo[$settings['PREFIX'].'_id'].' Canceled IP '.$ip;
   (new \MyAdmin\Mail())->adminMail($subject, $email, false, 'admin/vps_ip_canceled.tpl');
   ```
   Verify the template path `admin/vps_ip_canceled.tpl` exists before sending.

## Examples

**User says:** "Add IP allocation enable/disable handlers for a new addon"

**Actions taken:**
1. Copy the `doEnable`/`doDisable` signatures from `Plugin.php:78` and `Plugin.php:143`.
2. Resolve `$serviceInfo`, `$settings`, `$id`, `$db`, `$db2` at top of `doEnable`.
3. Implement regex-match branch (steps 3), then `vps_get_next_ip` branch (steps 4–6).
4. Implement `doDisable` with history, `add_output`, and `adminMail` (step 7).

**Result:** Methods matching `src/Plugin.php:78–159` exactly, with correct ORM updates and failure email.

## Common Issues

- **`validIp()` not found:** Call `function_requirements('validIp')` or ensure `include/validations.php` is loaded before the method runs.
- **`$invoiceObj->loaded` is `false` after `load_real()`:** The repeat invoice ID doesn't exist yet; confirm `$repeatInvoiceId` is a persisted DB row, not a transient value.
- **`vps_get_next_ip()` returns `false` unexpectedly:** The server has no free IPs in `{prefix}_ips`. Check `ips_used=0` rows for that server ID — do NOT skip the admin email path.
- **Admin email not delivered:** Verify the template argument matches an existing file under `include/templates/email/admin/` (e.g., `admin/vps_no_ips.tpl`, `admin/vps_ip_canceled.tpl`). Wrong path silently fails.
- **History table mismatch:** `doEnable` uses `self::$module.'queue'` (e.g., `vpsqueue`) as the table arg; `doDisable` failure path uses `$settings['TABLE']`. Do not swap them.