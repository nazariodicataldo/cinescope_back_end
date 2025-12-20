<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class Hall extends BaseModel {

    use WithValidate;

    public ?string $name = null;
    public ?int $places = null;
    public ?string $city = null;

    /**
     * Nome della collection
     */
    protected static ?string $table = "movies";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        return [
            "name" => ["required", "min:2", "max:100"],
            "places" => ["numeric", "min:0", "max:150"],
            "city" => ["required", "min:2", "max:50"],
        ];
    }

}