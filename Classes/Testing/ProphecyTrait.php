<?php

declare(strict_types=1);

namespace Prophecy\PhpUnit;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Prophecy\Exception\Doubler\DoubleException;
use Prophecy\Exception\Doubler\InterfaceNotFoundException;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

// backport for phpspec/prophecy-phpunit:^1.0
if (!trait_exists('Prophecy\\PhpUnit\\ProphecyTrait')) {
    /**
     * @mixin TestCase
     */
    trait ProphecyTrait
    {
        /**
         * @var Prophet|null
         *
         * @internal
         */
        private $prophet;

        /**
         * @var bool
         *
         * @internal
         */
        private $prophecyAssertionsCounted = false;

        /**
         * @throws DoubleException
         * @throws InterfaceNotFoundException
         *
         * @psalm-param class-string|null $classOrInterface
         *
         * @not-deprecated
         */
        protected function prophesize(?string $classOrInterface = null): ObjectProphecy
        {
            if (\is_string($classOrInterface)) {
                \assert($this instanceof TestCase);
                if (method_exists($this, 'recordDoubledType') && is_callable([$this, 'recordDoubledType'])) {
                    $this->recordDoubledType($classOrInterface);
                }
            }

            return $this->getProphet()->prophesize($classOrInterface);
        }

        /**
         * @postCondition
         */
        protected function verifyProphecyDoubles(): void
        {
            if (null === $this->prophet) {
                return;
            }

            try {
                $this->prophet->checkPredictions();
            } catch (PredictionException $e) {
                throw new AssertionFailedError($e->getMessage());
            } finally {
                $this->countProphecyAssertions();
            }
        }

        /**
         * @after
         */
        protected function tearDownProphecy(): void
        {
            if (null !== $this->prophet && !$this->prophecyAssertionsCounted) {
                // Some Prophecy assertions may have been done in tests themselves even when a failure happened before checking mock objects.
                $this->countProphecyAssertions();
            }

            $this->prophet = null;
        }

        /**
         * @internal
         */
        private function countProphecyAssertions(): void
        {
            \assert($this instanceof TestCase);
            $this->prophecyAssertionsCounted = true;

            foreach ($this->prophet->getProphecies() as $objectProphecy) {
                foreach ($objectProphecy->getMethodProphecies() as $methodProphecies) {
                    foreach ($methodProphecies as $methodProphecy) {
                        \assert($methodProphecy instanceof MethodProphecy);

                        $this->addToAssertionCount(\count($methodProphecy->getCheckedPredictions()));
                    }
                }
            }
        }

        /**
         * @internal
         */
        private function getProphet(): Prophet
        {
            if (null === $this->prophet) {
                $this->prophet = new Prophet();
            }

            return $this->prophet;
        }
    }
}
