<?php

namespace GlpiPlugin\Zebrashipping\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Zebrashipping\InstallationState;
use GlpiPlugin\Zebrashipping\ServiceClient;
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
        $settings = \Config::getConfigurationValues('plugin:zebrashipping');
        $license = (string) ($settings['license_file'] ?? '');
        $installState = InstallationState::get();
        $service = null;

        if ($request->isMethod('GET') && $request->query->getBoolean('download_recovery')) {
            $payload = self::recoveryPayload($installState, $license);
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
                    $license = trim($request->request->getString('license_file'));
                    if (mb_strlen($license) > 8192) {
                        throw new \InvalidArgumentException(__('License text is too long.', 'zebrashipping'));
                    }
                    if ($license !== '') {
                        $licenseStatus = $service->licenseStatus($installState, $license);
                        if (empty($licenseStatus['valid'])) {
                            throw new \RuntimeException(__('The supplied license could not be validated.', 'zebrashipping'));
                        }
                    }
                    \Config::setConfigurationValues('plugin:zebrashipping', ['license_file' => $license]);
                    $settings['license_file'] = $license;
                    $messages[] = __('License saved.', 'zebrashipping');
                } elseif ($request->request->has('restore_recovery')) {
                    $rawBackup = trim($request->request->getString('recovery_backup'));
                    if ($rawBackup === '' || strlen($rawBackup) > 16384) {
                        throw new \InvalidArgumentException(__('Recovery backup is empty or too large.', 'zebrashipping'));
                    }
                    $backup = json_decode($rawBackup, true, 16, JSON_THROW_ON_ERROR);
                    if (!is_array($backup)
                        || ($backup['product'] ?? null) !== 'glpi-zebra-shipping'
                        || ($backup['format_version'] ?? null) !== 1
                        || !is_array($backup['installation'] ?? null)
                        || !is_string($backup['license_file'] ?? null)
                        || mb_strlen($backup['license_file']) > 8192) {
                        throw new \InvalidArgumentException(__('This is not a supported Zebra Shipping recovery backup.', 'zebrashipping'));
                    }
                    $restoredState = InstallationState::validate($backup['installation']);
                    $restoredLicense = trim($backup['license_file']);
                    $restoredStatus = $service->licenseStatus($restoredState, $restoredLicense ?: null);
                    if (empty($restoredStatus['valid'])) {
                        throw new \RuntimeException(__('The recovery backup license or demo period is not valid.', 'zebrashipping'));
                    }
                    InstallationState::restore($restoredState);
                    \Config::setConfigurationValues('plugin:zebrashipping', ['license_file' => $restoredLicense]);
                    $installState = $restoredState;
                    $license = $restoredLicense;
                    $settings['license_file'] = $license;
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
                            'width_mm' => max(20, min(300, $request->request->get('width_mm', 93))),
                            'height_mm' => max(20, min(300, $request->request->get('height_mm', 71))),
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
        echo '<textarea class="form-control" name="license_file" rows="3" autocomplete="off" spellcheck="false">'
            . self::escape($license) . '</textarea><button class="btn btn-primary mt-2" name="save_license" value="1">'
            . self::escape(__('Save license', 'zebrashipping')) . '</button></form>';
        echo '<h3 class="mt-4">' . self::escape(__('License and recovery backup', 'zebrashipping')) . '</h3>';
        echo '<p>' . self::escape(__('Download this recovery file and keep it in a secure location. It contains the license and installation identity, but not your GLPI database or signing key.', 'zebrashipping')) . '</p>';
        echo '<p><a class="btn btn-outline-primary" href="' . self::escape($configUrl . '?download_recovery=1') . '">'
            . self::escape(__('Download recovery backup', 'zebrashipping')) . '</a></p>';
        echo '<form method="post" action="' . self::escape($configUrl) . '">';
        self::csrfField();
        echo '<label for="zs_recovery_backup">' . self::escape(__('Recovery backup JSON', 'zebrashipping')) . '</label>';
        echo '<textarea class="form-control font-monospace" id="zs_recovery_backup" name="recovery_backup" rows="8" maxlength="16384" spellcheck="false">'
            . self::escape(json_encode(self::recoveryPayload($installState, $license), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            . '</textarea><button class="btn btn-outline-secondary mt-2" name="restore_recovery" value="1">'
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

    private static function recoveryPayload(array $installation, string $license): array
    {
        return [
            'format_version' => 1,
            'product' => 'glpi-zebra-shipping',
            'created_at' => gmdate(DATE_ATOM),
            'installation' => InstallationState::validate($installation),
            'license_file' => $license,
        ];
    }

    private static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
