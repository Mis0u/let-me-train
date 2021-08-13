<?php

namespace App\Tests\Functionnal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessUrlTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider provideUrlAccessibleForAnonymousUser
     */
    public function url_is_accessible_as_anonymous_user(string $url): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, $url);

        $this->assertResponseIsSuccessful('L\'url '.$url.' est inaccessible');
    }

    public function provideUrlAccessibleForAnonymousUser(): \Generator
    {
        yield['/inscription'];
    }
}
