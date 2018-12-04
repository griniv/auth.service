<?php
declare(strict_types=1);

namespace AuthService\Controller;

use AuthService\Controller\Error\BadRequest;
use AuthService\Controller\Error\ControllerError;
use AuthService\ResponseFactory;
use CaseHelper\CaseHelperFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

abstract class AbstractController
{
    use ResponseFactory;

    /**
     * @param string $name
     * @param array  $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __call(string $name, array $args): ResponseInterface
    {
        $request = $this->getRequest($args);
        $data = array_merge(
            $request->getQueryParams(),
            (array) $request->getParsedBody(),
            $request->getAttributes()
        );

        $r = new ReflectionMethod($this, $name);
        try {
            $params = $this->getParams($r, $data);
            $result = call_user_func_array([$this, $name], $params);
        } /** @noinspection PhpRedundantCatchClauseInspection */
        catch (ControllerError $e) {
            return $this->createResponse(['message' => $e->getMessage()], $e->getCode());
        }

        return $this->createResponse($result);
    }

    private function getRequest(array $args): ServerRequestInterface
    {
        return $args[0];
    }

    /**
     * @param ReflectionMethod $r
     * @param array            $data
     *
     * @return array
     * @throws BadRequest
     * @throws \Exception
     */
    private function getParams(ReflectionMethod $r, array $data): array
    {
        $params = [];

        foreach ($r->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            } else {
                $name = CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_CAMEL_CASE)->toSnakeCase($name);
                if (array_key_exists($name, $data)) {
                    $value = $data[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $value = $param->getDefaultValue();
                } else {
                    throw new BadRequest("Missing [{$name}] parameter");
                }
            }

            if ($param->getType()->isBuiltin()) {
                settype($value, $param->getType()->getName());
            }

            $params[] = $value;
        }

        return $params;
    }
}