<?php

declare(strict_types=1);

namespace Qossmic\Deptrac\Result;

use Qossmic\Deptrac\Dependency\DependencyInterface;

/**
 * @psalm-immutable
 */
final class Violation implements CoveredRule
{
    private DependencyInterface $dependency;
    private string $dependerLayer;
    private string $dependentLayer;

    public function __construct(DependencyInterface $dependency, string $dependerLayer, string $dependentLayer)
    {
        $this->dependency = $dependency;
        $this->dependerLayer = $dependerLayer;
        $this->dependentLayer = $dependentLayer;
    }

    public function getDependency(): DependencyInterface
    {
        return $this->dependency;
    }

    public function getDependerLayer(): string
    {
        return $this->dependerLayer;
    }

    public function getDependentLayer(): string
    {
        return $this->dependentLayer;
    }
}
