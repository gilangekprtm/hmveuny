<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function useIsolatedJsonData(): void
    {
        config(['data.path' => storage_path('framework/testing/json')]);
    }
}
