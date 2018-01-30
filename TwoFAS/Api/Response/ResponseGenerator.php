<?php

namespace TwoFAS\Api\Response;

use TwoFAS\Api\Exception\Exception;

/**
 * This class converts plain data to Response object.
 *
 * @package TwoFAS\Api\Response
 */
class ResponseGenerator
{
    /**
     * @param string  $body
     * @param integer $code
     *
     * @return Response
     *
     * @throws Exception
     */
    public static function createFrom($body, $code)
    {
        if ('' === $body) {
            return new Response(array(), $code);
        }

        $decoded = @json_decode($body, true);

        if (null === $decoded) {
            throw new Exception('Invalid response. Json expected.');
        }

        return new Response($decoded, $code);
    }
}
