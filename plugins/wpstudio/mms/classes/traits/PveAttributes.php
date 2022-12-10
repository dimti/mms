<?php namespace Wpstudio\Mms\Classes\Traits;

/**
 * @property array $more
 * @property array $enums => [
 *     'keyName' => 'EnumClassName'
 * ]
 */
trait PveAttributes
{
    protected function prepareDataAttributes(array $data, array $exclude = []): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }

            if (property_exists($this, $key)) {
                if (property_exists($this, 'enums') && array_key_exists($key, $this->enums)) {
                    $enumClassName = $this->enums[$key];

                    $this->$key = $enumClassName::tryFrom($value) ?? $value;
                } else {
                    $this->$key = $value;
                }
            } else {
                $this->more[$key] = $value;
            }
        }
    }

    public function toArray(): array
    {
        $scalarProperties = collect((array)$this)
            ->filter(
                fn($value, $key) => is_scalar($value) &&
                    (!property_exists($this, 'enums') || !array_key_exists($key, $this->enums))
            );

        $scalarProperties->offsetSet('more', json_encode($this->more));

        return $scalarProperties->toArray();
    }
}
