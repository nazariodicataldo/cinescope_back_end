<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Movie extends BaseModel {

    use WithValidate;

    public ?string $title = null;
    public ?int $production_year = null;
    public ?string $nationality = null;
    public ?string $director = null;
    public ?string $genre = null;

    //Whitelist di filtri permessi per questa classe 
    protected static array $allowed_filters = ['nationality', 'title', 'director', 'genre', 'production_year_from', 'production_year_to'];

    /**
     * Nome della collection
     */
    protected static ?string $table = "movies";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        $current_year = (int)date('Y');

        return [
            "title" => ["required", "sometimes", "min:2", "max:100"],
            "production_year" => ["numeric", "min:1900", "max:{$current_year}"],
            "nationality" => ["min:2", "max:50"],
            "director" => ["required", "sometimes", "min:2", "max:50"],
            "genre" => ["min:2", "max:50"],
        ];
    }

    public static function filter(array $params): array
    {
        $conditions = []; //Conterrà tutte gli AND
        $bindings = [];

        if (isset($params['nationality'])) {
            static::filterByNationality($params['nationality'], $conditions, $bindings);
        }

        if (isset($params['birth_year_from'])) {
            static::filterByProductionYearFrom((int)$params['production_year_from'], $conditions, $bindings);
        }

        if (isset($params['birth_year_to'])) {
            static::filterByProductionYearTo((int)$params['production_year_to'], $conditions, $bindings);
        }

        if (isset($params['genre'])) {
            static::search($params['genre'], 'genre' ,$conditions, $bindings);
        }

        if (isset($params['title'])) {
            static::search($params['title'], 'title' ,$conditions, $bindings);
        }

        if (isset($params['director'])) {
            static::search($params['director'], 'director' ,$conditions, $bindings);
        }

        $where = '';
        if ($conditions) {
            $where = ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT * FROM " . static::getTableName() . $where;

        $rows = DB::select($sql, $bindings);

        return array_map(fn($row) => new static($row), $rows);
    }

    protected static function filterByNationality(string | array $value, array &$conditions, array &$bindings):void {

        //Prima verifico se value è un array o no
        /** 
         * $_GET['nationality'] = [0 => 'Italia', 1 => 'Francia]
         * $_GET['nationality'] = 'Italia' 
        */

        if(is_array($value)) {
            $placeholders = [];

            foreach ($value as $i => $val) {
                $ph = ":nationality_$i";
                $placeholders[] = $ph;
                $bindings[$ph] = trim($val);
            }

            $conditions[] = 'nationality IN (' . implode(',', $placeholders) . ')'; // nationality IN (:nationality_0,:nationality_1)

        } else {
            $conditions[] = 'nationality = :nationality';
            $bindings[':nationality'] = trim($value);
        }

    }

    protected static function filterByProductionYearFrom(int $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'production_year >= :production_year_from';
        $bindings[':production_year_from'] = $value;
    }

    protected static function filterByProductionYearTo(int $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'production_year <= :production_year_to';
        $bindings[':production_year_to'] = $value;
    } 

    protected function actors()
    {
        return $this->belongsToMany(Actor::class);
    }

    protected function projections()
    {
        return $this->hasMany(Projection::class);
    }

}