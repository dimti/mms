<?php
namespace wpstudio\cloud\Models;

use Model;
use \Winter\Storm\Database\Traits\Validation;
use \Winter\Storm\Database\Traits\SoftDelete;

/**
 * Model
 */
class Vps extends Model
{
    use Validation;
    use SoftDelete;

    /**
     * @var string[]
     */
    protected $fillable = [
        'vps_id',
        'vps_name',
        'slug',
        'hostname',
        'ip_address',
        'status',
        'platform_id',
    ];

    protected $dates = ['deleted_at'];

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
    public $jsonable = ['status'];

    protected $casts = [
        "api_key" => "encrypted",
    ];
}
