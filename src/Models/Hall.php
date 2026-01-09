<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Hall extends BaseModel {

    use WithValidate;

    public ?string $name = null;
    public ?int $places = null;
    public ?string $city = null;
    public ?string $image_path = null;

    /**
     * Nome della collection
     */
    protected static ?string $table = "halls";

    //Whitelist di filtri permessi per questa classe 
    protected static array $allowed_filters = [
        "order_by", 
        'limit',
        "order",
        'city', 
        'places_from',
        'places_to',
        'name'
    ];

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    public static function filter(array $params): array
    {
        $conditions = []; //Conterrà tutte gli AND
        $bindings = [];

        if (isset($params['places_from'])) {
            static::filterByPlacesFrom((int)$params['places_from'], $conditions, $bindings);
        }

        if (isset($params['places_to'])) {
            static::filterByPlacesTo((int)$params['places_to'], $conditions, $bindings);
        }

        if (isset($params['name'])) {
            static::search($params['name'], 'name' ,$conditions, $bindings);
        }

        if (isset($params['city'])) {
            static::search($params['city'], 'city' ,$conditions, $bindings);
        }

        //Order by
        $column = $params['order_by'] ?? 'id';
        $order = $params['order'] ?? 'ASC';
        $order_by = static::orderBy($column, $order, $conditions, $bindings);

        //Limit
        $limit = static::limit($params['limit'] ?? null);

        $where = '';
        if ($conditions) {
            $where = ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT * FROM " . static::getTableName() . $where . $order_by . $limit;

        $rows = DB::select($sql, $bindings);

        return array_map(fn($row) => new static($row), $rows);
    }

    protected static function filterByPlacesFrom(int $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'places >= :places_from';
        $bindings[':places_from'] = $value;
    }

    protected static function filterByPlacesTo(int $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'places <= :places_to';
        $bindings[':places_to'] = $value;
    }

    protected static function validationRules(): array {
        return [
            "name" => ["required", "sometimes", "min:2", "max:100"],
            "places" => ["numeric", "min:0", "max:150"],
            "city" => ["required", "sometimes", "min:2", "max:50"],
        ];
    }

    public static function getAllCities(): array {
        return DB::select("SELECT DISTINCT city FROM " . static::getTableName());
    }

    //Get min place number
    public static function getMinPlace(): array {
        return DB::select("SELECT DISTINCT MIN(places) FROM " . static::getTableName());
    }

    //Get max birth number
    public static function getMaxPlace(): array {
        return DB::select("SELECT DISTINCT MAX(places) FROM " . static::getTableName());
    }

    protected function projections()
    {
        return $this->hasMany(Projection::class);
    }
}