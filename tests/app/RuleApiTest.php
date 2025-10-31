<?php

use App\Libraries\JWTService;
use App\Models\RuleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CommandsTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class RuleApiTest extends FeatureTestCase
{
    use DatabaseTestTrait;
    use CommandsTestTrait;

    /**
     * Run full migrations for each test to ensure a clean database.
     *
     * @var bool
     */
    protected $refresh = true;

    private UserModel $users;
    private RuleModel $rules;

    protected function setUp(): void
    {
        parent::setUp();

        helper(['auth', 'response_formatter']);

        putenv('JWT_SECRET=testing-secret-key');
        $_ENV['JWT_SECRET']    = 'testing-secret-key';
        $_SERVER['JWT_SECRET'] = 'testing-secret-key';

        $this->users = new UserModel();
        $this->rules = new RuleModel();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['auth_user']);

        parent::tearDown();
    }

    public function testIndexRequiresAuthentication(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('api/rules');

        $this->assertSame(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $payload = $this->getResponseArray($response);
        $this->assertFalse($payload['status']);
        $this->assertSame('Unauthorized.', $payload['message']);
    }

    public function testIndexRequiresAdminRole(): void
    {
        $this->createRule();
        $nonAdmin = $this->createUser('pakar', 'expert-rules-index@example.com');

        $response = $this->withHeaders($this->jsonHeadersFor($nonAdmin))->get('api/rules');

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $payload = $this->getResponseArray($response);
        $this->assertFalse($payload['status']);
        $this->assertSame('You do not have permission to manage rules.', $payload['message']);
    }

    public function testIndexSucceedsForAdmin(): void
    {
        $this->createRule(['name' => 'Existing Rule']);
        $admin = $this->createUser('admin', 'admin-rules-index@example.com');

        $response = $this->withHeaders($this->jsonHeadersFor($admin))->get('api/rules');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $payload = $this->getResponseArray($response);
        $this->assertTrue($payload['status']);
        $this->assertSame('Daftar rule berhasil dimuat.', $payload['message']);
        $this->assertNotEmpty($payload['data']);
        $this->assertSame('Existing Rule', $payload['data'][0]['name']);
        $this->assertArrayHasKey('komentar_pakar', $payload['data'][0]);
    }

    public function testCreateRequiresAuthentication(): void
    {
        $payload = $this->rulePayload();

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post('api/rules');

        $this->assertSame(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('Unauthorized.', $data['message']);
    }

    public function testCreateRequiresAdminRole(): void
    {
        $payload  = $this->rulePayload();
        $nonAdmin = $this->createUser('pakar', 'expert-rules-create@example.com');

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($nonAdmin))
            ->post('api/rules');

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to manage rules.', $data['message']);
    }

    public function testCreateSucceedsForAdmin(): void
    {
        $payload = $this->rulePayload([
            'name'    => 'New Rule',
            'version' => 'v2',
        ]);
        $admin = $this->createUser('admin', 'admin-rules-create@example.com');

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($admin))
            ->post('api/rules');

        $this->assertSame(ResponseInterface::HTTP_CREATED, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Rule berhasil ditambahkan.', $data['message']);
        $this->assertSame('New Rule', $data['data']['name']);
        $this->assertSame('v2', $data['data']['version']);
        $this->assertNull($data['data']['komentar_pakar']);

        $stored = $this->rules->find($data['data']['id']);
        $this->assertIsArray($stored);
        $this->assertSame('New Rule', $stored['name']);
        $this->assertSame('v2', $stored['version']);
        $this->assertNull($stored['komentar_pakar']);
    }

    public function testUpdateRequiresAuthentication(): void
    {
        $rule = $this->createRule();
        $payload = ['name' => 'Updated'];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->put('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('Unauthorized.', $data['message']);
    }

    public function testUpdateRequiresAdminRole(): void
    {
        $rule = $this->createRule();
        $user = $this->createUser('pakar', 'expert-rules-update@example.com');
        $payload = ['name' => 'Updated'];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($user))
            ->put('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to manage rules.', $data['message']);
    }

    public function testUpdateSucceedsForAdmin(): void
    {
        $rule = $this->createRule();
        $admin = $this->createUser('admin', 'admin-rules-update@example.com');
        $payload = [
            'condition'      => 'if bmi > 25',
            'recommendation' => 'Consult doctor',
            'category'       => 'health',
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($admin))
            ->put('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Rule berhasil diperbarui.', $data['message']);
        $this->assertSame('v2', $data['data']['version']);
        $this->assertNull($data['data']['komentar_pakar']);

        $updated = $this->rules->find($rule['id']);
        $this->assertIsArray($updated);
        $details = json_decode((string) $updated['json_rule'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('if bmi > 25', $details['condition']);
        $this->assertSame('Consult doctor', $details['recommendation']);
        $this->assertSame('health', $details['category']);
        $this->assertSame('v2', $updated['version']);
        $this->assertNull($updated['komentar_pakar']);
    }

    public function testAdminUpdateClearsKomentarPakarAndIncrementsVersion(): void
    {
        $rule = $this->createRule([
            'version' => '1.2',
        ]);

        $this->rules->update($rule['id'], ['komentar_pakar' => 'Perlu review pakar']);
        $ruleWithComment = $this->rules->find($rule['id']);
        $this->assertIsArray($ruleWithComment);
        $this->assertSame('Perlu review pakar', $ruleWithComment['komentar_pakar']);

        $admin = $this->createUser('admin', 'admin-clear-comment@example.com');

        $payload = [
            'name' => 'Updated Name',
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($admin))
            ->put('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Rule berhasil diperbarui.', $data['message']);
        $this->assertSame('1.3', $data['data']['version']);
        $this->assertNull($data['data']['komentar_pakar']);

        $updated = $this->rules->find($rule['id']);
        $this->assertIsArray($updated);
        $this->assertSame('1.3', $updated['version']);
        $this->assertNull($updated['komentar_pakar']);
    }

    public function testDeleteRequiresAuthentication(): void
    {
        $rule = $this->createRule();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->delete('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('Unauthorized.', $data['message']);
    }

    public function testDeleteRequiresAdminRole(): void
    {
        $rule = $this->createRule();
        $user = $this->createUser('pakar', 'expert-rules-delete@example.com');

        $response = $this->withHeaders($this->jsonHeadersFor($user))->delete('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to manage rules.', $data['message']);
    }

    public function testDeleteSucceedsForAdmin(): void
    {
        $rule = $this->createRule();
        $admin = $this->createUser('admin', 'admin-rules-delete@example.com');

        $response = $this->withHeaders($this->jsonHeadersFor($admin))->delete('api/rules/' . $rule['id']);

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Rule berhasil dihapus.', $data['message']);

        $deleted = $this->rules->find($rule['id']);
        $this->assertNull($deleted);
    }

    /**
     * @return array{status: bool, message: string, data: mixed}
     */
    private function getResponseArray(TestResponse $response): array
    {
        $json = $response->getJSON();
        $this->assertIsString($json);

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);

        return $data;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function rulePayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Sample Rule',
            'version'        => 'v1',
            'condition'      => 'if bmi < 18.5',
            'recommendation' => 'Increase calorie intake',
            'category'       => 'nutrition',
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function createRule(array $overrides = []): array
    {
        $data = array_merge([
            'name'           => 'Sample Rule',
            'version'        => 'v1',
            'json_rule'      => json_encode([
                'condition'      => 'if bmi < 18.5',
                'recommendation' => 'Increase calorie intake',
                'category'       => 'nutrition',
                'status'         => 'active',
            ], JSON_THROW_ON_ERROR),
            'effective_from' => null,
            'is_active'      => 1,
        ], $overrides);

        $ruleId = $this->rules->insert($data, true);
        $rule   = $this->rules->find($ruleId);
        $this->assertIsArray($rule);

        return $rule;
    }

    /**
     * @param array<string, mixed> $user
     *
     * @return array<string, string>
     */
    private function jsonHeadersFor(array $user): array
    {
        $token = $this->generateTokenForUser($user);

        return [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    /**
     * @param array<string, mixed> $user
     */
    private function generateTokenForUser(array $user): string
    {
        $jwtService = new JWTService('testing-secret-key');

        return $jwtService->generateToken([
            'id'    => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'name'  => $user['name'],
        ]);
    }

    private function createUser(string $role, string $email): array
    {
        $userId = $this->users->insert([
            'name'          => ucfirst($role) . ' User',
            'email'         => $email,
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'role'          => $role,
        ], true);

        $user = $this->users->find($userId);
        $this->assertIsArray($user);

        return $user;
    }
}
