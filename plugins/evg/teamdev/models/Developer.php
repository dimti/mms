<?php

namespace Evg\Teamdev\Models;

use Model;
use Wpstudio\Mms\Models\Sysadmin;

/**
 * Model
 */
class Developer extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'evg_teamdev_developers';

    public $hasOne = [
        'sysadmin' => [
            Sysadmin::class,
            'foreignKey' => 'developer_id',
            'ownerKey'   => 'id',
        ]
    ];

    public $belongsToMany = [
        'teams' => [
            Team::class,
            'table'           => 'evg_teamdev_developer_teams',
            'foreignPivotKey' => 'developer_id',
            'relatedPivotKey' => 'team_id',
            'timestamps'      => true,
        ]
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
