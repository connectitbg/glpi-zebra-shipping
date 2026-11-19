<?php

namespace GlpiPlugin\Zebrashipping;

use CommonGLPI;
use Session;

final class ShippingTab extends \CommonGLPI
{
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if (!$item->getID() || !$this->isSupported($item)) {
            return '';
        }
        return self::createTabEntry(__('Shipping label', 'zebrashipping'));
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if (!$item->getID() || !$item->can($item->getID(), UPDATE)) {
            throw new \Glpi\Exception\Http\NotFoundHttpException();
        }

        global $DB;
        $defaults = EntityDefaults::forItem($item);
        $license = LicenseStorage::get();
        $service = null;
        $message = '';
        try {
            $client = new ServiceClient();
            $service = $client;
            $status = $client->licenseStatus(InstallationState::get(), $license);
            $printers = $client->printers();
            if (empty($status['valid'])) {
                $message = __('The demo period or license has expired. Contact your administrator.', 'zebrashipping');
            }
        } catch (\Throwable $error) {
            $printers = [];
            $message = $error->getMessage();
        }
        echo '<div class="p-3">';
        echo '<h2>' . zebrashipping_escape(__('Create shipping label', 'zebrashipping')) . '</h2>';
        if ($message !== '') {
            echo '<div class="alert alert-info">' . zebrashipping_escape($message) . '</div>';
        }
        if (!$service || !$printers) {
            echo '<p>' . zebrashipping_escape(__('Configure the Print Service and add a printer in Setup → Plugins → GLPI Zebra Shipping.', 'zebrashipping')) . '</p>';
        } elseif (!empty($status['valid'])) {
            $itemtype = $item->getType();
            $itemsId = (int) $item->getID();
            $assetName = MetadataValue::display($item->getField('name'));
            $assetType = $itemtype;
            $serial = MetadataValue::display($item->getField('serial'));
            $inventory = MetadataValue::display($item->getField('otherserial'));
            global $CFG_GLPI;
            $printUrl = rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/plugins/zebrashipping/Print';
            echo '<form method="post" action="' . zebrashipping_escape($printUrl) . '">';
            echo '<input type="hidden" name="_glpi_csrf_token" value="' . zebrashipping_escape(Session::getNewCSRFToken()) . '">';
            echo '<input type="hidden" name="itemtype" value="' . zebrashipping_escape($itemtype) . '">';
            echo '<input type="hidden" name="items_id" value="' . $itemsId . '">';
            echo '<input type="hidden" name="request_id" value="' . bin2hex(random_bytes(16)) . '">';
            self::textField('destination_city', __('City / company', 'zebrashipping'), $defaults['city'], 150);
            echo '<small class="form-text text-muted">' . zebrashipping_escape(__('Used as the large label heading when Recipient / office is blank.', 'zebrashipping')) . '</small>';
            self::textField('recipient', __('Recipient / office', 'zebrashipping'), '', 255);
            self::textField('address', __('Address', 'zebrashipping'), $defaults['address']);
            self::textField('phone', __('Phone', 'zebrashipping'), '', 80);
            self::textField('asset_name', __('Asset name', 'zebrashipping'), $assetName);
            self::textField('asset_type', __('Asset type', 'zebrashipping'), $assetType);
            self::textField('inventory_number', __('Inventory number', 'zebrashipping'), $inventory);
            self::textField('serial_number', __('Serial number', 'zebrashipping'), $serial);
            echo '<div class="mb-3"><label for="zs_notes">' . zebrashipping_escape(__('Notes', 'zebrashipping')) . '</label>';
            echo '<textarea class="form-control" id="zs_notes" name="notes" maxlength="500"></textarea></div>';
            echo '<div class="mb-3"><label for="zs_printer">' . zebrashipping_escape(__('Printer', 'zebrashipping')) . '</label>';
            echo '<select class="form-select" id="zs_printer" name="printer_id" required>';
            foreach ($printers as $printer) {
                if (empty($printer['enabled'])) {
                    continue;
                }
                echo '<option value="' . zebrashipping_escape((string) $printer['id']) . '">' . zebrashipping_escape((string) $printer['name']) . '</option>';
            }
            echo '</select></div>';
            echo '<button class="btn btn-primary" type="submit" name="add" value="1">' . zebrashipping_escape(__('Print label', 'zebrashipping')) . '</button>';
            echo '</form>';
        }
        echo '<h3 class="mt-4">' . zebrashipping_escape(__('Recent print jobs', 'zebrashipping')) . '</h3>';
        $rows = $DB->request([
            'FROM' => 'glpi_plugin_zebrashipping_jobs',
            'WHERE' => ['itemtype' => $item->getType(), 'items_id' => $item->getID()],
            'ORDER' => 'id DESC', 'LIMIT' => 10,
        ]);
        echo '<table class="table"><thead><tr><th>' . zebrashipping_escape(__('Date', 'zebrashipping')) . '</th><th>'
            . zebrashipping_escape(__('Printer', 'zebrashipping')) . '</th><th>' . zebrashipping_escape(__('Status', 'zebrashipping'))
            . '</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr><td>' . zebrashipping_escape((string) $row['created_at']) . '</td><td>'
                . zebrashipping_escape((string) $row['printer_id']) . '</td><td>'
                . zebrashipping_escape((string) $row['status']) . '</td></tr>';
        }
        echo '</tbody></table></div>';
        return true;
    }

    private static function textField(string $name, string $label, string $value, int $maxLength = 500): void
    {
        echo '<div class="mb-3"><label for="zs_' . zebrashipping_escape($name) . '">' . zebrashipping_escape($label) . '</label>';
        echo '<input class="form-control" id="zs_' . zebrashipping_escape($name) . '" name="' . zebrashipping_escape($name)
            . '" value="' . zebrashipping_escape($value) . '" maxlength="' . $maxLength . '"></div>';
    }

    private function isSupported(CommonGLPI $item): bool
    {
        return in_array($item->getType(), [
            'Computer', 'Monitor', 'Printer', 'NetworkEquipment', 'Peripheral', 'Phone', 'Ticket',
        ], true);
    }
}
