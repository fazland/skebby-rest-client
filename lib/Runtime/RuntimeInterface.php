<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Runtime;

interface RuntimeInterface
{
    /**
     * Checks if the class has been defined.
     */
    public function classExists(string $fqcn, bool $autoload = true): bool;

    /**
     * Find out whether an extension is loaded.
     */
    public function extensionLoaded(string $extension): bool;
}
