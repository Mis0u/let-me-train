<?php

namespace App\Tests\Functionnal;

use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\AsciiSlugger;

class RegistrationTest extends WebTestCase
{
    public const USER_EMAIL = 'new-user@gmail.com';
    public const PASSWORD = 'Pass_1234';
    public const ALIAS = 'New user';
    public const OVER_MAX_CHARS = "this text is over the maximum chars";

    /**
     * @test
     * @dataProvider provideFields
     */
    public function it_is_fail_with_incorrect_fields(
        string $email,
        string $alias,
        string $password,
        string $repeat_password,
        string $selector,
        string $error,
        string $field
    )
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, 'inscription');

        $crawler = $client->submitForm('S\'enregistrer', [
            'registration[email]' => $email,
            'registration[alias]' => $alias,
            'registration[plainPassword][password]' => $password,
            'registration[plainPassword][confirm_password]' => $repeat_password,
        ]);

        $this->assertSelectorTextContains($selector, $error, 'L\'erreur ne s\'est pas affiché pour le champ ' . $field);

        $this->assertSelectorTextContains('button', 'S\'enregistrer', 'Le button S\'enregistrer est introuvable');
    }

    /**
     * @test
     */
    public function it_is_redirect_with_wrong_captcha()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, 'inscription');

        $crawler = $client->submitForm('S\'enregistrer', [
            'registration[email]' => self::USER_EMAIL,
            'registration[alias]' => self::ALIAS,
            'registration[plainPassword][password]' => self::PASSWORD,
            'registration[plainPassword][confirm_password]' => self::PASSWORD,
            'registration[captcha]' => 'captcha'
        ]);

        $this->assertResponseRedirects("https://www.youtube.com/watch?v=dQw4w9WgXcQ");
    }

    /**
     * @test
     */
    public function it_is_successfull()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, 'inscription');

        $crawler = $client->submitForm('S\'enregistrer', [
            'registration[email]'                           => self::USER_EMAIL,
            'registration[alias]'                           => self::ALIAS,
            'registration[plainPassword][password]'         => self::PASSWORD,
            'registration[plainPassword][confirm_password]' => self::PASSWORD,
        ]);

        $slugger = new Slugify();

        $this->assertResponseRedirects(
            '/' . $slugger->slugify(self::ALIAS),
            Response::HTTP_FOUND,
            'La redirection après inscription n\' a pas eu lieu'
        );

        $client->followRedirect();

        $this->assertRouteSame('app_user', [], 'La route attendu n\'est pas la bonne');

        $this->assertPageTitleContains(self::ALIAS, 'L\'onglet ne contient pas le texte attendu');

        $this->assertSelectorExists('div.alert-success', 'Le sélecteur attendu n\'apparaît pas');
    }

    public function provideFields(): \Generator
    {
        //email
        yield[
            'wrong_email',
            self::ALIAS,
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'Ce format d\'email est invalide',
            'email => invalide'
        ];
        yield[
            '',
            self::ALIAS,
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'L\'email ne peut être vide',
            'email => vide'
        ];
        yield[
            'user2@gmail.com',
            self::ALIAS,
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'Cet email est déjà utilisé',
            'email => unique'
        ];
        //alias
        yield[
            self::USER_EMAIL,
            '',
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'Tu dois renseigner un pseudo',
            'alias => vide'
        ];
        yield[
            self::USER_EMAIL,
            'a',
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'Ton pseudo doit faire au minimum 2 caractères',
            'alias => min'
        ];
        yield[
            self::USER_EMAIL,
            str_repeat('max', 33),
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'Ton pseudo doit faire au maximum 20 caractères',
            'alias => max'
        ];
        yield[
            self::USER_EMAIL,
            'user1',
            self::PASSWORD,
            self::PASSWORD,
            'ul li',
            'Ce pseudo est déjà utilisé',
            'alias => unique'
        ];
        //password
        yield[
            self::USER_EMAIL,
            self::ALIAS,
            '',
            '',
            'ul li',
            'Les mots de passe doivent être rempli',
            'password => vide'
        ];
        yield[
            self::USER_EMAIL,
            self::ALIAS,
            'coucou',
            'koukou',
            'ul li',
            'Les mots de passe doivent être identique',
            'password => identique'
        ];
        yield[
            self::USER_EMAIL,
            self::ALIAS,
            'azerty57',
            'azerty57',
            'ul li',
            'Ton mot de passe doit avoir au minimum 1 majuscule, 1 minuscule et 1 chiffre',
            'password => regex'
        ];
        yield[
            self::USER_EMAIL,
            self::ALIAS,
            'azertyYy',
            'azertyYy',
            'ul li',
            'Ton mot de passe doit avoir au minimum 1 majuscule, 1 minuscule et 1 chiffre',
            'password => regex'
        ];
        //captcha
        yield[
            self::USER_EMAIL,
            self::ALIAS,
            'coucou',
            'koukou',
            'ul li',
            'Les mots de passe doivent être identique',
            'captcha => valide'
        ];
    }
}
