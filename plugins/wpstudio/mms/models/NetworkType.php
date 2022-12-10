<?php namespace Wpstudio\Mms\Models;

use Illuminate\Support\Collection;
use Model;
use Winter\Storm\Database\Relations\HasMany;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;

/**
 *
 * @property Collection|Container[] $containers
 * @method HasMany containers
 */
class NetworkType extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;

    const CODE_DIRECT = 'direct';
    const CODE_INNER = 'inner';

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_network_types';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [];

    public $hasMany = [
        'containers' => Container::class,
    ];
}
