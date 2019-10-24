<?php

namespace App\Enum;

/**
 * Class MessagesEnum.
 *
 * @package App\Enum
 */
abstract class MessagesEnum
{
    const INVALID_OPERATION = 'Unsupported operation type (%s) specified.';
    const INVALID_CURRENCY = 'Unsupported currency (%s) specified.';
    const INVALID_PERSON = 'Unsupported person type (%s) specified.';

    const EXCEPTION_MISSING_IMPLEMENTATION = 'No implementation found for plugin "Fee Manager".';
    const EXCEPTION_MISSING_PERSON = 'Cash-Out operations are bound to person type. None given.';
}
