<?php

namespace App\Services;

use App\Models\System\DebtorType;
use Illuminate\Support\Str;

class DebtorTypeService
{
    public function create(array $attributes): DebtorType
    {
        $slug = Str::slug($attributes['slug'] ?? $attributes['name']);
        $slug = $this->uniqueSlug($slug);

        return DebtorType::create([
            'name' => $attributes['name'],
            'slug' => $slug,
            'description' => $attributes['description'] ?? null,
            'is_active' => (bool) ($attributes['is_active'] ?? false),
        ]);
    }

    public function update(DebtorType $debtorType, array $attributes): DebtorType
    {
        $slug = Str::slug($attributes['slug'] ?? $attributes['name']);
        if ($slug !== $debtorType->slug) {
            $slug = $this->uniqueSlug($slug, $debtorType->id);
        }

        $debtorType->update([
            'name' => $attributes['name'],
            'slug' => $slug,
            'description' => $attributes['description'] ?? null,
            'is_active' => (bool) ($attributes['is_active'] ?? false),
        ]);

        return $debtorType->fresh();
    }

    /**
     * @return string|null Error message, or null when deleted successfully.
     */
    public function tryDelete(DebtorType $debtorType): ?string
    {
        if (in_array($debtorType->slug, ['cash', 'credit'], true)) {
            return 'Built-in cash/credit types cannot be deleted.';
        }

        if ($debtorType->tenants()->exists()) {
            return 'Cannot delete a debtor type that is assigned to agencies.';
        }

        $debtorType->delete();

        return null;
    }

    public function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base ?: Str::random(8);
        $n = 1;
        while (
            DebtorType::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }
}
