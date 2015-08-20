<?php

namespace Liip\MonitorBundle\Command;

use Liip\MonitorBundle\Runner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ListChecksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('monitor:list')
            ->setDescription('Lists Health Checks')
            ->addOption('reporters', 'r', InputOption::VALUE_NONE, 'List registered additional reporters')
            ->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'Check group')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getOption('group') ?: $this->getContainer()->getParameter('liip_monitor.default_group');

        switch (true) {
            case $input->getOption('reporters'):
                $this->listReporters($output, $group);
                break;
            default:
                $this->listChecks($output, $group);
                break;
        }
    }

    protected function listChecks(OutputInterface $output, $group = null)
    {
        $runner = $this->getRunner($group);

        $checks = $runner->getChecks();

        if (0 === count($checks)) {
            $output->writeln('<error>No checks configured.</error>');
        }

        foreach ($runner->getChecks() as $alias => $check) {
            $output->writeln(sprintf('<info>%s</info> %s', $alias, $check->getLabel()));
        }
    }

    protected function listReporters(OutputInterface $output, $group = null)
    {
        $reporters = $this->getRunner($group)->getAdditionalReporters();
        if (0 === count($reporters)) {
            $output->writeln('<error>No additional reporters configured.</error>');
        }

        foreach (array_keys($reporters) as $reporter) {
            $output->writeln($reporter);
        }
    }

    /**
     * @param string $group
     *
     * @return Runner
     */
    private function getRunner($group = null)
    {
        $container = $this->getContainer();

        if ($container->has('liip_monitor.runner_' . $group)) {
            return $container->get('liip_monitor.runner_' . $group);
        } else {
            return $container->get('liip_monitor.runner');
        }
    }
}
