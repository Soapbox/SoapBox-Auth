<?php

namespace App\Services;

use App\Exceptions\UserNotFoundException;
use App\Libraries\iJWTLibrary;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;

class TokenGeneratorService
{
    protected $code; //refers to oauth_code from provider
    protected $token;
    protected $payload;
    protected $provider; //ex google, slack, microsoft
    protected $jwt_library;
    protected $socialProviderUser;

    const ISS = 'http://auth-server.test';
    const AUD = 'http://api-gateway.test';

    public function __construct(iJWTLibrary $jwtLibrary)
    {
        $this->jwt_library = $jwtLibrary;
    }

    /**
     * Generate jwt token
     * @param array $args
     * @throws UserNotFoundException
     */
    public function generateToken($args = [])
    {
        $this->setProvider(Arr::get($args, 'provider'));
        $this->setCode(Arr::get($args, 'code'));

        $this->socialProviderUser = Socialite::driver($this->provider)->userFromToken($this->code);

        $userExist = true; //assumption

        if ($userExist) {
            $this->payload = $this->generatePayload();
            $this->token = $this->jwt_library->encode($this->payload);
        } else {
            throw new UserNotFoundException('User not found.: '.$this->socialProviderUser->getEmail(), Response::HTTP_NOT_FOUND);
        }

        return $this->token;
    }

    /**
     * Generate payload
     * @return array
     */
    protected function generatePayload(): array
    {
        return [
            'iss' => self::ISS,
            'aud' => self::AUD,
            'iat' => time(),
            'exp' => strtotime('+1 '.$this->jwt_library->getExpiry()),
            'name' => $this->socialProviderUser->getName(),
            'email' => $this->socialProviderUser->getEmail(),
        ];
    }

    protected function setProvider($provider)
    {
        if (empty($provider) || ! in_array($provider, config('support.providers'))) {
            throw new \InvalidArgumentException('Provider must be set', Response::HTTP_FORBIDDEN);
        }

        $this->provider = $provider;
    }

    protected function setCode($code)
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Code must be set', Response::HTTP_FORBIDDEN);
        }

        $this->code = $code;
    }
}
