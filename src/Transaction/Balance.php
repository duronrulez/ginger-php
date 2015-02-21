<?php

namespace GingerPayments\Payment\Transaction;

use GingerPayments\Payment\Common\ChoiceBasedValueObject;
use GingerPayments\Payment\Common\StringBasedValueObject;

final class Balance
{
    use StringBasedValueObject, ChoiceBasedValueObject;

    /**
     * Possible transaction value values
     */
    const INTERNAL = 'internal';
    const EXTERNAL = 'external';
    const TEST = 'test';

    /**
     * @return array
     */
    public static function possibleValues()
    {
        return array(
            self::INTERNAL,
            self::EXTERNAL,
            self::TEST
        );
    }

    /**
     * @return bool
     */
    public function isInternal()
    {
        return $this->value === self::INTERNAL;
    }

    /**
     * @return bool
     */
    public function isExternal()
    {
        return $this->value === self::EXTERNAL;
    }

    /**
     * @return bool
     */
    public function isTest()
    {
        return $this->value === self::TEST;
    }
}