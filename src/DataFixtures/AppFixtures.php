<?php

namespace App\DataFixtures;

use App\Entity\Exercice;
use App\Entity\Repetition;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Muscle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $target = ['jambes', 'mollets', 'biceps', 'triceps', 'dos', 'pectoraux','épaules'];
        $exos = ['curl', 'squat', 'élévation', 'dips', 'traction', 'fentes'];

        for ($i = 1; $i <= 5; $i++) {
            $gender = rand(1, 2);
            $randomNumber = rand(1, 9);
            $user = new User();

            for ($j = 0; $j < $randomNumber; $j++) {
                $upperOrLower = rand(1, 2);
                $muscle = new Muscle();

                for ($k = 0; $k < $randomNumber; $k++) {
                    $exercice = new Exercice();
                    for ($m = 0; $m < 200; $m++) {
                        $rep = new Repetition();
                        $randomDays = rand(1, 100);
                        $date = new \DateTimeImmutable('now');
                        $interval = 'P' . $randomDays . 'D';

                        $rep->setNumber(rand(1, 20))
                            ->setExercice($exercice)
                            ->setCreatedAt($date->sub(new \DateInterval($interval)));
                        $manager->persist($rep);
                    }

                    $exercice->setName((string) array_rand(array_flip($exos)))
                        ->setMuscle($muscle);
                    $manager->persist($exercice);
                }

                $muscle->setTarget((string) array_rand(array_flip($target)))
                    ->setUpperOrLowerBody($upperOrLower === 1 ? 'haut' : 'bas')
                    ->setMuscleOwner($user);
                $manager->persist($muscle);
            }

            $user->setAlias("user$i")
                ->setEmail("user$i@gmail.com")
                ->setGender($gender === 1 ? 'male' : 'female')
                ->setCountry('France')
                ->setPassword($this->encoder->hashPassword($user, 'Pass_1234'));

            $manager->persist($user);
        }
        $admin = new User();

        $admin->setAlias('Admin')
            ->setEmail('admin@gmail.com')
            ->setGender('male')
            ->setCountry('France')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->encoder->hashPassword($admin, 'Pass_1234'));

        $manager->persist($admin);
        $manager->flush();
    }
}
