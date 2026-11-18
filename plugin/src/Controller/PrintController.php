<?php

namespace GlpiPlugin\Zebrashipping\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Zebrashipping\EntityDefaults;
use GlpiPlugin\Zebrashipping\InstallationState;
use GlpiPlugin\Zebrashipping\MetadataValue;
use GlpiPlugin\Zebrashipping\ServiceClient;
use Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrintController extends AbstractController
{
    #[Route('/Print', name: 'zebrashipping_print', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            throw new \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException(['POST']);
        }
        Session::checkLoginUser();

        $supported = [
            'Computer', 'Monitor', 'Printer', 'NetworkEquipment', 'Peripheral', 'Phone', 'Unmanaged',
            'Appliance', 'Rack', 'Enclosure', 'PDU', 'PassiveDCEquipment', 'Cable', 'Software',
            'SoftwareLicense', 'Certificate', 'Ticket',
        ];
        $itemtype = $request->request->getString('itemtype');
        $itemsId = $request->request->getInt('items_id');
        if (!in_array($itemtype, $supported, true) || $itemsId < 1) {
            throw new \Glpi\Exception\Http\NotFoundHttpException();
        }
        $item = getItemForItemtype($itemtype);
        if (!$item || !$item->getFromDB($itemsId) || !$item->canUpdate()) {
            throw new \Glpi\Exception\Http\NotFoundHttpException();
        }

        $required = ['destination_city', 'recipient', 'address', 'phone', 'notes', 'printer_id'];
        $form = [];
        foreach ($required as $field) {
            $form[$field] = trim($request->request->getString($field));
            if (mb_strlen($form[$field]) > 500) {
                Session::addMessageAfterRedirect(__('A shipping field is too long.', 'zebrashipping'), false, ERROR);
                \Html::back();
            }
        }
        if ($form['printer_id'] === '') {
            Session::addMessageAfterRedirect(__('Choose a printer.', 'zebrashipping'), false, ERROR);
            \Html::back();
        }

        $requestId = $request->request->getString('request_id');
        if (!preg_match('/^[a-f0-9]{32}$/', $requestId)) {
            throw new \Glpi\Exception\Http\BadRequestHttpException();
        }

        global $DB;
        $existing = $DB->request([
            'FROM' => 'glpi_plugin_zebrashipping_jobs',
            'WHERE' => ['request_id' => $requestId],
            'LIMIT' => 1,
        ]);
        foreach ($existing as $_existingRow) {
            Session::addMessageAfterRedirect(__('This print request has already been submitted.', 'zebrashipping'), true, INFO);
            \Html::back();
        }

        $installState = InstallationState::get();
        $licenseSettings = \Config::getConfigurationValues('plugin:zebrashipping');
        $license = $licenseSettings['license_file'] ?? null;
        $defaults = EntityDefaults::forItem($item);
        $label = [
            'city' => $form['destination_city'] ?: $defaults['city'],
            'recipient' => $form['recipient'],
            'address' => $form['address'] ?: $defaults['address'],
            'phone' => $form['phone'],
            'asset_name' => MetadataValue::display($item->getField('name')),
            'asset_type' => $itemtype,
            'inventory_number' => MetadataValue::display($item->getField('otherserial')),
            'serial_number' => MetadataValue::display($item->getField('serial')),
            'notes' => $form['notes'],
        ];

        $record = [
            'request_id' => $requestId,
            'shipment_ref' => '',
            'itemtype' => $itemtype,
            'items_id' => $itemsId,
            'printer_id' => $form['printer_id'],
            'destination_city' => $label['city'],
            'recipient' => $label['recipient'],
            'address' => $label['address'],
            'phone' => $label['phone'],
            'notes' => $label['notes'],
            'created_by' => (int) Session::getLoginUserID(),
            'status' => 'queued',
        ];
        $DB->insert('glpi_plugin_zebrashipping_jobs', $record);
        $jobRowId = (int) $DB->insertId();

        try {
            $client = new ServiceClient();
            $job = $client->print([
                'request_id' => $requestId,
                'printer_id' => $form['printer_id'],
                'installation_id' => $installState['installation_id'],
                'trial_started_at' => $installState['installed_at'],
                'license_file' => $license ?: null,
                'label' => $label,
            ]);
            $DB->update('glpi_plugin_zebrashipping_jobs', [
                'id' => $jobRowId, 'service_job_id' => $job['id'] ?? null, 'status' => $job['status'] ?? 'sent',
                'response_text' => null,
            ]);
            Session::addMessageAfterRedirect(__('Print job sent to the printer.', 'zebrashipping'), true, INFO);
        } catch (\Throwable $error) {
            $safeMessage = mb_substr($error->getMessage(), 0, 400);
            $DB->update('glpi_plugin_zebrashipping_jobs', [
                'id' => $jobRowId, 'status' => 'failed', 'response_text' => $safeMessage,
            ]);
            Session::addMessageAfterRedirect(__('Printing failed: ', 'zebrashipping') . $safeMessage, false, ERROR);
        }

        \Html::back();
    }
}
