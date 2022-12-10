<?php namespace Wpstudio\Mms\Models;

use Illuminate\Support\Collection;
use Model;
use Winter\Storm\Database\Relations\HasMany;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;

/**
 * @property Collection|Container[] $containers
 * @method HasMany containers
 */
class DestinationRole extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;

    const CODE_NGINX_MASTER_PROXY = 'nginx-master-proxy';
    const CODE_SITE = 'site';
    const CODE_DATABASE = 'database';
    const CODE_REDIS = 'redis';
    const CODE_S3= 's3';

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

    public static function getMasterNginxProxyDestinationRole(): DestinationRole
    {
        return DestinationRole::whereCode(self::CODE_NGINX_MASTER_PROXY)->firstorFail();
    }
}
