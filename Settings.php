<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\Duo2FAIntegrator;

use Aurora\System\SettingsProperty;

/**
 * @property bool $Disabled
 * @property string $ClientId
 * @property string $ClientSecret
 * @property string $ApiHost
 */

class Settings extends \Aurora\System\Module\Settings
{
    protected function initDefaults()
    {
        $this->aContainer = [
            "Disabled" => new SettingsProperty(
                false,
                "bool",
                null,
                "Setting to true disables the module",
            ),
            "ClientId" => new SettingsProperty(
                '',
                "string",
                null,
                "The Client ID found in the admin panel",
            ),
            "ClientSecret" => new SettingsProperty(
                '',
                "string",
                null,
                "The Client Secret found in the admin panel",
            ),
            "ApiHost" => new SettingsProperty(
                '',
                "string",
                null,
                "The api-host found in the admin panel",
            ),
            "IncludeInMobile" => new SettingsProperty(
                true,
                "bool",
                null,
                "If true, the module is used in mobile version of the interface"
            ),
        ];
    }
}
