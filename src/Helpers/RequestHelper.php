<?php
/**
 * Created for plugin-export-core
 * Datetime: 24.07.2019 12:24
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Helpers;


use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Components\Form\FormData;
use Slim\Http\Request;

class RequestHelper
{

    public static function getApiParams(Request $request): ApiClient
    {
        return new ApiClient(
            $request->getParsedBodyParam('api')['token'],
            $request->getParsedBodyParam('api')['endpointUrl']
        );
    }

    public static function getFormDataConfig(Request $request): FormData
    {
        return new FormData($request->getParsedBodyParam('form'));
    }

}