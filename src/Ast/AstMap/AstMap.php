<?php

declare(strict_types=1);

namespace Qossmic\Deptrac\Ast\AstMap;

use ArrayObject;
use Qossmic\Deptrac\Ast\AstMap\ClassLike\ClassLikeReference;
use Qossmic\Deptrac\Ast\AstMap\ClassLike\ClassLikeToken;
use Qossmic\Deptrac\Ast\AstMap\File\FileReference;
use Qossmic\Deptrac\Ast\AstMap\File\FileToken;
use Qossmic\Deptrac\Ast\AstMap\FunctionLike\FunctionLikeReference;
use Qossmic\Deptrac\Ast\AstMap\FunctionLike\FunctionLikeToken;
use SplStack;

class AstMap
{
    /**
     * @var array<string, ClassLikeReference>
     */
    private array $classReferences = [];

    /**
     * @var array<string, FileReference>
     */
    private array $fileReferences = [];

    /**
     * @var array<string, FunctionLikeReference>
     */
    private array $functionReferences = [];

    /**
     * @param FileReference[] $astFileReferences
     */
    public function __construct(array $astFileReferences)
    {
        foreach ($astFileReferences as $astFileReference) {
            $this->addAstFileReference($astFileReference);
        }
    }

    /**
     * @return ClassLikeReference[]
     */
    public function getClassLikeReferences(): array
    {
        return $this->classReferences;
    }

    /**
     * @return FileReference[]
     */
    public function getFileReferences(): array
    {
        return $this->fileReferences;
    }

    /**
     * @return FunctionLikeReference[]
     */
    public function getFunctionLikeReferences(): array
    {
        return $this->functionReferences;
    }

    public function getClassReferenceForToken(ClassLikeToken $className): ?ClassLikeReference
    {
        return $this->classReferences[$className->toString()] ?? null;
    }

    public function getFunctionReferenceForToken(FunctionLikeToken $tokenName): ?FunctionLikeReference
    {
        return $this->functionReferences[$tokenName->toString()] ?? null;
    }

    public function getFileReferenceForToken(FileToken $tokenName): ?FileReference
    {
        return $this->fileReferences[$tokenName->toString()] ?? null;
    }

    /**
     * @return iterable<AstInherit>
     */
    public function getClassInherits(ClassLikeToken $classLikeName): iterable
    {
        $classReference = $this->getClassReferenceForToken($classLikeName);

        if (null === $classReference) {
            return [];
        }

        foreach ($classReference->getInherits() as $dep) {
            yield $dep;
            yield from $this->recursivelyResolveDependencies($dep);
        }
    }

    /**
     * @param ArrayObject<string, true>|null $alreadyResolved
     * @param SplStack<AstInherit>           $pathStack
     *
     * @return iterable<AstInherit>
     */
    private function recursivelyResolveDependencies(
        AstInherit $inheritDependency,
        ArrayObject $alreadyResolved = null,
        SplStack $pathStack = null
    ): iterable {
        /** @var ArrayObject<string, true> $alreadyResolved */
        $alreadyResolved = $alreadyResolved ?? new ArrayObject();

        if (null === $pathStack) {
            /** @var SplStack<AstInherit> $pathStack */
            $pathStack = new SplStack();
            $pathStack->push($inheritDependency);
        }

        $className = $inheritDependency->getClassLikeName()->toString();

        if (isset($alreadyResolved[$className])) {
            $pathStack->pop();

            return [];
        }

        $classReference = $this->getClassReferenceForToken($inheritDependency->getClassLikeName());

        if (null === $classReference) {
            return [];
        }

        foreach ($classReference->getInherits() as $inherit) {
            $alreadyResolved[$className] = true;

            /** @var AstInherit[] $path */
            $path = iterator_to_array($pathStack);
            yield $inherit->withPath($path);

            $pathStack->push($inherit);

            yield from $this->recursivelyResolveDependencies($inherit, $alreadyResolved, $pathStack);

            unset($alreadyResolved[$className]);
            $pathStack->pop();
        }
    }

    private function addClassLike(ClassLikeReference $astClassReference): void
    {
        $this->classReferences[$astClassReference->getToken()->toString()] = $astClassReference;
    }

    private function addAstFileReference(FileReference $astFileReference): void
    {
        $this->fileReferences[$astFileReference->getFilepath()] = $astFileReference;

        foreach ($astFileReference->getClassLikeReferences() as $astClassReference) {
            $this->addClassLike($astClassReference);
        }
        foreach ($astFileReference->getFunctionLikeReferences() as $astFunctionReference) {
            $this->addFunctionLike($astFunctionReference);
        }
    }

    private function addFunctionLike(FunctionLikeReference $astFunctionReference): void
    {
        $this->functionReferences[$astFunctionReference->getToken()->toString()] = $astFunctionReference;
    }
}
