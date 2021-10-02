<?php

namespace Bermuda\Router;

trait AttributeNormalizer
{
    /**
     * @param string $segment
     * @return bool
     */
    private function isOptional(string $segment): bool
    {
        return $segment[0] === '?';
    }

    /**
     * @param string $segment
     * @return bool
     */
    private function isAttribute(string $segment): bool
    {
        if (empty($segment)) {
            return false;
        }

        return ($segment[0] === '{' || ($segment[0] === '?' && $segment[1] === '{')) && $segment[mb_strlen($segment) - 1] === '}';
    }

    /**
     * @param string $placeholder
     * @return string
     */
    private function normalize(string $placeholder): string
    {
        return trim($placeholder, '?{}');
    }
}
