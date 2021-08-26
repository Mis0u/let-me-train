<?php

namespace App\Tests\Functionnal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordForgottenTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    public const UNKNOWN_EMAIL = 'unknown@email.com';
    public const KNOWN_EMAIL   = 'user2@gmail.com';

    /**
     * @test
     */
    public function it_is_not_sending_link_with_unknown_user()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/mot-de-passe-oublie');

        $this->assertResponseIsSuccessful('La page n\'a pas été atteinte');

        $this->assertPageTitleSame('Mot de passe oublié', 'L\'onglet ne contient pas le texte attendu');

        $this->assertSelectorExists('button', 'Le sélecteur button n\'existe pas');

        $this->assertSelectorExists('input', 'Le sélecteur input n\'existe pas');

        $this->assertSelectorTextContains(
            'button',
            'Envoyer le lien',
            'Le sélecteur button ne contient pas le texte attendu'
        );

        $crawler = $client->submitForm('Envoyer le lien', [
            'email' => self::UNKNOWN_EMAIL
        ]);

        $this->assertResponseRedirects(
            '/mot-de-passe-oublie',
            Response::HTTP_FOUND,
            'Il n\'y a pas eu de redirection après l\'erreur'
        );

        $client->followRedirect();

        $this->assertSelectorExists('div.alert', 'Le sélecteur alert n\'existe pas');

        $this->assertSelectorTextContains(
            'div.alert',
            'vérifie à nouveau',
            'Le sélecteur div.alert.alert-danger ne contient pas le texte attendu'
        );
    }

    /**
     * @test
     */
    public function it_is_sending_link_with_known_user()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/mot-de-passe-oublie');

        $this->assertResponseIsSuccessful('La page n\'a pas été atteinte');

        $this->assertPageTitleSame('Mot de passe oublié', 'L\'onglet ne contient pas le texte attendu');

        $this->assertSelectorExists('button', 'Le sélecteur button n\'existe pas');

        $this->assertSelectorExists('input', 'Le sélecteur input n\'existe pas');

        $this->assertSelectorTextContains(
            'button',
            'Envoyer le lien',
            'Le sélecteur button ne contient pas le texte attendu'
        );

        $crawler = $client->submitForm('Envoyer le lien', [
            'email' => self::KNOWN_EMAIL
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('h1', 'Le sélecteur h1 n\'existe pas');

        $this->assertSelectorTextContains(
            'h1',
            'Le lien a été envoyé sur ton adresse email',
            'Le sélecteur h1 ne contient pas le texte attendu'
        );
    }
}