<?php

namespace App\Tests\Functionnal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    public const EMAIL = 'user2@gmail.com';
    public const PASSWORD = 'Pass_1234';

    /**
     * @test
     * @dataProvider provideFields
     */
    public function it_is_fail_with_incorrect_fields(string $email, string $password, string $error, string $field)
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful('La page n\'a pas été atteinte');

        $this->assertPageTitleSame('Connexion', 'L\'onglet ne contient pas le texte attendu');

        $this->assertSelectorTextContains(
            'h1',
            'Connecte toi',
            'Le sélecteur ou le texte est absent'
        );

        $crawler = $client->submitForm('Go', [
            'email'    => $email ,
            'password' => $password
        ]);

        $this->assertResponseRedirects('/',
            Response::HTTP_FOUND,
            'Le form contenait un champ invalid est n\'a pas renvoyé vers la page login');

        $client->followRedirect();

        $this->assertSelectorExists('div.alert-danger', 'Il existe pas');

        $this->assertSelectorTextContains(
            'div.alert.alert-danger',
            $error,
            'L\'erreur ne s\'est pas affiché pour le champ ' . $field);

    }


    /**
     * @test
     */
    public function it_is_success()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful('La page n\'a pas été atteinte');

        $this->assertPageTitleSame('Connexion', 'L\'onglet ne contient pas le texte attendu');

        $this->assertSelectorExists(
            'a.forgotten_pass',
            'Le sélecteur pour le mot de passe oublié n\'existe pas'
        );

        $this->assertSelectorExists(
            'a.registration',
            'Le sélecteur pour l\'inscription n\'existe pas'
        );

        $this->assertSelectorTextContains(
            'h1',
            'Connecte toi',
            'Le sélecteur ou le texte est absent'
        );

        $crawler = $client->submitForm('Go', [
            'email'    => self::EMAIL ,
            'password' => self::PASSWORD
        ]);

        $client->followRedirect();

        $this->assertSelectorTextContains(
            'h1',
            'Bienvenue',
            'Le sélecteur H1 ne contient pas le texte attendu'
        );

        $this->assertPageTitleContains('User2', 'L\'onglet ne contient pas le texte attendu');
    }

    public function it_is_block_after_three_attempt_for_unknown_user()
    {

    }


    public function provideFields()
    {
        yield['email-introuvable@test.com', 'password','Les identifiants sont incorrects','email'];
        yield['user2@gmail.com', 'password','Le mot de passe est incorrect','password'];
    }
}