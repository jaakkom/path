Contains a single static class with methods for manipulating filesystem paths.

Most of use are used to seeing:
```php
$this->path = rtrim($path, '/').'/';
```
Problems arise when the path given is winbloze `C:\foo\bar` or for a stream wrapper `vfs://foo`

This package aims to provide commonly used methods for dealing with such paths.

```php
class Path
{
    /**
     * Removes '.', '..' and terminating '/' from path.
     * 
     * Also collapses multiple concurrent '/' characters.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalize(string $path): string;

    /**
     * Checks if $path has a valid prefix.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isAbsolute(string $path): bool;

    /**
     * Returns the absolute prefix of $path.
     *
     * For Unix style paths '/' is returned, for windows, 'c:/'
     * and for wrappers, the full scheme 'vfs://'. An empty
     * string is returned for relative paths.
     *
     * @param string $path
     *
     * @return string
     */
    public static function getPrefix(string $path): string;

    /**
     * Returns the directories/filename component of $path.
     * 
     * The returned path is fully normalized.
     *
     * @param string $path
     *
     * @return string
     */
    public static function getHierarchy(string $path): string;

    /**
     * Returns both prefix and hierarchy of $path.
     *
     * @param string $path
     *
     * @return array
     */
    public static function split(string $path): array;

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
    public static function append(string $path, string $append): string;

    /**
     * Returns the normalized parent directory of $path.
     *
     * @param string $path
     *
     * @return string
     */
    public static function dirname(string $path): string;
    
    /**
     * Calculates the path between $source and $target.
     * 
     * @param string $source
     * @param string $target
     * @return string
     * @throws PathException
     */
    public static function relativeTo(string $source, string $target): string;
}
```