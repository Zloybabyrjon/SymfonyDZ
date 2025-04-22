<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:add-user']
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private DepartmentRepository $departmentRepository;

    public function __construct(EntityManagerInterface $entityManager, DepartmentRepository $departmentRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->departmentRepository = $departmentRepository;
    }

    protected function configure(): void
    {
        $this
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('age', null, InputOption::VALUE_REQUIRED)
            ->addOption('status', null, InputOption::VALUE_REQUIRED)
            ->addOption('email', null, InputOption::VALUE_REQUIRED)
            ->addOption('telegram', null, InputOption::VALUE_REQUIRED)
            ->addOption('address', null, InputOption::VALUE_REQUIRED)
            ->addOption('department', null, InputOption::VALUE_REQUIRED)
            ->addOption('image', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstName = $input->getOption('first-name');
        $io->ask('Введите имя');
        $lastName = $input->getOption('last-name');
         $io->ask('Введите фамилию');
        $age = $input->getOption('age');
         $io->ask('Введите возраст');
        $status = $input->getOption('status');
         $io->ask('Введите статус');
        $email = $input->getOption('email');
         $io->ask('Введите эл.почту');
        $telegram = $input->getOption('telegram');
         $io->ask('Введите телеграм');
        $address = $input->getOption('address'); 
        $io->ask('Введите адрес');
        $departmentId = $input->getOption('department');
         $io->ask('Введите ID отдела');
        $image = $input->getOption('image');
        $io->ask('Введите путь фотографии');

        if (!$firstName || !$lastName || !$age || !$status || !$email || !$telegram || !$address || !$departmentId || !$image) {
            $io->error('Ошибка: Не все данные были переданы.');
            return Command::FAILURE;
        }

        
        $user = new User();
        $department = $this->departmentRepository->find($departmentId);

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setAge((int)$age);
        $user->setStatus($status);
        $user->setEmail($email);
        $user->setTelegram($telegram);
        $user->setAddress($address);
        $user->setDepartment($department);
        $user->setImage($image);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Пользователь $firstName $lastName успешно добавлен в базу данных.");

        return Command::SUCCESS;
    }
}
