<?php

use App\Filters\AuthFilter;
use App\Filters\RoleFilter;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockRequest;
use Config\App;

/**
 * @internal
 */
final class FilterResponseTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($GLOBALS['auth_user']);
    }

    public function testAuthFilterUnauthorizedResponseFormat(): void
    {
        $request = new MockRequest(new App(), new URI('http://example.com/protected'));

        $filter = new AuthFilter();

        $response = $filter->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertSame(
            [
                'status'  => false,
                'message' => 'Authorization token missing.',
                'data'    => null,
            ],
            json_decode($response->getBody(), true),
        );
    }

    public function testRoleFilterForbiddenResponseFormat(): void
    {
        helper('auth');
        set_auth_user(['role' => 'mother']);

        $request = new MockRequest(new App(), new URI('http://example.com/admin'));

        $filter = new RoleFilter();

        $response = $filter->before($request, ['pakar']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(ResponseInterface::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame(
            [
                'status'  => false,
                'message' => 'You do not have permission to access this resource.',
                'data'    => null,
            ],
            json_decode($response->getBody(), true),
        );
    }
}
