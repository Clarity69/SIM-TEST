<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

/**
 * Menyimpan atribut user yang login di cache lokal agar setiap request tidak perlu
 * query "select * from users" ke database (Supabase jauh: ±0,1–0,3 dtk per query).
 *
 * Hanya array atribut yang di-cache (cache.serializable_classes = false), lalu
 * di-hydrate kembali menjadi model. Cache dibersihkan saat user disimpan/dihapus
 * (lihat User::booted()).
 */
class CachedUserProvider extends EloquentUserProvider
{
    public const TTL_SECONDS = 600;

    public static function cacheKey(mixed $id): string
    {
        return "auth.user.{$id}";
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        $attributes = Cache::remember(self::cacheKey($identifier), self::TTL_SECONDS, function () use ($identifier) {
            return parent::retrieveById($identifier)?->getAttributes();
        });

        if (! is_array($attributes)) {
            Cache::forget(self::cacheKey($identifier));

            return null;
        }

        return $this->createModel()->newFromBuilder($attributes);
    }
}
