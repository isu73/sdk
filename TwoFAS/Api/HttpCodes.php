<?php

namespace TwoFAS\Api;

/**
 * List of http status codes.
 *
 * @package TwoFAS\Api
 */
class HttpCodes
{
    const OK                = 200;
    const CREATED           = 201;
    const NO_CONTENT        = 204;
    const BAD_REQUEST       = 400;
    const UNAUTHORIZED      = 401;
    const PAYMENT_REQUIRED  = 402;
    const FORBIDDEN         = 403;
    const NOT_FOUND         = 404;
    const NOT_ACCEPTABLE    = 406;
    const TOO_MANY_REQUESTS = 429;
}
