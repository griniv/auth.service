<?php

declare(strict_types = 1);

namespace AuthService\Controller;

use AuthService\Controller\Error\BadRequest;
use AuthService\Controller\Error\ControllerError;
use AuthService\ResponseFactory;
use CaseHelper\CaseHelperFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

abstract class AbstractController {

    use ResponseFactory;
    
    /**
     * @var ServerRequestInterface 
     */
    private $request;
    
    /**     
     * @var array 
     */
    private $rules;

    /**
     * @param ServerRequestInterface $request
     * @param array $rules
     */
    public function setRules(ServerRequestInterface $request, array $rules): void {
        $this->request = $request;
        $this->rules = $rules;
    }

    /**
     * @param type $name
     * @return mixed
     * @throws BadRequest
     */
    public function getParam($name) {
        if (!array_key_exists($name, $this->rules)) {
            throw new BadRequest("Rules for parameter [{$name}] are not defined");
        }
        list($contextName, $pattern) = $this->rules[$name];
        
        $request = $this->request;
        $context = ($contextName == 'any') ? array_merge(
                        $request->getQueryParams(), (array) $request->getParsedBody(), $request->getAttributes()
                ) : (array) $request->{'get' . $contextName}();

        if (!array_key_exists($name, $context)) {
            throw new BadRequest("Missing [{$name}] parameter");
        }

        $param = $context[$name];
        if (isset($pattern) && !preg_match($pattern, $param)) {
            throw new BadRequest("Invalid [{$name}] parameter");
        }

        return $param;
    }

}
