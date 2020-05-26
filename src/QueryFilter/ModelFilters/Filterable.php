<?php

namespace eloquentFilter\QueryFilter\ModelFilters;

use eloquentFilter\QueryFilter\QueryFilter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trait Filterable.
 */
trait Filterable
{
    /**
     * @param \eloquentFilter\QueryFilter\ModelFilters\ModelFilters $query
     * @param \eloquentFilter\QueryFilter\QueryFilter               $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, QueryFilter $filters): \Illuminate\Database\Eloquent\Builder
    {
        return $filters->apply($query, $this->getTable());
    }

    public function scopeInclude($query, $includes): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with($includes);
    }

    public function scopeSort($query, $sorts): \Illuminate\Database\Eloquent\Builder
    {
        foreach($sorts as $field => $direction) {
            $direction = in_array(Str::lower($direction), ['asc', 'desc']) ? $direction : 'asc';
            $query->orderBy($field, $direction);
        }

        return $query;
    }

    /**
     * @return mixed
     */
    public static function getWhiteListFilter()
    {
        return self::$whiteListFilter;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function addWhiteListFilter($value)
    {
        self::$whiteListFilter[] = $value;
    }

    /**
     * @param $array
     *
     * @return mixed
     */
    public static function setWhiteListFilter(array $array)
    {
        self::$whiteListFilter = $array;
    }
}
