<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
     * @var bool
     */
	protected $backupStaticAttributes = false;

    use CreatesApplication;
}
