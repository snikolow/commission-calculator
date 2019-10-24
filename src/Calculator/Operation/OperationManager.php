<?php

namespace App\Calculator\Operation;

use App\Contract\OperationInterface;
use App\Enum\OperationsEnum;

/**
 * Class OperationManager.
 *
 * @package App\Calculator\Operation
 */
class OperationManager implements OperationInterface
{
    /**
     * Contains a white-list with valid operations.
     *
     * @var array
     */
    private $validOperations = [
        OperationsEnum::CASH_IN,
        OperationsEnum::CASH_OUT,
    ];

    /**
     * {@inheritdoc}
     */
    public function addOperation(string $operation): OperationInterface
    {
        if (!in_array($operation, $this->validOperations)) {
            $this->validOperations[] = $operation;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidOperation(string $operation): bool
    {
        return in_array($operation, $this->validOperations);
    }
}
