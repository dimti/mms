<?php

namespace Evg\Teamdev\Models;

use Model;

/**
 * Model
 */
class Sysadmin extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    use \Winter\Storm\Database\Traits\SoftDelete;


    protected $dates = ['deleted_at'];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'evg_teamdev_sysadmins';

    public $belongsTo = [
        'developer' => [
            Developer::class,
            'foreignKey' => 'id',
            'ownerKey'   => 'developer_id',
        ]
    ];

    /**
     * @var array Validation rules
     */
    public
        $rules = [
    ];
}
