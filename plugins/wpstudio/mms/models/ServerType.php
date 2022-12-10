<?php namespace Wpstudio\Mms\Models;

use Illuminate\Support\Collection;
use Model;
use Winter\Storm\Database\Relations\HasMany;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;

/**
 * @property Collection|Server[] $servers
 * @method HasMany servers
 */
class ServerType extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;

    const CODE_PROXMOX = 'proxmox';
    const CODE_BARE_METAL = 'bare-metal';
    const CODE_CAPROVER = 'caprover';

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_server_types';

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
        'servers' => Server::class
    ];

    public static function getProxmoxServerType(): self
    {
        return self::whereCode(self::CODE_PROXMOX)->firstOrFail();
    }

    public static function getBareMetalServerType(): self
    {
        return self::whereCode(self::CODE_BARE_METAL)->firstOrFail();
    }

    public static function getCaproverServerType(): self
    {
        return self::whereCode(self::CODE_CAPROVER)->firstOrFail();
    }
}
