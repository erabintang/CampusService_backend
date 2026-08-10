<?php

namespace App\Models\Concerns;

use App\Database\MongoQueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Laravel\Connection as MongoDBConnection;
use MongoDB\Laravel\Eloquent\Builder as MongoDBEloquentBuilder;

/**
 * Membuat model Eloquent dapat dipakai di KEDUA jenis koneksi:
 *
 * - MongoDB (koneksi "mongodb"): query/Eloquent builder dari paket
 *   mongodb/laravel-mongodb, _id dipetakan ke atribut "id", tanggal
 *   disimpan sebagai BSON UTCDateTime.
 * - SQL (MySQL dll.): perilaku standar Laravel — TIDAK berubah sama sekali.
 *
 * Dipakai oleh semua model agar fitur lama (MySQL) tetap utuh sementara
 * migrasi ke MongoDB Atlas berjalan.
 */
trait HandlesMongoConnection
{
    /**
     * Relasi induk (embedded) — hanya dipakai paket MongoDB saat model
     * menjadi bagian relasi; default null berarti tidak ada.
     */
    protected $parentRelation;

    protected function isMongoConnection(): bool
    {
        return $this->getConnection() instanceof MongoDBConnection;
    }

    /**
     * Apakah model ini sedang berjalan di atas koneksi MongoDB?
     *
     * Dipakai controller/service untuk memilih jalur query yang kompatibel
     * (mis. mengganti withCount/whereHas yang tidak didukung MongoDB).
     */
    public static function isMongo(): bool
    {
        return static::resolveConnection() instanceof MongoDBConnection;
    }

    public function newEloquentBuilder($query)
    {
        if ($this->isMongoConnection()) {
            return new MongoDBEloquentBuilder($query);
        }

        return parent::newEloquentBuilder($query);
    }

    protected function newBaseQueryBuilder()
    {
        if ($this->isMongoConnection()) {
            $connection = $this->getConnection();

            return new MongoQueryBuilder(
                $connection,
                $connection->getQueryGrammar(),
                $connection->getPostProcessor()
            );
        }

        return parent::newBaseQueryBuilder();
    }

    /**
     * Kunci qualified tidak memakai prefix tabel di MongoDB — relasi
     * (belongsTo/hasMany) harus me-query "id", bukan "categories.id".
     */
    public function getQualifiedKeyName()
    {
        if ($this->isMongoConnection()) {
            return $this->getKeyName();
        }

        return parent::getQualifiedKeyName();
    }

    /**
     * Kolom MongoDB tidak memakai prefix tabel ("categories.id" tidak valid) —
     * kembalikan nama kolom apa adanya.
     */
    public function qualifyColumn($column)
    {
        if ($this->isMongoConnection()) {
            return $column;
        }

        return parent::qualifyColumn($column);
    }

    /**
     * Atribut "id" pada MongoDB berasal dari field "_id" (ObjectId).
     */
    public function getIdAttribute($value = null)
    {
        if ($this->isMongoConnection()) {
            $id = $this->attributes['_id'] ?? $this->attributes['id'] ?? null;

            return $id instanceof ObjectId ? (string) $id : $id;
        }

        return $value;
    }

    /**
     * Simpan tanggal sebagai BSON UTCDateTime saat koneksi MongoDB.
     */
    public function fromDateTime($value)
    {
        if ($this->isMongoConnection()) {
            if ($value instanceof UTCDateTime) {
                return $value;
            }

            $timestamp = $value instanceof \DateTimeInterface
                ? $value->getTimestamp() * 1000
                : Carbon::parse($value)->getTimestampMs();

            return new UTCDateTime($timestamp);
        }

        return parent::fromDateTime($value);
    }

    protected function asDateTime($value)
    {
        if ($this->isMongoConnection() && $value instanceof UTCDateTime) {
            // ext-mongodb 2.3.x: UTCDateTime punya toDateTime()/toDateTimeImmutable(),
            // TIDAK punya toMilliseconds() (method itu ada di versi lebih baru).
            if (method_exists($value, 'toDateTimeImmutable')) {
                return Carbon::instance($value->toDateTimeImmutable());
            }

            if (method_exists($value, 'toDateTime')) {
                return Carbon::instance($value->toDateTime());
            }

            return Carbon::createFromTimestampMs((int) (string) $value);
        }

        return parent::asDateTime($value);
    }

    /**
     * Cast 'array'/'json': di MongoDB array tersimpan sebagai BSON native
     * (bukan JSON string), sehingga jangan di-decode ulang lewat json_decode.
     * Nilai string JSON (MySQL) tetap diteruskan ke perilaku standar Laravel.
     */
    public function fromJson($value, $asObject = false)
    {
        if (is_array($value)) {
            return $asObject ? (object) $value : $value;
        }

        return parent::fromJson($value, $asObject);
    }

    public function setParentRelation(Relation $relation)
    {
        $this->parentRelation = $relation;
    }

    public function getParentRelation(): ?Relation
    {
        return $this->parentRelation ?? null;
    }
}
