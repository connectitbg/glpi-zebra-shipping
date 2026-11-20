# GLPI Zebra Shipping 0.2.8 uninstall test

**Date:** 2026-11-20  
**Environment:** isolated GLPI 11.0.9 test clone  
**Scope:** Verify that GLPI uninstall removes all plugin-owned persisted data, including the Professional license setting.

## Procedure and results

1. Replaced the test instance's plugin `hook.php` and `setup.php` with the 0.2.8 build. Both passed `php -l`; GLPI detected 0.2.8 as an update.
2. Before uninstall, confirmed the test clone had one `plugin:zebrashipping / license_file` configuration row, 20 rows in the plugin jobs table, and the installation state file.
3. Ran GLPI's `plugin:uninstall zebrashipping` command as the web server user.
4. Verified the jobs table was absent, the license setting count was zero, and `installation.json` was removed.
5. Installed and activated the plugin again. GLPI reports 0.2.8 as enabled; installation recreated the plugin's jobs table and installation state. The license setting remains absent after reinstall.
6. Ran `php -l` against every plugin PHP file on the test host; all passed.

Uninstall intentionally removed the 20 print-job rows from the test clone. No production system was changed, and no physical print was part of this uninstall-only test.
