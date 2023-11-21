<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class WishControllerTest extends WebTestCase
{
    public function testAccessIsDeniedWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/wish/create');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    private function getUser(): UserInterface
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        return $userRepository->findOneBy(["email" => "admin@bucket-list.fr"]);
    }

    public function testAccessIsGrantedWhenLoggedIn(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/wish/create');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testFormIsInvalidIfNoCategory(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/wish/create');
        $client->submitForm("Create", [
            'wish[title]' => 'Test sdafjf df',
            'wish[description]' => 'Test dsf dsaf kldsj flkds lf',
            'wish[category]' => '',
        ]);

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testFormIsInvalidIfTitleTooShort(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/wish/create');
        $client->submitForm("Create", [
            'wish[title]' => 'a',
            'wish[description]' => 'Test dsf dsaf kldsj flkds lf',
            'wish[category]' => '1',
        ]);

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testFormIsInvalidIfTitleTooLong(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/wish/create');
        $client->submitForm("Create", [
            'wish[title]' => str_repeat("a", 181),
            'wish[description]' => 'Test dsf dsaf kldsj flkds lf',
            'wish[category]' => '1',
        ]);

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testFormIsInvalidIfDescriptionTooLong(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/wish/create');
        $client->submitForm("Create", [
            'wish[title]' => 'afdj ksdfldskjl fjdsl',
            'wish[description]' => str_repeat("a", 3000),
            'wish[category]' => '1',
        ]);

        $this->assertEquals(422, $client->getResponse()->getStatusCode());
    }

    public function testFormIsValidWithValidData(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUser());

        $crawler = $client->request('GET', '/wish/create');
        $client->submitForm("Create", [
            'wish[title]' => 'test valide',
            'wish[description]' => 'description de test',
            'wish[category]' => '1',
        ]);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertRouteSame('wish_detail');
    }
}
