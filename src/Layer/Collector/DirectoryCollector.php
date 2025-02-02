<?php

declare(strict_types=1);

namespace Qossmic\Deptrac\Layer\Collector;

use LogicException;
use Qossmic\Deptrac\Ast\AstMap\AstMap;
use Qossmic\Deptrac\Ast\AstMap\TokenReferenceInterface;
use Symfony\Component\Filesystem\Path;

final class DirectoryCollector extends RegexCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference, AstMap $astMap): bool
    {
        $fileReference = $reference->getFileReference();

        if (null === $fileReference) {
            return false;
        }

        $filePath = $fileReference->getFilepath();
        $validatedPattern = $this->getValidatedPattern($config);
        $normalizedPath = Path::normalize($filePath);

        return 1 === preg_match($validatedPattern, $normalizedPath);
    }

    protected function getPattern(array $config): string
    {
        if (isset($config['regex']) && !isset($config['value'])) {
            trigger_deprecation('qossmic/deptrac', '0.20.0', 'ClassNameCollector should use the "value" key from this version');
            $config['value'] = $config['regex'];
        }

        if (!isset($config['value']) || !is_string($config['value'])) {
            throw new LogicException('DirectoryCollector needs the regex configuration.');
        }

        return '#'.$config['value'].'#i';
    }
}
