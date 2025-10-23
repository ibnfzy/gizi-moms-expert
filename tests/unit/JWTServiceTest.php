<?php

use App\Libraries\JWTService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class JWTServiceTest extends CIUnitTestCase
{
    private ?string $originalEnvSecret;
    private ?string $originalServerSecret;
    private ?string $originalGetenvSecret;

    protected function setUp(): void
    {
        parent::setUp();

        $getenvSecret = getenv('JWT_SECRET');
        $this->originalGetenvSecret = $getenvSecret === false ? null : $getenvSecret;
        $this->originalEnvSecret    = $_ENV['JWT_SECRET'] ?? null;
        $this->originalServerSecret = $_SERVER['JWT_SECRET'] ?? null;
    }

    protected function tearDown(): void
    {
        $this->setEnvironmentSecret($this->originalGetenvSecret);

        if ($this->originalEnvSecret === null) {
            unset($_ENV['JWT_SECRET']);
        } else {
            $_ENV['JWT_SECRET'] = $this->originalEnvSecret;
        }

        if ($this->originalServerSecret === null) {
            unset($_SERVER['JWT_SECRET']);
        } else {
            $_SERVER['JWT_SECRET'] = $this->originalServerSecret;
        }

        parent::tearDown();
    }

    public function testGenerateAndValidateTokenWithSecretFromEnvironment(): void
    {
        $this->setEnvironmentSecret('unit-test-secret');

        $service = new JWTService();

        $token = $service->generateToken([
            'id'    => 123,
            'email' => 'tester@example.com',
            'role'  => 'admin',
            'name'  => 'Unit Tester',
        ]);

        $this->assertNotEmpty($token);

        $result = $service->validateToken($token);

        $this->assertSame([
            'id'    => 123,
            'email' => 'tester@example.com',
            'role'  => 'admin',
            'name'  => 'Unit Tester',
        ], $result);
    }

    public function testConstructorThrowsWhenSecretMissing(): void
    {
        $this->setEnvironmentSecret(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('JWT secret is not configured');

        new JWTService();
    }

    private function setEnvironmentSecret(?string $value): void
    {
        if ($value === null) {
            putenv('JWT_SECRET');
            unset($_ENV['JWT_SECRET'], $_SERVER['JWT_SECRET']);

            return;
        }

        putenv('JWT_SECRET=' . $value);
        $_ENV['JWT_SECRET']    = $value;
        $_SERVER['JWT_SECRET'] = $value;
    }
}
