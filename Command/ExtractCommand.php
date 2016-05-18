<?php

namespace Mariselli\NgSymfonyBundle\Command;

use Mariselli\NgSymfonyBundle\Annotation\UiRouterState;
use Mariselli\NgSymfonyBundle\Annotation\UiRouterView;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;


class ExtractCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('mariselli:ng-symfony:states')
            ->setDescription('Generate states from annotations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extractor = $this->getContainer()->get('ng_symfony.extractor');
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating angular-ui status');
        try {
            $extractor->scanByConfig();
            $io->success('Angular UI States exported!');
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}