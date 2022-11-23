<?php namespace Wpstudio\Mms\Models;

use Model;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;

/**
 * Model
 */
class Container extends Model
{
    use Validation;
    use Sortable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_containers';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    public $belongsTo = [
        'destinationRole' => DestinationRole::class,
        'networkType' => NetworkType::class,
    ];
}
