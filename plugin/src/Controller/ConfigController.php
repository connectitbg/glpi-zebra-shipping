<?php

namespace GlpiPlugin\Zebrashipping\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Zebrashipping\InstallationState;
use GlpiPlugin\Zebrashipping\LicenseStorage;
use GlpiPlugin\Zebrashipping\ServiceClient;
use GlpiPlugin\Zebrashipping\ZplTemplate;
use Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConfigController extends AbstractController
{
    #[Route('/Config', name: 'zebrashipping_config', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        Session::checkRight('config', UPDATE);

        $messages = [];
        $license = LicenseStorage::get();
        $zplTemplate = ZplTemplate::get();
        $installState = InstallationState::get();
        $service = null;

        if ($request->isMethod('GET') && $request->query->getBoolean('download_recovery')) {
            $payload = self::recoveryPayload($installState, $license, $zplTemplate);
            $response = new Response(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="zebrashipping-recovery.json"');
            $response->headers->set('Cache-Control', 'no-store, private');
            return $response;
        }

        try {
            $service = new ServiceClient();
            if ($request->isMethod('POST')) {
                if ($request->request->has('save_license')) {
                    $submittedLicense = trim($request->request->getString('license_file'));
                    $license = $submittedLicense === '' ? $license : $submittedLicense;
                    if (mb_strlen($license) > 8192) {
                        throw new \InvalidArgumentException(__('License text is too long.', 'zebrashipping'));
                    }
                    if ($license !== '') {
                        $licenseStatus = $service->licenseStatus($installState, $license);
                        if (empty($licenseStatus['valid'])) {
                            throw new \RuntimeException(__('The supplied license could not be validated.', 'zebrashipping'));
                        }
                    }
                    LicenseStorage::store($license);
                    $messages[] = __('License saved.', 'zebrashipping');
                } elseif ($request->request->has('save_zpl_template')) {
                    $status = $service->licenseStatus($installState, $license ?: null);
                    if (empty($status['valid']) || ($status['edition'] ?? '') !== 'professional') {
                        throw new \RuntimeException(__('Custom ZPL templates are available in the Professional edition.', 'zebrashipping'));
                    }
                    $zplTemplate = ZplTemplate::validate($request->request->getString('zpl_template'));
                    ZplTemplate::store($zplTemplate);
                    $messages[] = $zplTemplate === ''
                        ? __('Custom ZPL template cleared. The built-in label layout will be used.', 'zebrashipping')
                        : __('Custom ZPL template saved.', 'zebrashipping');
                } elseif ($request->request->has('clear_license')) {
                    LicenseStorage::store('');
                    ZplTemplate::store('');
                    $license = '';
                    $zplTemplate = '';
                    $messages[] = __('License removed.', 'zebrashipping');
                } elseif ($request->request->has('restore_recovery')) {
                    $rawBackup = trim($request->request->getString('recovery_backup'));
                    if ($rawBackup === '' || strlen($rawBackup) > 65536) {
                        throw new \InvalidArgumentException(__('Recovery backup is empty or too large.', 'zebrashipping'));
                    }
                    $backup = json_decode($rawBackup, true, 16, JSON_THROW_ON_ERROR);
                    if (!is_array($backup)
                        || ($backup['product'] ?? null) !== 'glpi-zebra-shipping'
                        || ($backup['format_version'] ?? null) !== 1
                        || !is_array($backup['installation'] ?? null)
                        || !is_string($backup['license_file'] ?? null)
                        || mb_strlen($backup['license_file']) > 8192
                        || !is_string($backup['zpl_template'] ?? '')
                        || strlen($backup['zpl_template'] ?? '') > ZplTemplate::MAX_LENGTH) {
                        throw new \InvalidArgumentException(__('This is not a supported Zebra Shipping recovery backup.', 'zebrashipping'));
                    }
                    $restoredState = InstallationState::validate($backup['installation']);
                    $restoredLicense = trim($backup['license_file']);
                    $restoredStatus = $service->licenseStatus($restoredState, $restoredLicense ?: null);
                    if (empty($restoredStatus['valid'])) {
                        throw new \RuntimeException(__('The recovery backup license or demo period is not valid.', 'zebrashipping'));
                    }
                    $restoredTemplate = ZplTemplate::validate($backup['zpl_template'] ?? '');
                    if ($restoredTemplate !== '' && ($restoredStatus['edition'] ?? '') !== 'professional') {
                        throw new \RuntimeException(__('A Professional license is required to restore a custom ZPL template.', 'zebrashipping'));
                    }
                    InstallationState::restore($restoredState);
                    LicenseStorage::store($restoredLicense);
                    ZplTemplate::store($restoredTemplate);
                    $installState = $restoredState;
                    $license = $restoredLicense;
                    $messages[] = __('Recovery backup restored. Save a new backup after changing your license.', 'zebrashipping');
                } elseif ($request->request->has('check_printer')) {
                    $result = $service->checkPrinter($request->request->getString('printer_id'));
                    $messages[] = !empty($result['reachable'])
                        ? __('Printer is reachable.', 'zebrashipping')
                        : __('Printer is not reachable. Check its address, port, and network route.', 'zebrashipping');
                } elseif ($request->request->has('save_printer') || $request->request->has('delete_printer')) {
                    $printers = $service->printers();
                    $target = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->request->getString('printer_id'));
                    if ($request->request->has('delete_printer')) {
                        $printers = array_values(array_filter($printers, static fn ($printer) => $printer['id'] !== $target));
                    } else {
                        $name = trim($request->request->getString('name'));
                        $host = trim($request->request->getString('host'));
                        if ($name === '' || $host === '') {
                            throw new \RuntimeException(__('Printer name and address are required.', 'zebrashipping'));
                        }
                        if ($target === '') {
                            $target = 'printer-' . bin2hex(random_bytes(6));
                        }
                        $entry = [
                            'id' => $target,
                            'name' => mb_substr($name, 0, 100),
                            'host' => mb_substr($host, 0, 253),
                            'port' => max(1, min(65535, $request->request->getInt('port', 9100))),
                            'dpi' => max(152, min(600, $request->request->getInt('dpi', 203))),
                            'width_mm' => self::requestInt($request, 'width_mm', 93, 20, 300),
                            'height_mm' => self::requestInt($request, 'height_mm', 71, 20, 300),
                            'enabled' => $request->request->has('enabled'),
                            'description' => mb_substr(trim($request->request->getString('description')), 0, 250),
                        ];
                        $found = false;
                        foreach ($printers as &$printer) {
                            if ($printer['id'] === $target) {
                                $printer = $entry;
                                $found = true;
                            }
                        }
                        unset($printer);
                        if (!$found) {
                            $printers[] = $entry;
                        }
                    }
                    $service->savePrinters($printers, $installState, $license ?: null);
                    $messages[] = __('Printer settings saved.', 'zebrashipping');
                }
            }

            $licenseStatus = $service->licenseStatus($installState, $license ?: null);
            $zplTemplate = ZplTemplate::get();
            $printers = $service->printers();
        } catch (\Throwable $error) {
            $messages[] = $error->getMessage();
            $licenseStatus = null;
            $printers = [];
        }

        global $CFG_GLPI;
        $configUrl = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/plugins/zebrashipping/Config';
        ob_start();
        \Html::header(__('GLPI Zebra Shipping', 'zebrashipping'), $configUrl, 'config', 'plugins');

        echo '<div class="container-fluid"><h2>' . self::escape(__('GLPI Zebra Shipping settings', 'zebrashipping')) . '</h2>';
        foreach ($messages as $message) {
            echo '<div class="alert alert-info">' . self::escape($message) . '</div>';
        }
        if ($licenseStatus) {
            echo '<p><strong>' . self::escape(__('Edition', 'zebrashipping')) . ':</strong> '
                . self::escape((string) ($licenseStatus['edition'] ?? 'unknown'));
            if (isset($licenseStatus['expires_at'])) {
                echo ' · ' . self::escape(__('Demo expires', 'zebrashipping')) . ': '
                    . self::escape((string) $licenseStatus['expires_at']);
            }
            echo '</p>';
        }

        echo '<h3>' . self::escape(__('Printer setup', 'zebrashipping')) . '</h3>';
        echo '<p>' . self::escape(__('Printer addresses are stored by the local Print Service. Add or edit printer settings below.', 'zebrashipping')) . '</p>';
        foreach ($printers as $printer) {
            echo '<form method="post" action="' . self::escape($configUrl) . '" class="card card-body mb-3">';
            self::csrfField();
            echo '<input type="hidden" name="printer_id" value="' . self::escape((string) $printer['id']) . '">';
            echo '<div class="row g-2">';
            self::printerFields($printer);
            echo '<div class="col-md-3 form-check"><input class="form-check-input" type="checkbox" name="enabled"'
                . (!empty($printer['enabled']) ? ' checked' : '') . '><label class="form-check-label">'
                . self::escape(__('Enabled', 'zebrashipping')) . '</label></div></div>';
            echo '<button class="btn btn-primary mt-2" name="save_printer" value="1">' . self::escape(__('Save printer', 'zebrashipping')) . '</button> '
                . '<button class="btn btn-outline-secondary mt-2" name="check_printer" value="1">' . self::escape(__('Test connection', 'zebrashipping')) . '</button> '
                . '<button class="btn btn-outline-danger mt-2" name="delete_printer" value="1">' . self::escape(__('Remove', 'zebrashipping')) . '</button></form>';
        }

        echo '<form method="post" action="' . self::escape($configUrl) . '" class="card card-body mb-4">';
        self::csrfField();
        echo '<h4>' . self::escape(__('Add printer', 'zebrashipping')) . '</h4>';
        self::printerFields(['port' => '9100', 'dpi' => '203', 'width_mm' => '93', 'height_mm' => '71']);
        echo '<div class="form-check"><input class="form-check-input" type="checkbox" name="enabled" checked><label class="form-check-label">'
            . self::escape(__('Enabled', 'zebrashipping')) . '</label></div>';
        echo '<button class="btn btn-primary mt-2" name="save_printer" value="1">' . self::escape(__('Add printer', 'zebrashipping')) . '</button></form>';

        echo '<h3>' . self::escape(__('Professional license', 'zebrashipping')) . '</h3>';
        echo '<form method="post" action="' . self::escape($configUrl) . '">';
        self::csrfField();
        echo '<input class="form-control" type="password" name="license_file" maxlength="8192" autocomplete="new-password" spellcheck="false" placeholder="'
            . self::escape($license !== '' ? __('License is saved; leave blank to keep it.', 'zebrashipping') : __('Paste license key', 'zebrashipping'))
            . '"><button class="btn btn-primary mt-2" name="save_license" value="1">'
            . self::escape(__('Save license', 'zebrashipping')) . '</button> '
            . '<button class="btn btn-outline-danger mt-2" name="clear_license" value="1">'
            . self::escape(__('Remove license', 'zebrashipping')) . '</button></form>';
        echo '<h3 class="mt-4">' . self::escape(__('Custom ZPL label template', 'zebrashipping')) . '</h3>';
        $professional = is_array($licenseStatus)
            && !empty($licenseStatus['valid'])
            && ($licenseStatus['edition'] ?? '') === 'professional';
        if ($professional) {
            echo '<p>' . self::escape(__('Available only with a valid Professional license. Leave the field empty to use the built-in label layout. Supported placeholders:', 'zebrashipping'))
                . ' <code>{{width_dots}}</code>, <code>{{height_dots}}</code>, <code>{{dpi}}</code>, <code>{{city}}</code>, <code>{{recipient}}</code>, <code>{{address}}</code>, <code>{{phone}}</code>, <code>{{asset_name}}</code>, <code>{{asset_type}}</code>, <code>{{inventory_number}}</code>, <code>{{serial_number}}</code>, <code>{{notes}}</code>.</p>';
            echo '<form method="post" action="' . self::escape($configUrl) . '">';
            self::csrfField();
            echo '<label for="zs_zpl_template">' . self::escape(__('ZPL template', 'zebrashipping')) . '</label>';
            echo '<textarea class="form-control font-monospace" id="zs_zpl_template" name="zpl_template" rows="16" maxlength="'
                . ZplTemplate::MAX_LENGTH . '" spellcheck="false">' . self::escape($zplTemplate) . '</textarea>';
            echo '<button class="btn btn-primary mt-2" name="save_zpl_template" value="1">'
                . self::escape(__('Save ZPL template (clear the field to use the built-in layout)', 'zebrashipping'))
                . '</button></form>';
        } else {
            echo '<p>' . self::escape(__('Professional feature: customize the printer ZPL label template with fields from the GLPI record. Upgrade to Professional to edit and use a custom template.', 'zebrashipping')) . '</p>';
            echo '<textarea class="form-control font-monospace" rows="8" disabled aria-label="'
                . self::escape(__('Professional ZPL template editor preview', 'zebrashipping')) . '">'
                . self::escape("^XA\n^PW{{width_dots}}\n^LL{{height_dots}}\n^CI28\n^FO24,24^A0N,32,32^FD{{recipient}}^FS\n^FO24,70^A0N,24,24^FD{{address}}^FS\n^XZ")
                . '</textarea>';
        }
        echo '<h3 class="mt-4">' . self::escape(__('License and recovery backup', 'zebrashipping')) . '</h3>';
        echo '<p>' . self::escape(__('Download this recovery file and keep it in a secure location. It contains the license, installation identity, and custom ZPL template, but not your GLPI database or signing key.', 'zebrashipping')) . '</p>';
        echo '<p><a class="btn btn-outline-primary" href="' . self::escape($configUrl . '?download_recovery=1') . '">'
            . self::escape(__('Download recovery backup', 'zebrashipping')) . '</a></p>';
        echo '<form method="post" action="' . self::escape($configUrl) . '">';
        self::csrfField();
        echo '<label for="zs_recovery_backup">' . self::escape(__('Recovery backup JSON', 'zebrashipping')) . '</label>';
        echo '<textarea class="form-control font-monospace" id="zs_recovery_backup" name="recovery_backup" rows="8" maxlength="65536" spellcheck="false" placeholder="'
            . self::escape(__('Paste the contents of a previously downloaded recovery backup.', 'zebrashipping'))
            . '"></textarea><button class="btn btn-outline-secondary mt-2" name="restore_recovery" value="1">'
            . self::escape(__('Restore from recovery backup', 'zebrashipping')) . '</button></form>';
        echo '<p class="text-muted mt-3">' . self::escape(__('The service URL and API token are configured by the server administrator in the environment; they are never stored here or shown in this page.', 'zebrashipping')) . '</p>';
        echo '</div>';
        \Html::footer();
        return new Response((string) ob_get_clean());
    }

    private static function printerFields(array $values): void
    {
        foreach ([
            'name' => __('Name', 'zebrashipping'), 'host' => __('IP address / hostname', 'zebrashipping'),
            'port' => __('TCP port', 'zebrashipping'), 'dpi' => __('DPI', 'zebrashipping'),
            'width_mm' => __('Width (mm)', 'zebrashipping'), 'height_mm' => __('Height (mm)', 'zebrashipping'),
            'description' => __('Description / location', 'zebrashipping'),
        ] as $field => $label) {
            $value = $values[$field] ?? '';
            echo '<div class="mb-2"><label>' . self::escape($label) . '</label><input class="form-control" name="'
                . self::escape($field) . '" value="' . self::escape((string) $value) . '"'
                . (in_array($field, ['name', 'host'], true) ? ' required' : '') . '></div>';
        }
    }

    private static function csrfField(): void
    {
        echo '<input type="hidden" name="_glpi_csrf_token" value="' . self::escape(Session::getNewCSRFToken()) . '">';
    }

    private static function requestInt(Request $request, string $field, int $default, int $min, int $max): int
    {
        $raw = $request->request->getString($field, (string) $default);
        if (!preg_match('/^\d{1,4}$/', $raw)) {
            throw new \InvalidArgumentException(__('Printer dimensions must be whole numbers.', 'zebrashipping'));
        }
        $value = (int) $raw;
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException(__('Printer dimensions are outside the supported range.', 'zebrashipping'));
        }
        return $value;
    }

    private static function recoveryPayload(array $installation, string $license, string $zplTemplate): array
    {
        return [
            'format_version' => 1,
            'product' => 'glpi-zebra-shipping',
            'created_at' => gmdate(DATE_ATOM),
            'installation' => InstallationState::validate($installation),
            'license_file' => $license,
            'zpl_template' => $zplTemplate,
        ];
    }

    private static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
