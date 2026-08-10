<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Menghitung jumlah relasi secara cross-driver.
 *
 * Eloquent `withCount()` tidak didukung oleh mongodb/laravel-mongodb
 * (menyebabkan 500 via mekanisme whereColumn). Di koneksi MongoDB jumlah
 * relasi dihitung lewat satu query `whereIn(foreign key)` lalu dihitung di
 * PHP; di koneksi SQL perilaku asli Laravel (`loadCount`) dipertahankan
 * tanpa perubahan.
 */
final class RelationCounts
{
    /**
     * Menempelkan atribut jumlah (mis. products_count / orders_count) ke setiap model.
     *
     * @param  Collection<int, Model>  $models
     * @param  \Closure|null  $constrain  constraint tambahan pada model terkait (mis. status aktif)
     */
    public static function attachCount(Collection $models, string $relation, string $foreignKey, string $attribute, ?\Closure $constrain = null): Collection
    {
        if ($models->isEmpty()) {
            return $models;
        }

        $first = $models->first();

        if ($first::isMongo()) {
            $ids = $models->pluck('id');

            $query = $first->{$relation}()->getRelated()->newQuery()
                ->whereIn($foreignKey, $ids);

            if ($constrain !== null) {
                $constrain($query);
            }

            $counts = $query->get([$foreignKey])
                ->pluck($foreignKey)
                ->map(fn ($value) => (string) $value)
                ->countBy();

            $models->each(function (Model $model) use ($counts, $attribute): void {
                $model->setAttribute($attribute, (int) ($counts[(string) $model->id] ?? 0));
            });

            return $models;
        }

        $models->loadCount([$relation => $constrain ?? fn ($query) => $query]);

        return $models;
    }
}
