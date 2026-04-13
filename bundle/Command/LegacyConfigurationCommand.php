<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter;
use eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Ibexa\Core\MVC\Symfony\ConfigDumperInterface;
use eZ\Publish\Core\MVC\Legacy\Kernel\Loader;
use Symfony\Component\HttpKernel\KernelInterface;

class LegacyConfigurationCommand extends Command
{
    /** @var Loader */
    private $legacyKernelLoader;

    /** @var KernelInterface */
    private $kernel;

    /** @var ConfigurationConverter */
    private $configurationConverter;

    /** @var ConfigurationDumper */
    private $configurationDumper;

    public function __construct(
        Loader $legacyKernelLoader,
        KernelInterface $kernel,
        ConfigurationConverter $configurationConverter,
        ConfigurationDumper $configurationDumper
    ) {
        parent::__construct();
        $this->legacyKernelLoader = $legacyKernelLoader;
        $this->kernel = $kernel;
        $this->configurationConverter = $configurationConverter;
        $this->configurationDumper = $configurationDumper;
    }
    protected function configure()
    {
        $this
            ->setName('exponential:legacy:configure')
            ->setAliases(['ezpublish:configure'])
            ->setDefinition(
                [
                    new InputArgument('package', InputArgument::REQUIRED, 'Name of the installed package. Used to generate the settings group name. Example: ezdemo_site'),
                    new InputArgument('adminsiteaccess', InputArgument::REQUIRED, 'Name of your admin siteaccess. Example: ezdemo_site_admin'),
                    new InputOption('backup', null, InputOption::VALUE_NONE, 'Makes a backup of existing files if any'),
                ]
            )
            ->setDescription('Creates the ezpublish 5 configuration based on an existing ezpublish_legacy')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> creates the ezpublish 5 configuration,
based on an existing ezpublish_legacy installation.

Settings will be picked based on the default siteaccess.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $package = $input->getArgument('package');
        $adminSiteaccess = $input->getArgument('adminsiteaccess');
        $this->legacyKernelLoader->setBuildEventsEnabled(false);
        $kernel = $this->kernel;

        /** @var $configurationConverter \eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter */
        $configurationConverter = $this->configurationConverter;
        /** @var $configurationDumper \eZ\Bundle\EzpublishLegacyBundle\SetupWizard\ConfigurationDumper */
        $configurationDumper = $this->configurationDumper;
        $configurationDumper->addEnvironment($kernel->getEnvironment());

        $options = ConfigDumperInterface::OPT_DEFAULT;
        if ($input->getOption('backup')) {
            $options |= ConfigDumperInterface::OPT_BACKUP_CONFIG;
        }
        $configurationDumper->dump($configurationConverter->fromLegacy($package, $adminSiteaccess), $options);

        $output->writeln('Configuration written to ezpublish.yml and environment related ezpublish configuration files.');
        $output->writeln('Make sure to apply the config relevant to your install to your ezplatform.yml file.');

        return 0;
    }
}
