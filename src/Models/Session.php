<?php
/**
 * Created for plugin-core
 * Date: 02.03.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Core\Macros\Models;


use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\Db\Model;
use Leadvertex\Plugin\Components\Form\FormData;
use Leadvertex\Plugin\Components\Handshake\Registration;
use Leadvertex\Plugin\Core\Macros\Exceptions\SessionException;
use Leadvertex\Plugin\Core\Macros\Exceptions\TokenException;
use Leadvertex\Plugin\Core\Macros\Components\InputToken;

/**
 * Class Session
 * @package Leadvertex\Plugin\Core\Macros\Models
 *
 * @property InputToken $token
 * @property string $lang
 * @property ApiFilterSortPaginate $fsp
 * @property array options
 */
class Session extends Model
{

    /** @var Settings */
    private $settings;

    /** @var self|null */
    private static $current;

    public function __construct(InputToken $token, ApiFilterSortPaginate $fsp, string $lang)
    {
        parent::__construct($token->getInputToken()->getClaim('jti'));

        $this->token = $token;
        $this->lang = $lang;
        $this->fsp = $fsp;

        $this->options = [];

        if ($token->getInputToken()->getClaim('cid') != $this->getCompanyId()) {
            throw new TokenException('Mismatch token company ID and current company ID');
        }
    }

    public function getToken(): InputToken
    {
        return $this->token;
    }

    public function getRegistration(): Registration
    {
        return $this->token->getRegistration();
    }

    public function getSettings(): Settings
    {
        if (is_null($this->settings)) {
            $registration = $this->getRegistration();
            $this->settings = Settings::findById($registration->getId(), $registration->getFeature());
            if (is_null($this->settings)) {
                $this->settings = new Settings($registration->getId(), $registration->getFeature());
            }
        }
        return $this->settings;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getFsp(): ApiFilterSortPaginate
    {
        return $this->fsp;
    }

    public function getApiClient(): ApiClient
    {
        return new ApiClient(
            $this->token->getInputToken()->getClaim('iss') . 'companies/stark-industries/CRM',
            (string) $this->token->getOutputToken()
        );
    }

    public function getOptions(int $number): FormData
    {
        return $this->options[$number] ?? new FormData([]);
    }

    public function setOptions(int $number, FormData $data)
    {
        $options = $this->options;
        $options[$number] = $data;
        $this->options = $options;
    }

    public static function start(self $session)
    {
        $current = self::$current ? (string) self::$current->getToken()->getInputToken() : null;
        $new = (string) $session->getToken()->getInputToken();

        if ($current && $current !== $new) {
            throw new SessionException('Current session already exists', 1);
        }

        self::$current = $session;
    }

    public static function current(): self
    {
        if (is_null(self::$current)) {
            throw new SessionException('No started session', 404);
        }

        return self::$current;
    }

}