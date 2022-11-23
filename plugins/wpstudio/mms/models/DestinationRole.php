<?php namespace Wpstudio\Mms\Models;

use Model;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;

/**
 * Model
 */
class DestinationRole extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_destination_roles';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    public $hasMany = [
        'containers' => Container::class,
    ];
}
