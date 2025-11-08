<?php

use App\Libraries\JWTService;
use App\Models\MotherModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class MotherApiTest extends FeatureTestCase
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

    public function testMotherCanUpdateOwnProfile(): void
    {
        $motherBundle = $this->createMotherWithUser('mother-update@example.com');

        $payload = [
            'bb'            => 55.5,
            'tb'            => 165.2,
            'umur'          => 32,
            'usia_bayi_bln' => 8,
            'laktasi_tipe'  => 'parsial',
            'aktivitas'     => 'sedang',
            'alergi'        => ['kacang', 'telur'],
            'name'          => 'Ibu Diperbarui',
            'email'         => 'ibu-update@example.com',
            'password'      => 'passwordBaru123',
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($motherBundle['user']))
            ->put('api/mothers/' . $motherBundle['mother']['id']);

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());

        $data = $this->getResponseArray($response);

        $this->assertTrue($data['status']);
        $this->assertSame('Data ibu berhasil diperbarui.', $data['message']);
        $this->assertSame('Ibu Diperbarui', $data['data']['name']);
        $this->assertSame('ibu-update@example.com', $data['data']['email']);
        $this->assertSame(55.5, $data['data']['profile']['bb']);
        $this->assertSame(165.2, $data['data']['profile']['tb']);
        $this->assertSame(32, $data['data']['profile']['umur']);
        $this->assertSame(8, $data['data']['profile']['usia_bayi_bln']);
        $this->assertSame('parsial', $data['data']['profile']['laktasi_tipe']);
        $this->assertSame('sedang', $data['data']['profile']['aktivitas']);
        $this->assertSame(['kacang', 'telur'], $data['data']['profile']['alergi']);

        $updatedMother = $this->mothers->find($motherBundle['mother']['id']);
        $this->assertIsArray($updatedMother);
        $this->assertSame(55.5, (float) $updatedMother['bb']);
        $this->assertSame(165.2, (float) $updatedMother['tb']);
        $this->assertSame(32, (int) $updatedMother['umur']);
        $this->assertSame(8, (int) $updatedMother['usia_bayi_bln']);
        $this->assertSame('parsial', $updatedMother['laktasi_tipe']);
        $this->assertSame('sedang', $updatedMother['aktivitas']);

        $decodedAlergi = $updatedMother['alergi_json'] !== null
            ? json_decode((string) $updatedMother['alergi_json'], true, 512, JSON_THROW_ON_ERROR)
            : null;
        $this->assertSame(['kacang', 'telur'], $decodedAlergi);

        $updatedUser = $this->users->find($motherBundle['user']['id']);
        $this->assertIsArray($updatedUser);
        $this->assertSame('Ibu Diperbarui', $updatedUser['name']);
        $this->assertSame('ibu-update@example.com', $updatedUser['email']);
        $this->assertTrue(password_verify('passwordBaru123', $updatedUser['password_hash']));
    }

    public function testMotherCannotUpdateOtherMother(): void
    {
        $firstMother  = $this->createMotherWithUser('mother-first@example.com');
        $secondMother = $this->createMotherWithUser('mother-second@example.com');

        $payload = [
            'bb' => 70.0,
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($firstMother['user']))
            ->put('api/mothers/' . $secondMother['mother']['id']);

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());

        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to access this resource.', $data['message']);

        $unchangedMother = $this->mothers->find($secondMother['mother']['id']);
        $this->assertIsArray($unchangedMother);
        $this->assertSame(50.0, (float) $unchangedMother['bb']);
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
     * @return array{user: array<string, mixed>, mother: array<string, mixed>}
     */
    private function createMotherWithUser(string $email): array
    {
        $user = $this->createUser('ibu', $email);

        $motherId = $this->mothers->insert([
            'user_id'        => $user['id'],
            'bb'             => 50.0,
            'tb'             => 160.0,
            'umur'           => 30,
            'usia_bayi_bln'  => 6,
            'laktasi_tipe'   => 'eksklusif',
            'aktivitas'      => 'ringan',
            'alergi_json'    => null,
            'preferensi_json'=> null,
            'riwayat_json'   => null,
        ], true);

        $mother = $this->mothers->find($motherId);
        $this->assertIsArray($mother);

        return ['user' => $user, 'mother' => $mother];
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
