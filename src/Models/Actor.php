<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Actor extends BaseModel {

    use WithValidate;

    public ?string $name = null;
    public ?int $birth_year = null;
    public ?string $nationality = null;
    public ?string $image_path = null;

    //Whitelist di filtri permessi per questa classe 
    protected static array $allowed_filters = [
        "order_by", 
        "order",
        'limit',
        'nationality', 
        'birth_year_from', 
        'birth_year_to',
        'name'
    ];

    /**
     * Nome della collection
     */
    protected static ?string $table = "actors";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        $current_year = (int)date('Y');
        return [
            "name" => ["required", "sometimes", "min:2", "max:50"],
            "birth_year" => ["numeric", "min:1900", "max:{$current_year}"],
            "nationality" => ["min:2", "max:50"]
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
            static::filterByBirthYearFrom((int)$params['birth_year_from'], $conditions, $bindings);
        }

        if (isset($params['birth_year_to'])) {
            static::filterByBirthYearTo((int)$params['birth_year_to'], $conditions, $bindings);
        }

        if (isset($params['name'])) {
            static::search($params['name'], 'name' ,$conditions, $bindings);
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

    /**
     * Filtra la ricerca per nazionalità
     * @param array | string $value -> Rappresenta i valori provenienti dalla query string 'nationality'  ($_GET['nationality'])
     * @param array $conditions -> Rappresenta l'array con tutte le query da concatenare
     * @param array $bindings -> Rappresenta l'array con tutti i bindings per PDO
     * Utilizziamo l'accesso per riferimento, per modificare gli array originali
     */
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
                $bindings[$ph] = strtolower(trim($val));
            }

            $conditions[] = 'LOWER(nationality) IN (' . implode(',', $placeholders) . ')'; // nationality IN (:nationality_0,:nationality_1)

        } else {
            $conditions[] = 'LOWER(nationality) = :nationality';
            $bindings[':nationality'] = strtolower(trim($value));
        }

    }

    protected static function filterByBirthYearFrom(int $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'birth_year >= :birth_year_from';
        $bindings[':birth_year_from'] = $value;
    }

    protected static function filterByBirthYearTo(int $value, array &$conditions, array &$bindings):void {
        $conditions[] = 'birth_year <= :birth_year_to';
        $bindings[':birth_year_to'] = $value;
    }  

    protected function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

}