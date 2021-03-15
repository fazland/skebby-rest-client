<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Runtime;

use function class_exists;
use function extension_loaded;

class Runtime implements RuntimeInterface
{
    /**
     * {@inheritdoc}
     */
    public function classExists(string $fqcn, bool $autoload = true): bool
    {
        return class_exists($fqcn, $autoload);
    }

    /**
     * {@inheritdoc}
     */
    public function extensionLoaded(string $extension): bool
    {
        return extension_loaded($extension);
    }
}
