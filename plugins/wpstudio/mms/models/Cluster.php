<?php namespace Wpstudio\Mms\Models;

use Illuminate\Support\Collection;
use Jacob\Logbook\Traits\LogChanges;
use Model;
use Winter\Storm\Database\Relations\HasMany;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Validation;

/**
 * @property Collection $servers
 * @method HasMany servers
 */
class Cluster extends Model
{
    use Validation;
    use SoftDelete;
    use LogChanges;

    const AUTH_TYPE_PAM = 0;
    const AUTH_TYPE_PVE = 1;

    public static $authTypeLabels = [
        self::AUTH_TYPE_PAM => 'pam',
        self::AUTH_TYPE_PVE => 'pve',
    ];

    const ATTRIBUTE_DEFAULT_USERNAME = 'root';
    const ATTRIBUTE_DEFAULT_PORT = '8006';
    const ATTRIBUTE_DEFAULT_AUTH_TYPE = self::AUTH_TYPE_PAM;

    protected $dates = ['deleted_at'];

    protected $fillable = ['code'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_clusters';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [
        'cluster_status',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public $hasMany = [
        'servers' => Server::class,
    ];

    public function getAuthTypeAttribute(): string
    {
        if ($this->attributes['auth_type'] == self::AUTH_TYPE_PVE) {
            return static::$authTypeLabels[self::AUTH_TYPE_PVE];
        }

        return static::$authTypeLabels[self::AUTH_TYPE_PAM];
    }

    public function getUsernameAttribute(): string
    {
        return $this->attributes['username'] ? : self::ATTRIBUTE_DEFAULT_USERNAME;
    }

    public function getPortAttribute(): string
    {
        return $this->attributes['port'] ? : self::ATTRIBUTE_DEFAULT_PORT;
    }

    public $belongsToMany = [
        'sysadmins' => [
            Sysadmin::class,
            'table' => 'wpstudio_mms_cluster_sysadmin',
        ],
    ];
}
