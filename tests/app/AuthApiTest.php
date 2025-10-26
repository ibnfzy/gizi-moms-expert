<?php

use App\Models\MotherModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class AuthApiTest extends FeatureTestCase
{
    use DatabaseTestTrait;

    /**
     * Run full migrations for each test to ensure a clean database.
     *
     * @var bool
     */
    protected $refresh = true;

    private UserModel $users;
    private MotherModel $mothers;

    protected function setUp(): void
    {
        parent::setUp();

        helper(['auth', 'response_formatter']);

        putenv('JWT_SECRET=testing-secret-key');
        $_ENV['JWT_SECRET']    = 'testing-secret-key';
        $_SERVER['JWT_SECRET'] = 'testing-secret-key';

        $this->users   = new UserModel();
        $this->mothers = new MotherModel();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['auth_user']);

        parent::tearDown();
    }

    public function testMotherLoginIncludesMotherId(): void
    {
        $password = 'secret123';
        $email    = 'ibu-login@example.com';

        $userId = $this->users->insert([
            'name'          => 'Ibu User',
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role'          => 'ibu',
        ], true);

        $motherId = $this->mothers->insert([
            'user_id' => $userId,
        ], true);

        $payload = [
            'email'    => $email,
            'password' => $password,
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post('api/auth/login');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->decodeResponse($response);

        $this->assertTrue($data['status']);
        $this->assertSame('Login successful.', $data['message']);
        $this->assertArrayHasKey('token', $data['data']);
        $this->assertArrayHasKey('user', $data['data']);
        $this->assertArrayHasKey('motherId', $data['data']['user']);
        $this->assertSame($motherId, $data['data']['user']['motherId']);
    }

    /**
     * @return array{status: bool, message: string, data: mixed}
     */
    private function decodeResponse(TestResponse $response): array
    {
        $json = $response->getJSON();
        $this->assertIsString($json);

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);

        return $decoded;
    }
}
