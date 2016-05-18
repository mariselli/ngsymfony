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


class ExtractManualCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('mariselli:ng-symfony:states:manual')
            ->setDescription('Generate states from annotations')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('constant-name', 'cn', InputOption::VALUE_OPTIONAL,
                        'Set angular constant name that contains all the states', '$ngStates'),
                    new InputOption('module-name', 'mn', InputOption::VALUE_OPTIONAL,
                        'Set angular module name that contains the constant', 'ngSymfony.states'),
                    new InputOption('file-path', 'fp', InputOption::VALUE_OPTIONAL,
                        'Set where file with states will be saved', '/Resources/ui-states.js'),
                    new InputOption('url', 'u', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                        'Set what path will be scanned searching states', []),
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $constantName = $input->getOption('constant-name');
        $moduleName = $input->getOption('module-name');
        $url = $input->getOption('url');
        $filePath = $input->getOption('file-path');

        $extractor = $this->getContainer()->get('ng_symfony.extractor');


        $io = new SymfonyStyle($input, $output);
        $io->title('Creating angular-ui status');
        try {
            $extractor->scanRoutesAndSaveFile($url);
            $extractor->saveStateFile($moduleName, $constantName, $filePath);

            $io->success('Angular UI States exported!' . "\n\nPath: " . $filePath);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

    }
}