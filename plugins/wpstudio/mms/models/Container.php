<?php namespace Wpstudio\Mms\Models;

use Jacob\Logbook\Traits\LogChanges;
use Model;
use Winter\Storm\Database\Builder;
use Winter\Storm\Database\Relations\BelongsTo;
use Winter\Storm\Database\Traits\Nullable;
use Winter\Storm\Database\Traits\Sortable;
use Winter\Storm\Database\Traits\Validation;
use Wpstudio\Mms\Classes\Exceptions;
use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\Nginx\NginxSite;
use Wpstudio\Mms\Classes\Pve\LxcStatus;

/**
 * @property DestinationRole $destinationRole
 * @method BelongsTo destinationRole
 *
 * @property NetworkType $networkType
 * @method BelongsTo networkType
 *
 * @property Server $server
 * @method BelongsTo server
 *
 * @property array $role_payload = [
 *     'nginx_site_code' => 'tw',
 * ]
 *
 * @property string $lxc_status = [
 *     'mem' => 100089856,
 * ]
 * @see LxcStatus
 */
class Container extends Model
{
    use Validation;
    use Sortable;
    use LogChanges;
    use Nullable;

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
    public $jsonable = [
        'lxc_config',
        'lxc_status',
        'mountpoints',
        'role_payload',
        'replication',
    ];

    public $nullable = [
        'description',
    ];

    public $belongsTo = [
        'destinationRole' => DestinationRole::class,
        'networkType' => NetworkType::class,
        'server' => Server::class,
    ];

    private LinuxContainer $linuxContainer;

    private NginxSite $nginxSite;

    protected $deleteLogbookAfterDelete = true;

    /**
     * @return void
     * @throws Exceptions\MmsException
     */
    public function beforeSave()
    {
        if ($this->isDirty('destination_role_id') && $this->destination_role_id == DestinationRole::getMasterNginxProxyDestinationRole()->id) {
            try {
                $nginxMasterProxy = $this->server->getNginxMasterProxy();

                throw new Exceptions\MmsException(sprintf(
                    'Try to save container with duplicate nginx master proxy role. Another container has this role is: %s (ID: %d)',
                    $nginxMasterProxy->linuxContainer->container->code,
                    $nginxMasterProxy->linuxContainer->container->id,
                ));
            } catch (Exceptions\MmsNginxException) {
                ;
            }
        }
    }

    public function getNginxSiteCode(): string
    {
        if (is_array($this->role_payload) && array_key_exists('nginx_site_code', $this->role_payload) && $this->role_payload['nginx_site_code']) {
            return $this->role_payload['nginx_site_code'];
        } else {
            return $this->name;
        }
    }

    public function getLinuxContainer(): LinuxContainer
    {
        if (!isset($this->linuxContainer)) {
            $this->linuxContainer = new LinuxContainer($this);
        }

        return $this->linuxContainer;
    }

    /**
     * @return NginxSite
     * @throws Exceptions\MmsException
     */
    public function getNginxSite(): NginxSite
    {
        if (!isset($this->nginxSite)) {
            $this->nginxSite = new NginxSite($this->server->getNginxMasterProxy(), $this->getNginxSiteCode());
        }

        return $this->nginxSite;
    }

    public function hasExistsReplicaOnTarget(string $targetServerCode): bool
    {
        return !!$this->getLinuxContainer()->replication->where('target', '=', $targetServerCode)->count();
    }

    public function scopeCluster(Builder $query, int|array $clusterId): void
    {
        if (!is_array($clusterId)) {
            $clusterId = [$clusterId];
        }

        $query->whereHas('server', fn($query) => $query->whereIn('cluster_id', $clusterId));
    }
}
