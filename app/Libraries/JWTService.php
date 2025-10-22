<?php

namespace App\Libraries;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\UnencryptedToken;
use Lcobucci\JWT\UnencryptedToken as LegacyUnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
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

        if (class_exists(StrictValidAt::class)) {
            $constraints[] = new StrictValidAt($clock);
        } elseif (class_exists(LooseValidAt::class)) {
            $constraints[] = new LooseValidAt($clock);
        }

        $this->config->setValidationConstraints(...$constraints);
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

        if (! $parsedToken instanceof UnencryptedToken && ! $parsedToken instanceof LegacyUnencryptedToken) {
            return null;
        }

        $constraints = $this->config->validationConstraints();

        if (! $this->config->validator()->validate($parsedToken, ...$constraints)) {
            return null;
        }

        $claims = $parsedToken->claims();

        return [
            'id'    => $claims->get('uid'),
            'email' => $claims->get('email'),
            'role'  => $claims->get('role'),
            'name'  => $claims->has('name') ? $claims->get('name') : null,
        ];
    }
}
