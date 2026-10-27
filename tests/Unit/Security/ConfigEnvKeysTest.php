<?php

namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

/**
 * C6: config/zoom.php once used the Zoom key and secret themselves as env() key names,
 * committing the secrets. Every env() key in config/ must be an UPPER_SNAKE_CASE name.
 */
class ConfigEnvKeysTest extends TestCase
{
    public function test_config_env_keys_are_variable_names_not_values(): void
    {
        $offenders = [];

        foreach (glob(dirname(__DIR__, 3) . '/config/*.php') as $file) {
            preg_match_all("/env\\(\\s*['\"]([^'\"]+)['\"]/", file_get_contents($file), $matches);

            foreach ($matches[1] as $key) {
                if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                    $offenders[] = basename($file) . ': ' . substr($key, 0, 4) . '…';
                }
            }
        }

        $this->assertSame([], $offenders, 'env() keys must be UPPER_SNAKE_CASE variable names');
    }
}
