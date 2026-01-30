<?php

declare(strict_types=1);

namespace PsrMock\Psr7\Entities;

use Stringable;

/**
 * @psalm-api
 */
final class Header implements Stringable
{
    public function __construct(private string $name, private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->name . ': ' . $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
