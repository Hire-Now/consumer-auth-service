<?php

namespace App\Domain\Traits;

use App\Domain\Attributes\Getter;
use App\Domain\Attributes\Setter;
use BadMethodCallException;
use ReflectionClass;

trait AccessorTrait
{
    public function __call($method, $args): mixed
    {
        if (($isSetter = str_starts_with($method, 'set')) || str_starts_with($method, 'get')) {
            $property = lcfirst(substr($method, 3));

            if ($isSetter) {
                if ($this->hasSetterAttribute($property)) {
                    $this->applyFromSetter($property, $args[0]);
                    return $this;
                }
            } else {
                if ($this->hasGetterAttribute($property)) {
                    return $this->$property;
                }
            }
        }

        throw new BadMethodCallException("Method $method not found.");
    }

    private function hasGetterAttribute(string $property): bool
    {
        $reflection = new ReflectionClass($this);
        return $this->hasPropertyWithAttribute($reflection, $property, Getter::class);
    }

    private function hasSetterAttribute(string $property): bool
    {
        $reflection = new ReflectionClass($this);
        return $this->hasPropertyWithAttribute($reflection, $property, Setter::class);
    }

    private function hasPropertyWithAttribute(ReflectionClass $reflection, string $property, string $attribute): bool
    {
        if (!$reflection->hasProperty($property)) {
            return false;
        }
        $propertyReflection = $reflection->getProperty($property);
        return count($propertyReflection->getAttributes($attribute)) > 0;
    }

    private function applyFromSetter(string $property, mixed $value): void
    {
        $applier = "apply" . ucfirst($property);

        $newValue = $value;
        if (method_exists($this, $applier)) {
            $newValue = $this->{$applier}($value);
        }
        $this->$property = $newValue;
    }
}
