<?php
namespace wpstudio\cloud\Models;

use Model;

/**
 * Model
 */
class Vps extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string
     */
    public $belongsTo = [
        'platform' => Platform::class,
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_cloud_vps';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
    
    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = ["status"];

    /**
     * @var string[]
     */
}
