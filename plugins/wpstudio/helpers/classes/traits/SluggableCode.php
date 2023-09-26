<?php namespace wpstudio\helpers\classes\traits;

use Winter\Storm\Database\Model;

trait SluggableCode
{
    protected static array $codeToModel = [];

    protected static array $idToCode = [];

    public static function getByCode(string $code, bool $silent = false): self|bool
    {
        if (!array_key_exists($code, static::$codeToModel)) {
            $model = new self;

            assert($model instanceof Model);

            $query = $model->whereCode($code);

            if ($silent) {
                $record = $query->first();

                if (!$record) {
                    return false;
                }

                static::$codeToModel[$code] = $record;
            } else {
                static::$codeToModel[$code] = $query->firstOrFail();
            }
        }

        return static::$codeToModel[$code];
    }

    public static function getCodeById(int $id): string
    {
        if (!array_key_exists($id, static::$idToCode)) {
            $model = new self;

            assert($model instanceof Model);

            static::$idToCode[$id] = $model->whereKey($id)->firstOrFail()->code;
        }

        return static::$idToCode[$id];
    }
}