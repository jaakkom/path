<?php

declare(strict_types=1);

/*
 * This file is part of Twifty Path.
 *
 * (c) Owen Parry <waldermort@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Twifty\Filesystem;

use Twifty\Filesystem\Exceptions\PathException;

/**
 * Collection of methods for dealing with paths.
 *
 * @author Owen Parry <waldermort@gmail.com>
 */
class Path
{
    const PREFIX = 0;
    const HIERARCHY = 1;
    
    /**
     * Removes '.', '..' and terminating '/' from path. Also collapses multiple '/'.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalize(string $path): string
    {
        list(
            $prefix,
            $path
        ) = self::split($path);

        $segments = self::chunkify($path);

        return $prefix.implode('/', $segments);
    }

    /**
     * Checks if $path has a valid prefix.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isAbsolute(string $path): bool
    {
        return !empty(self::getPrefix($path));
    }

    /**
     * Returns the prefix from $path.
     *
     * The prefix always includes the trailing '/'.
     *
     * @param string $path
     *
     * @return string
     */
    public static function getPrefix(string $path): string
    {
        return self::split($path)[0];
    }

    /**
     * Returns the directories/filename component of $path.
     *
     * @param string $path
     *
     * @return string
     */
    public static function getHierarchy(string $path): string
    {
        return self::split($path)[1];
    }

    /**
     * Returns both prefix and hierarchy of $path.
     *
     * @param string $path
     *
     * @return array
     */
    public static function split(string $path): array
    {
        preg_match('{^ ( [a-z0-9]{2,}:// | (?:[a-z]:)?/ )? ( [^/]* (?: /+?[^/]+)* (?:/)? | ) }ix', strtr($path, '\\', '/'), $match);

        return [
            $match[1],
            rtrim($match[2], '/'),
        ];
    }

    /**
     * Appends to and normalizes $path.
     *
     * @param string $path
     * @param string $append
     *
     * @throws PathException
     *
     * @return string
     */
    public static function append(string $path, string $append): string
    {
        if (!self::isAbsolute($path)) {
            throw new PathException(sprintf('Path "%s" must be absolute in order to append "%s"', $path, $append));
        }

        if (self::isAbsolute($append)) {
            throw new PathException(sprintf('Hierarchy "%s" cannot be absolute in order to append to "%s"', $append, $path));
        }

        return self::normalize($path.'/'.$append);
    }

    /**
     * Returns the normalized parent directory of $path.
     *
     * @param string $path
     *
     * @return string
     */
    public static function dirname(string $path): string
    {
        return self::append($path, '..');
    }
    
    /**
     * Calculates the path between $source and $target
     * 
     * @param string $source
     * @param string $target
     * @return string
     * @throws PathException
     */
    public static function relativeTo(string $source, string $target): string
    {
        $_source = self::split($source);
        $_target = self::split($target);
        
        if (empty($_source[self::PREFIX]) || empty($_target[self::PREFIX])) {
            throw new PathException(sprintf('Paths "%s" and "%s" must be absolute', $source, $target));
        }
        
        $sourceChunks = self::chunkify($_source[self::HIERARCHY]);
        $targetChunks = self::chunkify($_target[self::HIERARCHY]);
        
        $index = 0;
        while (isset($sourceChunks[$index]) && isset($targetChunks[$index]) && $sourceChunks[$index] === $targetChunks[$index]) {
            ++$index;
        }
        
        if ($_source[self::PREFIX] !== $_target[self::PREFIX] || 0 === $index) {
            return $target;
        }
        
        $traverser = str_repeat('../', count($sourceChunks) - $index);
        $remainder = implode('/', array_slice($targetChunks, $index));
        
        return ('' === $traverser ? './' : $traverser).$remainder;
    }
    
    /**
     * Splits a path into directory names while resolving dot directories and removing extra slashes.
     * 
     * @param string $hierarchy
     * 
     * @return array
     */
    protected static function chunkify(string $hierarchy): array
    {
        $chunks = [];
        
        foreach (explode('/', $hierarchy) as $chunk) {
            if ('..' === $chunk) {
                array_pop($chunks);
            } elseif ('.' !== $chunk && '' !== $chunk) {
                $chunks[] = $chunk;
            }
        }
        
        return $chunks;
    }
}
