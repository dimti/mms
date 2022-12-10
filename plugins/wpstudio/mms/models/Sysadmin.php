<?php namespace Wpstudio\Mms\Models;

use Model;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Validation;

/**
 * Model
 */
class Sysadmin extends Model
{
    use Validation;
    use SoftDelete;

    const NICKNAME_DIMTI = 'dimti';

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_sysadmins';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = ['ssh_keys'];

    public $belongsToMany = [
        'clusters' => [
            Cluster::class,
            'table' => 'wpstudio_mms_cluster_sysadmin',
        ],
        'servers' => [
            Server::class,
            'table' => 'wpstudio_mms_server_sysadmin',
        ],
    ];

    public static function getSysadminByNickname(string $nickName): Sysadmin
    {
        return Sysadmin::whereNickname($nickName)->firstorFail();
    }
}
