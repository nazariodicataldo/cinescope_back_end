<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Projection extends BaseModel {

    use WithValidate;

    public ?int $movie_id = null;
    public ?int $hall_id = null;
    public ?float $takings = null;
    public ?string $projection_date = null;

    /**
     * Nome della collection
     */
    protected static ?string $table = "projections";

    //Whitelist di filtri permessi per questa classe 
    protected static array $allowed_filters = ['projection_date_from', 'projection_date_to', 'takings_from', 'takings_to'];

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        return [
            "movie_id" => ["required", "sometimes", "numeric", "min:1", "max:50"],
            "hall_id" => ["required", "sometimes", "numeric", "min:1", "max:50"],
            "takings" => ["numeric", "min:0"],
            "projection_date" => ["datetime"]
        ];
    }

    public static function filter(array $params): array
    {
        $conditions = []; //Conterrà tutte gli AND
        $bindings = [];

        if (isset($params['projection_date_from'])) {
            static::filterByProductionDateFrom((int)$params['projection_date_from'], $conditions, $bindings);
        }

        if (isset($params['projection_date_to'])) {
            static::filterByProductionDateTo((int)$params['projection_date_to'], $conditions, $bindings);
        }

        if (isset($params['takings_from'])) {
            static::filterByTakingsFrom((float)$params['takings_from'], $conditions, $bindings);
        }

        if (isset($params['takings_to'])) {
            static::filterByTakingsTo((float)$params['takings_to'], $conditions, $bindings);
        }

        $where = '';
        if ($conditions) {
            $where = ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT * FROM " . static::getTableName() . $where;

        $rows = DB::select($sql, $bindings);

        return array_map(fn($row) => new static($row), $rows);
    }

    protected static function filterByProductionDateFrom(string $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'production_date >= :production_date_from';
        $bindings[':production_date_from'] = $value;
    }

    protected static function filterByProductionDateTo(string $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'production_date <= :production_date_to';
        $bindings[':production_date_to'] = $value;
    } 
    
    protected static function filterByTakingsFrom(float $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'takings >= :takings_from';
        $bindings[':takings_from'] = $value;
    }

    protected static function filterByTakingsTo(float $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'takings <= :takings_to';
        $bindings[':takings_to'] = $value;
    } 

    protected function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    protected function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}