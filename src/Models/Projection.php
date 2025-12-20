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
    protected static ?string $table = "movies";

    public function __construct(array $data = []) {
        parent::__construct($data);
    }

    protected static function validationRules(): array {
        return [
            "movie_id" => ["required", "sometimes", "numeric", "min:1", "max:50"],
            "places" => ["required", "sometimes", "numeric", "min:1", "max:50"],
            "takings" => ["numeric", "min:0"],
            "projection_date" => ["datetime"]
        ];
    }

}