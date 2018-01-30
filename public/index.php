<?php declare(strict_types=1);

require_once('../src/vendor/autoload.php');

use GBV\PicaHelp\Database;
use GBV\PicaHelp\Field;
use GBV\PicaHelp\NotFoundException;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


$response = new JsonResponse();
$request = Request::createFromGlobals();

try {

    // load configuration
    $configFile = __DIR__ . '/../config/picahelp.json';
    if (!file_exists($configFile)) {
        throw new \RuntimeException("config file not found: $configFile");
    }

    $file = file_get_contents($configFile);
    $config = json_decode($file, true);

    $db = new Database($config['database'] ?? []);

    // Check HTTP method.
    $method = $request->getMethod();
    if ($method != 'GET') {
        throw new \Exception('', 405);
    }

    // Check path info if empty load full list, else small block
    $field = new Field($request->getPathInfo(), $db);
    if ($field->isPica3()) {
        $url = rtrim($request->getBaseUrl(), '/').'/' . $field->getName();
        $response = new RedirectResponse($url);
    } else {
        $response->setData($field->getData());
    }

} catch (\Exception $e) {
    error_log("$e");

    $code = $e->getCode();
    if (!isset(JsonResponse::$statusTexts[$code])) {
        $code = 503;
    }
    $message = JsonResponse::$statusTexts[$code];

    $response->setStatusCode($code);
    $response->setData(['error' => ['code' => $code, 'message' => $message]]);
}

// send response
$response->setEncodingOptions( $response->getEncodingOptions() | JSON_PRETTY_PRINT );
$response->headers->set('Access-Control-Allow-Origin', '*');
$response->prepare($request);
$response->send();
