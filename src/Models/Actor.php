<?php

namespace App\Models;

use App\Traits\WithValidate;
use App\Database\DB;

class User extends BaseModel {

    use WithValidate;

    public ?string $name = null;
    public ?int $birth_year = null;
    public ?string $nationality = null;

    /**
     * Nome della collection
     */
    protected static ?string $table = "actors";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        $current_year = (int)date('Y', 'now');
        return [
            "name" => ["required", "min:2", "max:50"],
            "birth_year" => ["required", "numeric", "min:1900", "max:{$current_year}"],
            "nationality" => ["required", "min:2", "max:50"]
        ];
    }

}