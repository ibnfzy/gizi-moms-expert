<?php

namespace App\Libraries;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Throwable;

class JWTService
{
    private Configuration $config;

    public function __construct(?string $secret = null)
    {
        $secretKey = $secret ?? env('JWT_SECRET');

        if (empty($secretKey)) {
            throw new \RuntimeException('JWT secret is not configured. Please set JWT_SECRET in your environment.');
        }

        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secretKey)
        );

        $clock = new SystemClock(new DateTimeZone('UTC'));

        $constraints = [
            new SignedWith($this->config->signer(), $this->config->signingKey()),
        ];

        $strictValidAtClass = '\\Lcobucci\\JWT\\Validation\\Constraint\\StrictValidAt';
        $looseValidAtClass  = '\\Lcobucci\\JWT\\Validation\\Constraint\\LooseValidAt';

        if (class_exists($strictValidAtClass)) {
            $constraints[] = new $strictValidAtClass($clock);
        } elseif (class_exists($looseValidAtClass)) {
            $constraints[] = new $looseValidAtClass($clock);
        }

        if (method_exists($this->config, 'setValidationConstraints')) {
            $this->config->setValidationConstraints(...$constraints);
        }
    }

    public function generateToken(array $user, ?DateInterval $ttl = null): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expires = $ttl ? $now->add($ttl) : $now->modify('+1 hour');

        $builder = $this->config->builder()
            ->issuedAt($now)
            ->expiresAt($expires)
            ->withClaim('uid', $user['id'] ?? null)
            ->withClaim('email', $user['email'] ?? null)
            ->withClaim('role', $user['role'] ?? null);

        if (! empty($user['name'])) {
            $builder = $builder->withClaim('name', $user['name']);
        }

        return $builder
            ->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();
    }

    public function validateToken(?string $token): ?array
    {
        if (empty($token)) {
            return null;
        }

        try {
            $parsedToken = $this->config->parser()->parse($token);
        } catch (Throwable $e) {
            log_message('error', 'Failed to parse JWT: ' . $e->getMessage());
            return null;
        }

        $interfacesToCheck = [
            '\\Lcobucci\\JWT\\Token\\UnencryptedToken',
            '\\Lcobucci\\JWT\\UnencryptedToken',
        ];

        $classesToCheck = [
            '\\Lcobucci\\JWT\\Token\\Plain',
        ];

        $isUnencryptedToken = false;

        foreach ($interfacesToCheck as $interface) {
            if (interface_exists($interface) && $parsedToken instanceof $interface) {
                $isUnencryptedToken = true;
                break;
            }
        }

        if (! $isUnencryptedToken) {
            foreach ($classesToCheck as $class) {
                if (class_exists($class) && $parsedToken instanceof $class) {
                    $isUnencryptedToken = true;
                    break;
                }
            }
        }

        if (! $isUnencryptedToken && method_exists($parsedToken, 'claims')) {
            $isUnencryptedToken = true;
        }

        if (! $isUnencryptedToken) {
            return null;
        }

        $constraints = $this->config->validationConstraints();

        if (! $this->config->validator()->validate($parsedToken, ...$constraints)) {
            return null;
        }

        $sentinel = new \stdClass();

        $nameClaim = $this->getClaimValue($parsedToken, 'name', $sentinel);

        return [
            'id'    => $this->getClaimValue($parsedToken, 'uid'),
            'email' => $this->getClaimValue($parsedToken, 'email'),
            'role'  => $this->getClaimValue($parsedToken, 'role'),
            'name'  => $nameClaim === $sentinel ? null : $nameClaim,
        ];
    }

    /**
     * Extracts a claim value from a token, supporting multiple JWT library versions.
     */
    private function getClaimValue(object $token, string $claim, $default = null)
    {
        if (method_exists($token, 'claims')) {
            $claims = $token->claims();

            if (is_object($claims)) {
                if (method_exists($claims, 'has') && method_exists($claims, 'get')) {
                    return $claims->has($claim) ? $claims->get($claim) : $default;
                }

                if (method_exists($claims, 'all')) {
                    $data = $claims->all();
                    if (is_array($data)) {
                        return $data[$claim] ?? $default;
                    }
                }

                if (method_exists($claims, 'get')) {
                    try {
                        return $claims->get($claim);
                    } catch (Throwable $e) {
                        return $default;
                    }
                }
            }

            if (is_array($claims)) {
                return $claims[$claim] ?? $default;
            }
        }

        if (method_exists($token, 'hasClaim') && method_exists($token, 'getClaim')) {
            return $token->hasClaim($claim) ? $token->getClaim($claim) : $default;
        }

        if (method_exists($token, 'getClaim')) {
            try {
                return $token->getClaim($claim, $default);
            } catch (Throwable $e) {
                return $default;
            }
        }

        return $default;
    }
}
