<?php

declare(strict_types=1);

namespace Qossmic\Deptrac\Finder\Exception;

use Qossmic\Deptrac\Exception\ExceptionInterface;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use function sprintf;

final class FileAlreadyExistsException extends RuntimeException implements ExceptionInterface
{
    public static function alreadyExists(SplFileInfo $file): self
    {
        return new self(sprintf('A file named "%s" already exists.', Path::canonicalize($file->getPathname())));
    }
}
