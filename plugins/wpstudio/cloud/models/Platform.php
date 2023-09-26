<?php namespace wpstudio\cloud\Models;

use Model;

/**
 * Model
 */
class Platform extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string
     */
    public $belongsTo = [
        'platform_type' => PlatformType::class
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_cloud_platforms';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    /**
     * @var string[]
     */
    protected $casts = [
        "api_key" => "encrypted",
    ];
}
