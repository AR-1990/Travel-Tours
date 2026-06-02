<?php

namespace App\Services\Travelport;

class TravelportAirCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function groups(): array
    {
        $g = config('travelport_air_operations.groups', []);

        return is_array($g) ? $g : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function operations(): array
    {
        $ops = config('travelport_air_operations.operations', []);

        return is_array($ops) ? $ops : [];
    }

    /**
     * Flight flow operations shown in the UI.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function flightOperations(): array
    {
        return array_filter(
            self::operations(),
            fn (array $op): bool => in_array($op['service'] ?? '', ['air', 'universal_record', 'flight'], true)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        return self::operations()[$key] ?? null;
    }

    public static function exists(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * @return list<array{key: string, group: string, group_label: string, group_icon: string, operations: list<array<string, mixed>>}>
     */
    public static function groupedForUi(): array
    {
        $groups = self::groups();
        $bucket = [];

        foreach ($groups as $slug => $meta) {
            $bucket[$slug] = [
                'key' => $slug,
                'group' => $slug,
                'group_label' => $meta['label'] ?? $slug,
                'group_description' => $meta['description'] ?? '',
                'group_icon' => $meta['icon'] ?? 'fa-plane',
                'order' => $meta['order'] ?? 99,
                'operations' => [],
            ];
        }

        foreach (self::flightOperations() as $key => $op) {
            $group = $op['group'] ?? 'shop';
            if (! isset($bucket[$group])) {
                $bucket[$group] = [
                    'key' => $group,
                    'group' => $group,
                    'group_label' => ucfirst($group),
                    'group_icon' => 'fa-plane',
                    'order' => 99,
                    'operations' => [],
                ];
            }
            $bucket[$group]['operations'][] = array_merge($op, ['key' => $key]);
        }

        uasort($bucket, fn ($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        return array_values(array_filter($bucket, fn (array $g): bool => $g['operations'] !== []));
    }
}
