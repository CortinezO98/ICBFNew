<?php use PHPUnit\Framework\TestCase; final class CoachingSmokeTest extends TestCase { public function testPhpRuntime():void{$this->assertTrue(PHP_VERSION_ID>=80100);} }
