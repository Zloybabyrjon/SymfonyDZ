<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:user',
    description: 'User management commands',
    hidden: false,
    aliases: ['user']
)]
class CreateUserCommand extends Command
{
    private const RECIPIENT_EMAIL = 'egortumanov812@gmail.com';
    private const SENDER_EMAIL = 'egortumanov812@gmail.com';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DepartmentRepository $departmentRepository,
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        #[Autowire('%kernel.project_dir%')] 
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('action', null, InputOption::VALUE_REQUIRED, 'Action to perform: create or export')
            ->addOption('first-name', null, InputOption::VALUE_OPTIONAL)
            ->addOption('last-name', null, InputOption::VALUE_OPTIONAL)
            ->addOption('age', null, InputOption::VALUE_OPTIONAL)
            ->addOption('status', null, InputOption::VALUE_OPTIONAL)
            ->addOption('email', null, InputOption::VALUE_OPTIONAL)
            ->addOption('telegram', null, InputOption::VALUE_OPTIONAL)
            ->addOption('address', null, InputOption::VALUE_OPTIONAL)
            ->addOption('department', null, InputOption::VALUE_OPTIONAL)
            ->addOption('image', null, InputOption::VALUE_OPTIONAL)
            ->addOption('recipient-email', null, InputOption::VALUE_OPTIONAL, 'Email address to send export to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getOption('action');

        if ($action === 'create') {
            return $this->createUser($input, $io);
        } elseif ($action === 'export') {
            return $this->exportUsers($input, $io);
        } else {
            $io->error('Неизвестное действие. Используйте --action=create или --action=export');
            return Command::FAILURE;
        }
    }

    private function createUser(InputInterface $input, SymfonyStyle $io): int
    {
        $firstName = $input->getOption('first-name') ?? $io->ask('Введите имя');
        $lastName = $input->getOption('last-name') ?? $io->ask('Введите фамилию');
        $age = $input->getOption('age') ?? $io->ask('Введите возраст');
        $status = $input->getOption('status') ?? $io->ask('Введите статус');
        $email = $input->getOption('email') ?? $io->ask('Введите эл.почту');
        $telegram = $input->getOption('telegram') ?? $io->ask('Введите телеграм');
        $address = $input->getOption('address') ?? $io->ask('Введите адрес');
        $departmentId = $input->getOption('department') ?? $io->ask('Введите ID отдела');
        $image = $input->getOption('image') ?? $io->ask('Введите путь фотографии');

        if (!$firstName || !$lastName || !$age || !$status || !$email || !$telegram || !$address || !$departmentId || !$image) {
            $io->error('Ошибка: Не все данные были переданы.');
            return Command::FAILURE;
        }

        $user = new User();
        $department = $this->departmentRepository->find($departmentId);

        if (!$department) {
            $io->error('Отдел с указанным ID не найден.');
            return Command::FAILURE;
        }

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

    private function exportUsers(InputInterface $input, SymfonyStyle $io): int
    {
        $recipientEmail = $input->getOption('recipient-email') ?? self::RECIPIENT_EMAIL;

        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $io->error('В базе данных нет пользователей для экспорта.');
            return Command::FAILURE;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'ID', 'Имя', 'Фамилия', 'Возраст', 'Статус', 
            'Email', 'Telegram', 'Адрес', 'Отдел', 'Фото'
        ], null, 'A1');

        $row = 2;
        foreach ($users as $user) {
            $sheet->fromArray([
                $user->getId(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getAge(),
                $user->getStatus(),
                $user->getEmail(),
                $user->getTelegram(),
                $user->getAddress(),
                $user->getDepartment()?->getName() ?? 'N/A',
                $user->getImage()
            ], null, "A{$row}");
            $row++;
        }

        $fileName = 'users_export_' . date('Y-m-d_His') . '.xlsx';
        $filePath = $this->projectDir . '/var/' . $fileName;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $io->success('Excel файл создан: ' . $filePath);

        $email = (new Email())
            ->from(self::SENDER_EMAIL)
            ->to($recipientEmail)
            ->subject('Экспорт пользователей - ' . date('Y-m-d H:i:s'))
            ->text('Во вложении находится экспорт данных пользователей.')
            ->attachFromPath($filePath);

        try {
            $this->mailer->send($email);
            $io->success('Отчет отправлен на email: ' . $recipientEmail);
            
        } catch (\Exception $e) {
            $io->error('Ошибка при отправке email: ' . $e->getMessage());
            $io->note('Файл сохранен локально: ' . $filePath);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}