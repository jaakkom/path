<?php

declare(strict_types=1);

namespace Twifty\Filesystem\Test;

use PHPUnit\Framework\TestCase;
use Twifty\Filesystem\Exceptions\PathException;
use Twifty\Filesystem\Path;

class PathTest extends TestCase
{
    /**
     * @dataProvider provideSplit
     * 
     * @param string $path
     * @param string $prefix
     * @param string $hierarchy
     */
    public function testSplit(string $path, string $prefix = null, string $hierarchy = null)
    {
        $result = Path::split($path);
        
        $this->assertEquals($prefix, Path::getPrefix($path));
        $this->assertEquals($hierarchy, Path::getHierarchy($path));
    }
    
    public function provideSplit() : array
    {
        return [
            ['', '', ''],
            ['/', '/', ''],
            ['/foo', '/', 'foo'],
            ['C:\\', 'C:/', ''],
            ['C:\\foo', 'C:/', 'foo'],
            ['vfs123://', 'vfs123://', ''],
            ['vfs123://foo', 'vfs123://', 'foo'],
            ['foo', '', 'foo'],
            ['foo/bar', '', 'foo/bar'],
        ];
    }
    
    /**
     * @dataProvider provideNormalize
     * 
     * @param string $path
     */
    public function testNormalize(string $path, string $normalized)
    {
        Path::split($path);
        
        $this->assertEquals($normalized, Path::normalize($path));
    }
    
    public function provideNormalize() : array
    {
        return [
            ['', ''],
            ['.', ''],
            ['..', ''],
            ['foo/..', ''],
            ['foo/bar/..', 'foo'],
            ['foo/../bar', 'bar'],
            ['../foo/bar', 'foo/bar'],
            ['vfs://../foo/bar', 'vfs://foo/bar'],
            ['vfs:///foo/bar', 'vfs://foo/bar'],
            ['vfs://foo//bar', 'vfs://foo/bar'],
            ['vfs://foo\/\/////\\\/\\/\/\/\////bar', 'vfs://foo/bar'],
        ];
    }
    
    /**
     * @dataProvider provideAppend
     * 
     * @param string $path
     * @param string $append
     * @param string $absolute
     */
    public function testAppend(string $path, string $append, string $absolute)
    {
        $this->assertEquals($absolute, Path::append($path, $append));
    }
    
    public function provideAppend() : array
    {
        return [
            ['/', 'foo', '/foo'],
            ['C:\\', 'foo', 'C:/foo'],
            ['C:\\foo', '../bar', 'C:/bar'],
            ['C:\\foo', 'bar\\baz', 'C:/foo/bar/baz'],
        ];
    }
    
    /**
     * @dataProvider provideInvalidAppend
     * 
     * @param string $path
     * @param string $append
     */
    public function testInvalidAppend(string $path, string $append)
    {
        $this->expectException(PathException::class);
        
        Path::append($path, $append);
    }
    
    public function provideInvalidAppend() : array
    {
        return [
            'Non absolute path' => ['foo', 'bar'],
            'absolut append' => ['/foo', '/bar'],
        ];
    }
    
    /**
     * @dataProvider provideDirName
     * 
     * @param string $path
     * @param string $expect
     */
    public function testDirName(string $path, string $expect)
    {
        $this->assertEquals($expect, Path::dirname($path));
    }
    
    public function provideDirName() : array
    {
        return [
            'Simple' => ['/foo', '/'],
            'Simple file' => ['/foo/bar.tmp', '/foo'],
            'Indirect' => ['/foo/../bar', '/'],
        ];
    }
    
    /**
     * @dataProvider provideRelativeTo
     * 
     * @param string $source
     * @param string $target
     * @param string $expected
     */
    public function testRelativeTo(string $source, string $target, string $expected)
    {
        $this->assertEquals($expected, Path::relativeTo($source, $target));
    }
    
    public function provideRelativeTo(): array
    {
        return [
            ['/foo/bar', '/foo/bar', './'],
            ['/foo/bar', '/foo/baz', '../baz'],
            ['/foo/bar', '/foo/bar/baz', './baz'],
            ['C:\\foo\\bar', 'D:\\foo\\bar', 'D:\\foo\\bar'],
        ];
    }
    
    public function testRelativeTo_NonAbsoluteSource()
    {
        $this->expectException(PathException::class);
        
        Path::relativeTo('foo/bar', '/foo/bar');
    }
    
    public function testRelativeTo_NonAbsoluteTarget()
    {
        $this->expectException(PathException::class);
        
        Path::relativeTo('/foo/bar', 'foo/bar');
    }
}
