<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FlightResultsPaginator
{
    public const PER_PAGE = 10;

    /**
     * Slice solutions into pages when count exceeds {@see PER_PAGE}.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public static function apply(array $result, Request $request, int $perPage = self::PER_PAGE): array
    {
        $solutions = $result['solutions'] ?? [];
        if (! is_array($solutions)) {
            $solutions = [];
        }

        $count = count($solutions);
        $result['solutions_total'] = $count;

        if ($count <= $perPage) {
            $result['solutions'] = $solutions;
            $result['solutions_paginator'] = null;

            return $result;
        }

        $page = max(1, (int) $request->input('page', 1));
        $paginator = new LengthAwarePaginator(
            array_values(array_slice($solutions, ($page - 1) * $perPage, $perPage)),
            $count,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $result['solutions'] = $paginator->items();
        $result['solutions_paginator'] = $paginator;

        return $result;
    }
}
