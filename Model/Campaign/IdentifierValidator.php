<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Model\Campaign;

/**
 * URL-key rules: lowercase letters, digits, hyphens. Max 64 characters.
 */
class IdentifierValidator
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public const MAX_LENGTH = 64;

    public function isValid(string $identifier): bool
    {
        if ($identifier === '' || strlen($identifier) > self::MAX_LENGTH) {
            return false;
        }
        return preg_match(self::PATTERN, $identifier) === 1;
    }
}
