<?php

namespace Evg\Teamdev\Models;

use Model;

/**
 * Model
 */
class Team extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;


    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'evg_teamdev_teams';

    public $belongsToMany = [
        'developers' => [
            Developer::class,
            'table'           => 'evg_teamdev_developer_teams',
            'foreignPivotKey' => 'team_id',
            'relatedPivotKey' => 'developer_id',
            'timestamps'      => true,
        ]
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
