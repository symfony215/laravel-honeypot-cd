<?php

namespace Spatie\Honeypot\Tests;

use Illuminate\Support\Str;
use Spatie\Honeypot\Honeypot;

class HoneypotTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function honeypot_setup_returns_enabled_true_if_true_in_config()
    {
        config()->set('honeypot.enabled', true);

        app(Honeypot::class)->toArray()['enabled'];

        $this->assertTrue(app(Honeypot::class)->toArray()['enabled']);
    }

    /** @test */
    public function honeypot_setup_returns_enabled_false_if_false_in_config()
    {
        config()->set('honeypot.enabled', false);

        $this->assertFalse(app(Honeypot::class)->toArray()['enabled']);
    }

    /** @test */
    public function honeypot_setup_returns_correct_name_field_name_when_randomize_name_field_name_is_false()
    {
        config()->set('honeypot.name_field_name', 'test_field');
        config()->set('honeypot.randomize_name_field_name', false);

        $this->assertEquals('test_field', app(Honeypot::class)->toArray()['nameFieldName']);
    }

    /** @test */
    public function honeypot_setup_returns_correct_name_field_name_when_randomize_name_field_name_is_true()
    {
        config()->set('honeypot.name_field_name', 'test_field');
        config()->set('honeypot.randomize_name_field_name', true);

        $actualNameFieldName = app(Honeypot::class)->toArray()['nameFieldName'];
        $this->assertTrue(Str::of($actualNameFieldName)->startsWith('test_field_'));
        $this->assertTrue(Str::of($actualNameFieldName)->length() > 11);
    }

    /** @test */
    public function honeypot_setup_returns_correct_valid_from_field_name()
    {
        config()->set('honeypot.valid_from_field_name', 'test_from_field');

        $actualValidFromFieldName = app(Honeypot::class)->toArray()['validFromFieldName'];
        $this->assertEquals('test_from_field', $actualValidFromFieldName);
    }

    /** @test */
    public function honeypot_setup_returns_an_encrypted_time()
    {
        $actualValue = app(Honeypot::class)->toArray()['encryptedValidFrom'];
        $this->assertTrue(strlen($actualValue) > 1);
    }
}
