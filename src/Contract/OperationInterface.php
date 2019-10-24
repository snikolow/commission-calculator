<?php

namespace App\Contract;

/**
 * Interface OperationInterface.
 *
 * @package App\Contract
 */
interface OperationInterface
{
    /**
     * Adds new operation type to the white-list.
     *
     * @param string $operation
     *   The operation type.
     *
     * @return \App\Contract\OperationInterface
     *   The instance of the class.
     */
    public function addOperation(string $operation): OperationInterface;

    /**
     * Indicates whether or not given operation is valid.
     *
     * @param string $operation
     *   The operation type.
     *
     * @return boolean
     *   The validation result.
     */
    public function isValidOperation(string $operation): bool;
}
