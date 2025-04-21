<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
    hidden: false,
    aliases: ['app:add-user']
)]
class CreateUserCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connect = new \mysqli("localhost", "root", "AXZVOPBA1023", "lab-symfony");
        if ($connect->connect_error) {
            $output->writeln("Ошибка: " . $connect->connect_error);
            return Command::FAILURE;
        }

        $sql = "INSERT INTO User (first_name, last_name, age, status, email, telegram, address, department_id, image)
                VALUES ('drj', 'tdjs', '54', 'hRSh', 'zfdn', 'zdfnz', 'zfdnzd', '1', '/public/image/Blue.png')";

        if ($connect->query($sql)) {
            $output->writeln("Данные успешно добавлены");
        } else {
            $output->writeln("Ошибка: " . $connect->error);
            return Command::FAILURE;
        }

        $connect->close();
        return Command::SUCCESS;
    }
}
