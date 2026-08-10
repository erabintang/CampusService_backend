<?php

namespace App\Database;

use MongoDB\Laravel\Query\Builder as MongoBaseQueryBuilder;

/**
 * Perbaikan kompatibilitas khusus project ini di atas query builder MongoDB.
 *
 * Laravel memakai whereIntegerInRaw() saat eager-load relasi (hasMany/
 * belongsTo); MongoDB tidak mendukung metode tersebut. Nilai id kita berupa
 * string/ObjectId, jadi fallback ke whereIn()/whereNotIn() setara secara
 * semantik.
 */
class MongoQueryBuilder extends MongoBaseQueryBuilder
{
    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        return $this->whereIn($column, $values, $boolean, $not);
    }

    public function whereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        return $this->whereNotIn($column, $values, $boolean);
    }
}
