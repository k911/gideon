<?php

use Gideon\Filesystem\Directory;
use Gideon\Filesystem\FilesystemException;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    const TESTS = __DIR__ . '/../var/tests';
    const DIR = 'test-phpunit-';
    const PATH =  self::TESTS . DIRECTORY_SEPARATOR . self::DIR;
    const FILE = 'test.file';

    public static function tearDownAfterClass() {
        $dir = new Directory(self::TESTS);
        self::assertEquals(true, $dir->isEmpty());
    }

    public function testPermissions() {
        $path = uniqid(self::PATH);
        $success = true;
        $dir = null;
        try {
            // dir permissions
            $dir = new Directory($path, '0757');
            $this->assertSame('0757', $dir->getPermissions());
            $dir->setPermissions('755');
            $this->assertSame('0755', $dir->getPermissions());

            // file permissions
            $file = ($dir->makeFile(uniqid(self::FILE)))->setPermissions('0646');
            $this->assertSame('0646', $file->getPermissions());
            $file->setPermissions('644');
            $this->assertSame('0644', $file->getPermissions());

            $dir->delete();
        }
        catch (FilesystemException $ex) {
            $success = false;
            var_dump($ex);
        }
        $this->assertEquals(true, $success);
        $this->assertEquals(false, $dir->exists());
    }

    public function testReadWriteDelete()
    {
        $path = uniqid(self::PATH);
        $success = true;
        try {
            $dir = new Directory($path);
            $this->assertEquals(false, $dir->exists());
            $dir->create();
            $this->assertEquals(true, file_exists($path));
            $this->assertEquals(true, $dir->exists());
            $this->assertEquals(true, $dir->isExecutable());
            $this->assertEquals(true, $dir->isWritable());

            $file = $dir->makeFile('test.file');
            $this->assertEquals(true, file_exists($path . DIRECTORY_SEPARATOR . self::FILE));
            $this->assertEquals(true, $dir->has(self::FILE));
            $this->assertEquals(true, $file->exists());

            $dir->clear();
            $this->assertEquals(false, file_exists($path . DIRECTORY_SEPARATOR . self::FILE));
            $this->assertEquals(false, $dir->has(self::FILE));
            $this->assertEquals(false, $file->exists());
            $this->assertEquals(true, file_exists($path));
            $this->assertEquals(true, $dir->exists());

            $file->create();
            $this->assertEquals(true, file_exists($path . DIRECTORY_SEPARATOR . self::FILE));
            $this->assertEquals(true, $dir->has(self::FILE));
            $this->assertEquals(true, $file->exists());

            $file->getParent()->delete();
            $this->assertEquals(false, $dir->exists());
        } catch (FilesystemException $ex) {
            $success = false;
            var_dump($ex);
        }
        $this->assertEquals(true, $success);
        $this->assertEquals(false, file_exists($path));
    }

}