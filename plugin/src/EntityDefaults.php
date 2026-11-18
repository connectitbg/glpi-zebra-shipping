<?php

namespace GlpiPlugin\Zebrashipping;

use CommonGLPI;
use Entity;

final class EntityDefaults
{
    public static function forItem(CommonGLPI $item): array
    {
        $path = [];
        $entityId = (int) ($item->getField('entities_id') ?? 0);
        $seen = [];
        while ($entityId > 0 && !isset($seen[$entityId]) && count($path) < 32) {
            $seen[$entityId] = true;
            $entity = new Entity();
            if (!$entity->getFromDB($entityId)) {
                break;
            }
            $path[] = (string) ($entity->fields['name'] ?? '');
            $entityId = (int) ($entity->fields['entities_id'] ?? 0);
        }
        $path = array_values(array_filter(array_reverse($path), static fn ($part) => trim($part) !== ''));
        return [
            'city' => $path[0] ?? '',
            'address' => implode(' / ', array_slice($path, 1)),
        ];
    }
}
