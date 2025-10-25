<?php

use App\Libraries\JWTService;
use App\Models\MotherModel;
use App\Models\NotificationModel;
use App\Models\ScheduleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CommandsTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class ScheduleApiTest extends FeatureTestCase
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
    private MotherModel $mothers;
    private ScheduleModel $schedules;
    private NotificationModel $notifications;

    protected function setUp(): void
    {
        parent::setUp();

        helper(['auth', 'response_formatter']);

        putenv('JWT_SECRET=testing-secret-key');
        $_ENV['JWT_SECRET']    = 'testing-secret-key';
        $_SERVER['JWT_SECRET'] = 'testing-secret-key';

        $this->users         = new UserModel();
        $this->mothers       = new MotherModel();
        $this->schedules     = new ScheduleModel();
        $this->notifications = new NotificationModel();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['auth_user']);
        Time::resetTestNow();

        parent::tearDown();
    }

    public function testCreateScheduleRequiresAuthentication(): void
    {
        $mother = $this->createMotherWithUser('mother-auth@example.com');

        $payload = [
            'mother_id'    => $mother['mother']['id'],
            'scheduled_at' => '2024-06-15 08:00:00',
            'status'       => 'pending',
            'location'     => 'Ruang Konsultasi',
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post('api/schedules');

        $this->assertSame(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('Unauthorized.', $data['message']);
    }

    public function testCreateScheduleRequiresExpertRole(): void
    {
        $motherBundle = $this->createMotherWithUser('mother-role@example.com');
        $ibuUser      = $motherBundle['user'];

        $payload = [
            'mother_id'    => $motherBundle['mother']['id'],
            'scheduled_at' => '2024-06-15 09:00:00',
            'status'       => 'pending',
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($ibuUser))
            ->post('api/schedules');

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to access this resource.', $data['message']);
    }

    public function testCreateScheduleSucceedsForExpert(): void
    {
        $expert       = $this->createUser('pakar', 'expert-create@example.com');
        $motherBundle = $this->createMotherWithUser('mother-create@example.com');

        $payload = [
            'mother_id'    => $motherBundle['mother']['id'],
            'scheduled_at' => '2024-06-16 10:00:00',
            'status'       => 'confirmed',
            'location'     => 'Klinik Sehat',
            'notes'        => 'Bawa catatan makan.',
        ];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($expert))
            ->post('api/schedules');

        $this->assertSame(ResponseInterface::HTTP_CREATED, $response->getStatusCode());

        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Jadwal berhasil dibuat.', $data['message']);
        $this->assertIsArray($data['data']);
        $this->assertSame($motherBundle['mother']['id'], $data['data']['mother_id']);
        $this->assertSame($expert['id'], $data['data']['expert_id']);
        $this->assertSame('confirmed', $data['data']['status']);
        $this->assertSame('Klinik Sehat', $data['data']['location']);

        $schedule = $this->schedules->find($data['data']['id']);
        $this->assertNotNull($schedule);
        $this->assertSame('confirmed', $schedule['status']);
        $this->assertSame('Klinik Sehat', $schedule['location']);
        $this->assertSame('Bawa catatan makan.', $schedule['notes']);
    }

    public function testUpdateAttendanceRequiresMotherRole(): void
    {
        $expert       = $this->createUser('pakar', 'expert-attendance@example.com');
        $motherBundle = $this->createMotherWithUser('mother-attendance@example.com');
        $schedule     = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => '2024-06-17 08:00:00',
        ]);

        $payload = ['attendance' => 'confirmed'];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($expert))
            ->put('api/schedules/' . $schedule['id'] . '/attendance');

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to access this resource.', $data['message']);
    }

    public function testUpdateAttendanceSucceedsForMotherOwner(): void
    {
        $expert       = $this->createUser('pakar', 'expert-attendance-success@example.com');
        $motherBundle = $this->createMotherWithUser('mother-attendance-success@example.com');
        $schedule     = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => '2024-06-18 10:00:00',
        ]);

        $payload = ['attendance' => 'confirmed'];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($motherBundle['user']))
            ->put('api/schedules/' . $schedule['id'] . '/attendance');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Kehadiran berhasil diperbarui.', $data['message']);
        $this->assertSame('confirmed', $data['data']['attendance']);
        $this->assertSame('confirmed', $data['data']['status']);

        $updated = $this->schedules->find($schedule['id']);
        $this->assertSame('confirmed', $updated['attendance']);
        $this->assertSame('confirmed', $updated['status']);
    }

    public function testUpdateAttendanceDeclinedCancelsSchedule(): void
    {
        $expert       = $this->createUser('pakar', 'expert-attendance-decline@example.com');
        $motherBundle = $this->createMotherWithUser('mother-attendance-decline@example.com');
        $schedule     = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => '2024-06-18 10:00:00',
        ]);

        $payload = ['attendance' => 'declined'];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($motherBundle['user']))
            ->put('api/schedules/' . $schedule['id'] . '/attendance');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Kehadiran berhasil diperbarui.', $data['message']);
        $this->assertSame('declined', $data['data']['attendance']);
        $this->assertSame('cancelled', $data['data']['status']);

        $updated = $this->schedules->find($schedule['id']);
        $this->assertSame('declined', $updated['attendance']);
        $this->assertSame('cancelled', $updated['status']);
    }

    public function testUpdateEvaluationRequiresExpertRole(): void
    {
        $expert       = $this->createUser('pakar', 'expert-evaluation@example.com');
        $motherBundle = $this->createMotherWithUser('mother-evaluation@example.com');
        $schedule     = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => '2024-06-19 09:00:00',
        ]);

        $payload = ['evaluation' => ['catatan' => 'Perlu ditinjau ulang']];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($motherBundle['user']))
            ->put('api/schedules/' . $schedule['id'] . '/evaluation');

        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertFalse($data['status']);
        $this->assertSame('You do not have permission to access this resource.', $data['message']);
    }

    public function testUpdateEvaluationCompletesScheduleForExpert(): void
    {
        $expert       = $this->createUser('pakar', 'expert-evaluation-success@example.com');
        $motherBundle = $this->createMotherWithUser('mother-evaluation-success@example.com');
        $schedule     = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => '2024-06-20 11:00:00',
        ]);

        $payload = ['evaluation' => ['catatan' => 'Sesi berjalan baik']];

        $response = $this->withBody(json_encode($payload, JSON_THROW_ON_ERROR))
            ->withHeaders($this->jsonHeadersFor($expert))
            ->put('api/schedules/' . $schedule['id'] . '/evaluation');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Evaluasi jadwal berhasil diperbarui.', $data['message']);
        $this->assertSame('completed', $data['data']['status']);

        $updated = $this->schedules->find($schedule['id']);
        $this->assertSame('completed', $updated['status']);
        $this->assertJsonStringEqualsJsonString(json_encode($payload['evaluation'], JSON_THROW_ON_ERROR), (string) $updated['evaluation_json']);

        $motherNotifications = $this->notifications
            ->where('mother_id', $motherBundle['mother']['id'])
            ->where('schedule_id', $schedule['id'])
            ->findAll();
        $this->assertNotEmpty($motherNotifications);
        $this->assertSame('schedule_evaluation_completed', $motherNotifications[0]['type']);
    }

    public function testNotificationsEndpointReturnsOnlyUnreadForMother(): void
    {
        $motherOne = $this->createMotherWithUser('mother-notif-one@example.com');
        $motherTwo = $this->createMotherWithUser('mother-notif-two@example.com');
        $expert    = $this->createUser('pakar', 'expert-notif@example.com');
        $admin     = $this->createUser('admin', 'admin-notif@example.com');

        $this->notifications->insert([
            'mother_id' => $motherOne['mother']['id'],
            'expert_id' => $expert['id'],
            'title'     => 'Pengingat A',
            'message'   => 'Pesan A',
            'type'      => 'info',
            'is_read'   => 0,
        ]);
        $this->notifications->insert([
            'mother_id' => $motherOne['mother']['id'],
            'expert_id' => $expert['id'],
            'title'     => 'Pengingat B',
            'message'   => 'Pesan B',
            'type'      => 'info',
            'is_read'   => 1,
        ]);
        $this->notifications->insert([
            'mother_id' => $motherTwo['mother']['id'],
            'expert_id' => $expert['id'],
            'title'     => 'Pengingat C',
            'message'   => 'Pesan C',
            'type'      => 'info',
            'is_read'   => 0,
        ]);

        $response = $this->withHeaders($this->jsonHeadersFor($admin))
            ->get('api/notifications?mother_id=' . $motherOne['mother']['id'] . '&unread=1');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseArray($response);
        $this->assertTrue($data['status']);
        $this->assertSame('Daftar notifikasi berhasil dimuat.', $data['message']);
        $this->assertIsArray($data['data']);
        $this->assertCount(1, $data['data']);
        $this->assertSame($motherOne['mother']['id'], $data['data'][0]['mother_id']);
        $this->assertFalse($data['data'][0]['is_read']);
    }

    public function testScheduleReminderEndpointCreatesNotifications(): void
    {
        $expert       = $this->createUser('pakar', 'expert-reminder@example.com');
        $motherBundle = $this->createMotherWithUser('mother-reminder@example.com');

        $now = Time::createFromFormat('Y-m-d H:i:s', '2024-05-01 08:00:00', 'Asia/Makassar');
        Time::setTestNow($now);

        $dueSchedule = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => $now->copy()->addHours(24)->toDateTimeString(),
            'status'       => 'confirmed',
        ]);

        $outsideSchedule = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => $now->copy()->addHours(30)->toDateTimeString(),
            'status'       => 'confirmed',
        ]);

        $response = $this->get('cron/schedules/reminder');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());

        $payload = $this->getResponseArray($response);
        $this->assertTrue($payload['status']);
        $this->assertSame('Schedule reminder cron executed successfully.', $payload['message']);
        $this->assertSame(0, $payload['data']['cancelled_count']);
        $this->assertSame(1, $payload['data']['reminders_sent']);
        $this->assertCount(1, $payload['data']['reminders']);
        $this->assertSame($dueSchedule['id'], $payload['data']['reminders'][0]['schedule_id']);
        $this->assertSame($motherBundle['mother']['id'], $payload['data']['reminders'][0]['mother_id']);

        $updatedDue = $this->schedules->find($dueSchedule['id']);
        $this->assertSame(1, (int) $updatedDue['reminder_sent']);

        $updatedOutside = $this->schedules->find($outsideSchedule['id']);
        $this->assertSame(0, (int) $updatedOutside['reminder_sent']);

        $notifications = $this->notifications->where('schedule_id', $dueSchedule['id'])->findAll();
        $this->assertCount(1, $notifications);
        $this->assertSame($motherBundle['mother']['id'], (int) $notifications[0]['mother_id']);
        $this->assertSame($expert['id'], (int) $notifications[0]['expert_id']);
        $this->assertSame('schedule-reminder', $notifications[0]['type']);
    }

    public function testScheduleReminderEndpointCancelsExpiredSchedules(): void
    {
        $expert       = $this->createUser('pakar', 'expert-reminder-expired@example.com');
        $motherBundle = $this->createMotherWithUser('mother-reminder-expired@example.com');

        $now = Time::createFromFormat('Y-m-d H:i:s', '2024-05-01 09:00:00', 'Asia/Makassar');
        Time::setTestNow($now);

        $confirmedPast = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => $now->copy()->subHours(1)->toDateTimeString(),
            'status'       => 'confirmed',
        ]);

        $pendingPast = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => $now->copy()->subHours(2)->toDateTimeString(),
            'status'       => 'pending',
        ]);

        $completedPast = $this->createSchedule([
            'mother_id'    => $motherBundle['mother']['id'],
            'expert_id'    => $expert['id'],
            'scheduled_at' => $now->copy()->subHours(3)->toDateTimeString(),
            'status'       => 'completed',
        ]);

        $response = $this->get('cron/schedules/reminder');

        $this->assertSame(ResponseInterface::HTTP_OK, $response->getStatusCode());

        $payload = $this->getResponseArray($response);
        $this->assertTrue($payload['status']);
        $this->assertSame('Schedule reminder cron executed successfully.', $payload['message']);
        $this->assertSame(2, $payload['data']['cancelled_count']);
        $this->assertSameCanonicalizing([
            $confirmedPast['id'],
            $pendingPast['id'],
        ], $payload['data']['cancelled_schedule_ids']);
        $this->assertSame(0, $payload['data']['reminders_sent']);
        $this->assertSame([], $payload['data']['reminders']);

        $updatedConfirmed = $this->schedules->find($confirmedPast['id']);
        $this->assertSame('cancelled', $updatedConfirmed['status']);

        $updatedPending = $this->schedules->find($pendingPast['id']);
        $this->assertSame('cancelled', $updatedPending['status']);

        $updatedCompleted = $this->schedules->find($completedPast['id']);
        $this->assertSame('completed', $updatedCompleted['status']);
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
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function createSchedule(array $overrides): array
    {
        $data = array_merge([
            'mother_id'     => null,
            'expert_id'     => null,
            'scheduled_at'  => Time::now()->toDateTimeString(),
            'status'        => 'pending',
            'location'      => 'Fasilitas Kesehatan',
            'notes'         => 'Catatan awal',
            'reminder_sent' => 0,
            'attendance'    => 'pending',
        ], $overrides);

        $scheduleId = $this->schedules->insert($data, true);
        $schedule   = $this->schedules->find($scheduleId);
        $this->assertIsArray($schedule);

        return $schedule;
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
