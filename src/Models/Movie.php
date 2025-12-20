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

    /**
     * Nome della collection
     */
    protected static ?string $table = "movies";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        $current_year = (int)date('Y', 'now');

        return [
            "title" => ["required", "min:2", "max:100"],
            "production_year" => ["numeric", "min:1900", "max:{$current_year}"],
            "nationality" => ["min:2", "max:50"],
            "director" => ["required", "min:2", "max:50"],
            "genre" => ["min:2", "max:50"],
        ];
    }

}