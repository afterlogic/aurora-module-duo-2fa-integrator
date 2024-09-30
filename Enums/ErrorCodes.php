<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\Duo2FAIntegrator\Enums;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2024, Afterlogic Corp.
 */
class ErrorCodes
{
    public const NoError = 0;
    public const NoSavedState = 1;
    public const StateDoesNotMatch = 2;
    public const ErrorDecoding = 3;
    public const CallbackError = 4;
    public const NoAssociatedDuoUser = 5;

    /**
     * @var array
     */
    protected $aConsts = [
        'NoError' => self::NoError,
        'NoSavedState' => self::NoSavedState,
        'StateDoesNotMatch' => self::StateDoesNotMatch,
        'ErrorDecoding' => self::ErrorDecoding,
        'CallbackError' => self::CallbackError,
        'NoAssociatedDuoUser' => self::NoAssociatedDuoUser,
    ];
}
