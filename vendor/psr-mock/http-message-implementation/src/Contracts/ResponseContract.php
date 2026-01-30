<?php

declare(strict_types=1);

namespace PsrMock\Psr7\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseContract extends ResponseInterface
{
    public function getReasonPhrase(): string;

    public function getStatusCode(): int;

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface;
}
