<?php

namespace RedberryProducts\CryptoWallet\Drivers\Bitgo\Data;

use Illuminate\Support\Str;

abstract class Data
{
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
                if (is_array($value)) {
                    //use $key as class name and call fromArray static method
                    $dtoClass = Str::studly($key);
                    //find class in the same namespace where the extending class is
                    $dtoClass = '\\'.__NAMESPACE__.'\\'.$dtoClass;

                    //if class exists
                    if (class_exists($dtoClass)) {
                        $dto->$key = $dtoClass::fromArray($value);
                    } else {
                        $dto->$key = $value;
                    }

                } else {
                    $dto->$key = $value;
                }
            }
        }

        return $dto;
    }

    /**
     * convert data object to array
     */
    public function toArray(bool $filter = false): array
    {
        $arr = (array) $this;
        if ($filter) {
            $arr = array_filter($arr);
        }
        array_walk_recursive($arr, function (&$item) use ($filter) {
            if (is_object($item)) {
                $item->toArray();
                $item = (array) $item;
                if ($filter) {
                    $item = array_filter($item);
                }
            }
        });

        return $arr;
    }
}
