<?php

namespace TwoFAS\Api\Exception;

/**
 * @todo extension for compatibility reasons
 * This exception will be thrown if entity with provided id is not found.
 *
 * @package TwoFAS\Api\Exception
 */
class ResourceNotFoundException extends IntegrationUserNotFoundException
{
}
