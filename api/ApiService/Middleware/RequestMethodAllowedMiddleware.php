<?php
declare(strict_types=1);

namespace Api\ApiService\Middleware;

use Api\ApiBase\Base;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RequestMethodAllowedMiddleware
 * @package Api\ApiService\Middleware
 */
class RequestMethodAllowedMiddleware extends Base implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtolower($request->getMethod());
        if ($method === 'post') {
            return $handler->handle($request);
        }
        //TODO 记录日志
        return $this->requestFail('请求方式不允许')->withStatus(self::RESPONSE_STATUS_CODE);
    }

}

