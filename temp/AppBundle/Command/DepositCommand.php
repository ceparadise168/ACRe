<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * This command will deposit amount and there are some Arguments.
 * 1.Argument times will set how much times you want to deposit.
 * 2.Argument sleep will set how much times from the previous deposit behavior to the next one.
 * 3.Argument id will set which id to deposit.
 * 4.Argument amount will set who much cash to deposit.
 */
class DepositCommand extends Command
{
    protected function  configure()
    {
        $this->setName('app:deposit')
            ->setDescription('Deposit 100 times and sleep 0.5s between each deposits.')
            ->setHelp('This command allows you to deposit use   "app:deposit [deposit times] [sleep second] [id] [amount]"')
            ->addArgument('times', InputArgument::REQUIRED, 'The times of deposit.')
            ->addArgument('sleep', InputArgument::REQUIRED, 'sleep time.')
            ->addArgument('id', InputArgument::REQUIRED, 'id.')
            ->addArgument('amount', InputArgument::REQUIRED, 'amount.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start Deposit');
        $output->writeln('=============');
        $output->writeln('Deposit Times: '. $input->getArgument('times'));
        $output->writeln('Sleep second: '. $input->getArgument('sleep'));
        $output->writeln('Deposit id: '. $input->getArgument('id'));
        $output->writeln('Deposit amount: '. $input->getArgument('amount'));

        $id = $input->getArgument('id');
        $amount = $input->getArgument('amount');
        $total = $input->getArgument('times');

        for ($i = 0; $i < $total; $i++) {
            $percent = round($i*100/$total);

            if ($percent % 25 == 0){
                $output->writeln('');
                $output->writeln('');
                $output->writeln("...$percent%"."  Already deposited $i times");
            }
            sleep($input->getArgument('sleep'));
            $process = new Process("curl -H \"Content-Type: application/json\" -X POST -d '{\"id\":\"$id\",\"amount\":\"$amount\"}' http://127.0.0.1:8000/bank/deposit");
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
        $output->writeln('');
        $output->writeln('');
        $output->writeln("...100%  Already deposited $total times");
        $output->writeln('');
        $output->writeln('All Complete!');
    }
}
