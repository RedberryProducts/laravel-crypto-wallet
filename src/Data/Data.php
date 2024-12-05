<?php

namespace RedberryProducts\CryptoWallet\Data;

abstract class Data
{
    public object|array $driverObject;

    //set properties from constructor
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function fromArray(array $payload): static
    {
        $dataClass = static::class;
        $dto = new $dataClass;
        foreach ($payload as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    /**
     * convert data object to array
     */
    public function toArray(): array
    {
        $arr = (array) $this;
        array_walk_recursive($arr, function (&$item) {
            if (is_object($item)) {
                $item->toArray();
                $item = (array) $item;
            }
        });

        return $arr;
    }
}
