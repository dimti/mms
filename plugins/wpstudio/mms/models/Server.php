<?php namespace Wpstudio\Mms\Models;

use Illuminate\Support\Collection;
use Jacob\Logbook\Traits\LogChanges;
use Model;
use Winter\Storm\Database\Relations\BelongsTo;
use Winter\Storm\Database\Relations\BelongsToMany;
use Winter\Storm\Database\Relations\HasMany;
use Winter\Storm\Database\Traits\SoftDelete;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;
use Wpstudio\Mms\Classes\Exceptions\MmsException;
use Wpstudio\Mms\Classes\Exceptions\MmsNginxException;
use Wpstudio\Mms\Classes\Nginx\NginxMasterProxy;
use Wpstudio\Mms\Classes\ProxmoxServer;

/**
 *
 * @property Collection|Container[] $containers
 * @method HasMany containers
 *
 * @property ServerType $serverType
 * @method BelongsTo serverType
 *
 * @property Cluster $cluster
 * @method BelongsTo cluster
 *
 * @property Cluster $sysadmins
 * @method BelongsToMany sysadmins
 */
class Server extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;
    use LogChanges;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wpstudio_mms_servers';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    public $jsonable = [
        'cluster_node_status',
        'node_status',
        'additional_ip_addresses',
    ];

    public $hasMany = [
        'containers' => Container::class,
    ];

    public $belongsTo = [
        'serverType' => ServerType::class,
        'cluster' => Cluster::class
    ];

    public $belongsToMany = [
        'sysadmins' => [
            Sysadmin::class,
            'table' => 'wpstudio_mms_server_sysadmin',
        ],
    ];

    private ProxmoxServer $proxmoxServer;

    private NginxMasterProxy $masterNginxProxy;

    private bool $isExistsMasterNginxProxy;

    public function beforeCreate()
    {
        if ($this->hostname && !$this->main_ip_address) {
            $this->main_ip_address = gethostbyname($this->hostname);
        } else if (!$this->hostname && $this->main_ip_address) {
            $this->hostname = gethostbyaddr($this->main_ip_address);
        }
    }

    public function getProxmoxServer(): ProxmoxServer
    {
        if (!isset($this->proxmoxServer)) {
            $this->proxmoxServer = new ProxmoxServer($this);
        }

        return $this->proxmoxServer;
    }

    /**
     * @return NginxMasterProxy
     * @throws MmsException
     */
    public function getNginxMasterProxy(): NginxMasterProxy
    {
        if (!isset($this->masterNginxProxy)) {
            $this->masterNginxProxy = $this->getProxmoxServer()->getMasterNginxProxy();
        }

        return $this->masterNginxProxy;
    }

    public function hasExistsMasterNginxProxy(): bool
    {
        if (!isset($this->isExistsMasterNginxProxy)) {
            $this->isExistsMasterNginxProxy = true;

            try {
                $this->getNginxMasterProxy();
            } catch (MmsNginxException) {
                $this->isExistsMasterNginxProxy = false;
            }
        }

        return $this->isExistsMasterNginxProxy;
    }

    /**
     * @param array|null $scopes = [
     *     'cluster' => (object) [
     *          'value' => 2
     *      ]
     * ]
     * @return mixed
     */
    public function getServerOptionsDependByCluster(array $scopes = null)
    {
        if ($scopes && array_key_exists('cluster', $scopes) && $scopes['cluster']->value) {
            $clusterId = is_array($scopes['cluster']->value) ? $scopes['cluster']->value : [$scopes['cluster']->value];

            return Server::whereHas('cluster', fn($query) => $query->whereIn('hostname', $clusterId))->lists('code', 'id');
        }
        else {
            return Server::lists('code', 'id');
        }
    }
}
