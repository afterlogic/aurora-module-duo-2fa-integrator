<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\Duo2FAIntegrator;

use Aurora\Modules\Core\Models\User;
use Aurora\System\Api;
use Duo\DuoUniversal\Client;
use Duo\DuoUniversal\DuoException;
use Aurora\System\Router;
use Aurora\System\Session;
use DuoUsers;

include_once 'users.php';

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @property Settings $oModuleSettings
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
    protected $client = null;

    /**
     * @return Module
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * @return Module
     */
    public static function Decorator()
    {
        return parent::Decorator();
    }

    /**
     * @return Settings
     */
    public function getModuleSettings()
    {
        return $this->oModuleSettings;
    }

    public function init()
    {
        $this->aErrors = [
            Enums\ErrorCodes::NoSavedState	=> 'No saved state please login again',
            Enums\ErrorCodes::StateDoesNotMatch	=> 'Duo state does not match saved state',
            Enums\ErrorCodes::ErrorDecoding	=> 'Error decoding Duo result. Confirm device clock is correct.',
            Enums\ErrorCodes::CallbackError	=> 'Callback error.',
        ];

        Router::getInstance()->register(self::GetName(), 'duo-callback', [$this, 'EntryDuoCallback']);

        $this->subscribeEvent('Core::Login::after', [$this, 'onAfterLogin'], 200);
    }

    protected function getClient()
    {
        if ($this->client === null) {
            try {
                $this->client = new Client(
                    $this->oModuleSettings->ClientId,
                    $this->oModuleSettings->ClientSecret,
                    $this->oModuleSettings->ApiHost,
                    \MailSo\Base\Http::SingletonInstance()->GetFullUrl() . 'modules/' . $this->GetName() . '/callback.php',
                    true,
                );
            } catch (DuoException $e) {
                throw new \Exception("*** Duo config error. Verify the settings are correct ***\n" . $e->getMessage());
            }
        }

        return $this->client;
    }

    /**
     * @param array $aArgs
     * @param array $mResult
     */
    public function onAfterLogin($aArgs, &$mResult)
    {
        if ($mResult && is_array($mResult) && isset($mResult['AuthToken'])) {
            $authToken = $mResult['AuthToken'];
            $oUser = Api::getAuthenticatedUser($mResult['AuthToken']);
            unset($mResult['AuthToken']);
            if ($oUser instanceof User) {
                $client = $this->getClient();
                if ($client) {
                    try {
                        $client->healthCheck();
                    } catch (DuoException $e) {
                        Api::LogException($e);
                        $mResult = false;
                    }
                
                    # Generate random string to act as a state for the exchange.
                    # Store it in the session to be later used by the callback.
                    # This example demonstrates use of the http session (cookie-based) 
                    # for storing the state. In some applications, strict cookie 
                    # controls or other session security measures will mean a different
                    # mechanism to persist the state and username will be necessary.
                    $state = $client->generateState();
                    Session::Set("State", $state);
                    Session::Set("AuthToken", $authToken);

                    $username = $oUser->PublicId;
                    $username = DuoUsers::getUser($username);

                    if (!empty($username)) {
                        # Redirect to prompt URI which will redirect to the client's redirect URI after 2FA
                        $mResult['DuoUri'] = $client->createAuthUrl($username, $state);
                    } else {
                        Api::Log('Username not specified');
                        $mResult = false;
                    }
                }
            }
        }
    }

    public function EntryDuoCallback()
    {
        $error = $this->oHttp->GetQuery('error');

        # Check for errors from the Duo authentication
        if ($error) {
            $error_description = $this->oHttp->GetQuery('error_description');
            Api::Log($error . ":" . $error_description);
            $error = Enums\ErrorCodes::CallbackError;
        } else {
            # Get authorization token to trade for 2FA
            $code = $this->oHttp->GetQuery('duo_code');

            # Get state to verify consistency and originality
            $state = $this->oHttp->GetQuery('state');

            $saved_state = Session::get("State");
            $authToken = Session::get("AuthToken");

            Session::clear("State");
            Session::clear("AuthToken");

            $error = Enums\ErrorCodes::NoError;

            $oUser = Api::getAuthenticatedUser($authToken);
            if ($oUser) {
                $username = $oUser->PublicId;
                $username = DuoUsers::getUser($username);

                if (empty($saved_state) || empty($username)) {
                    # If the URL used to get to login.php is not localhost, (e.g. 127.0.0.1), then the sessions will be different
                    # and the localhost session will not have the state.
                    $error = Enums\ErrorCodes::NoSavedState;
                } else if ($state != $saved_state) { # Ensure nonce matches from initial request
                    $error = Enums\ErrorCodes::StateDoesNotMatch;
                } else {
                    try {
                        $client =  $this->getClient();
                        $decoded_token = $client->exchangeAuthorizationCodeFor2FAResult($code, $username);
                        //dd($decoded_token);

                        @setcookie(\Aurora\System\Application::AUTH_TOKEN_KEY, $authToken);
                        Api::Location(\MailSo\Base\Http::SingletonInstance()->GetFullUrl());
                    } catch (DuoException $e) {
                        $error = Enums\ErrorCodes::ErrorDecoding;
                        Api::LogException($e);
                    }
                }
            } else {
                $error = Enums\ErrorCodes::NoSavedState;
            }
        }

        if ($error !== Enums\ErrorCodes::NoError) {
            Api::Location(\MailSo\Base\Http::SingletonInstance()->GetFullUrl() . '?error=' . $error . '&module=' . self::GetName());
        }
    }
}