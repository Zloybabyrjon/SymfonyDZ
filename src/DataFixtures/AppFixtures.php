<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $departmentArray = [
            'Фамилия',
            'Телеграмм'
        ];

        foreach ($departmentArray as $i => $name) {
            $department = new Department();
            $department->setName($name);
            $manager->persist($department);
            $this->addReference('department_' . $i, $department);
            $departments[] = $department;
        }

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setLastName('lastName' . $i);
            $user->setFirstName('firstName' . $i);
            $user->setAge('age' . $i);
            $user->setStatus('status' . $i);
            $user->setEmail('email' . $i);
            $user->setTelegram('telegram' . $i);
            $user->setAddress('address' . $i);
            $randomDepartmentId = array_rand($departments);
            $user->setDepartment($departments[$randomDepartmentId]);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
