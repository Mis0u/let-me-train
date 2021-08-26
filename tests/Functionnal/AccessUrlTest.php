<?php

namespace App\Tests\Functionnal;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessUrlTest extends WebTestCase
{
    public const USER_AUTHENTICATE = 'user2@gmail.com';

    /**
     * @test
     * @dataProvider provideUrl
     */
    public function url_is_accessible_as_anonymous_user(string $url): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, $url);

        $this->assertResponseIsSuccessful('L\'url '.$url.' est inaccessible');
    }

    /**
     * @test
     * @dataProvider provideUrl
     */
    public function url_is_not_accessible_as_authenticate_user(string $url): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail(self::USER_AUTHENTICATE);

        $client->loginUser($testUser);

        $client->request(Request::METHOD_GET, $url);

        $this->assertResponseRedirects(
            '/' . $testUser->getSlug(),
            Response::HTTP_FOUND,
            'Il n\'y a pas eu de redirection'
        );

        $client->followRedirect();

        $this->assertSelectorExists('h1', 'Le sélecteur n\'existe pas');

        $this->assertSelectorTextContains('h1', 'Bienvenue', 'Le sélecteur ne contient pas le texte');
    }

    public function provideUrl(): \Generator
    {
        yield['/'];
        yield['/inscription'];
        yield['mot-de-passe-oublie'];
    }
}
