<?php

namespace GoWeb\ClientAPI\Query;

class Auth extends \GoWeb\ClientAPI\Query
{
    protected $_url = 'users/authorize';

    protected $_responseModel = 'GoWeb\Api\Model\Client';

    const ERROR_NONE                                    = 0;
    const ERROR_GENERIC_SERVER_ERROR                    = 1;
    const ERROR_WRONG_CREDENTIALS                       = 2;
    const ERROR_ACCOUNT_BLOCKED                         = 3;
    const ERROR_EMAIL_NOT_CONFIRMED                     = 4;
    const ERROR_CLIENT_VERSION_NOT_SUPPORTED            = 5;
    const ERROR_NO_ACTIVE_SERVICES_FOUND                = 6;
    public function setIp($ip)
    {
        $this->setParam('ip', $ip);

        return $this;
    }

    public function byEmail($email, $password)
    {
        $this->setParam('email', $email);
        $this->setParam('password', $password);

        return $this;
    }

    public function remember($remember = true)
    {
        $this->setParam('remember', (int) $remember );

        return $this;
    }

    public function byPermanentId($permId)
    {
        $this->setParam('permid', $permId);

        return $this;
    }

    public function send()
    {
        /**
         * Send auth request
         */
        try
        {
            $activeUser = parent::send();
        }
        catch(\GoWeb\ClientAPI\Query\Exception\Common $e)
        {
            $rawResponse = $this->getRawResponse();

            $statusExceptionMap = array
            (
                self::ERROR_GENERIC_SERVER_ERROR                    => 'GenericServerError',
                self::ERROR_WRONG_CREDENTIALS                       => 'WrongCredentials',
                self::ERROR_ACCOUNT_BLOCKED                         => 'AccountBlocked',
                self::ERROR_EMAIL_NOT_CONFIRMED                     => 'EmailNotConfirmed',
                self::ERROR_CLIENT_VERSION_NOT_SUPPORTED            => 'ClientVersionNotSupported',
                self::ERROR_NO_ACTIVE_SERVICES_FOUND                => 'NoActiveServicesFound',
            );

            // throw generic exception
            if(!isset($statusExceptionMap[$rawResponse['status']])) {
                throw new Auth\Exception('Unknown server error with status code : ' . json_encode($rawResponse)  );
            }

            // throw defined exception
            $exceptionClass = '\GoWeb\ClientAPI\Query\Auth\Exception\\' . $statusExceptionMap[$rawResponse['status']];
            throw new $exceptionClass($rawResponse['errorMessage']);
        }

        /**
         * Set active user
         */
        $this->_clientAPI->setActiveUser($activeUser);

        return $activeUser;
    }

}
