<?php

namespace RainmakerProfileManagerCliBundle;

//use Symfony\Component\Console\Application as ParentApplication;
use Symfony\Bundle\FrameworkBundle\Console\Application as ParentApplication;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Shell;

class Application extends ParentApplication
{

    protected $output;

    /**
     * {@inheritdoc}
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setName('Rainmaker CLI');
        $this->setVersion('1.x-dev');

        $this->setDefaultTimezone();

        $this->getCommands();
        $this->addCommands($this->getCommands());

        $this->setDefaultCommand('welcome');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCommands()
    {
        if ($this->getKernel()->getEnvironment() != 'prod') {
            parent::registerCommands();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
        ));
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getCommands()
    {
        $commands = array();
        $commands[] = new Command\WelcomeCommand();
        $commands[] = new Command\ProfileAddCommand();
        $commands[] = new Command\ProfileRemoveCommand();
        $commands[] = new Command\ProfileShowUpdatesCommand();
        $commands[] = new Command\ProfileUpdateCommand();
        $commands[] = new Command\NodeAddCommand();
        $commands[] = new Command\NodeRemoveCommand();
        return $commands;
    }

    /**
     * @inheritdoc
     */
    public function getHelp()
    {
        $messages = array(
            $this->getLongVersion(),
            '',
            '<comment>Global options:</comment>',
        );

        foreach ($this->getDefinition()->getOptions() as $option) {
            $messages[] = sprintf('  %-29s %s %s',
                '<info>--'.$option->getName().'</info>',
                $option->getShortcut() ? '<info>-'.$option->getShortcut().'</info>' : '  ',
                $option->getDescription()
            );
        }

        return implode(PHP_EOL, $messages);
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // Set the input to non-interactive if the yes or no options are used.
        if ($input->hasParameterOption(array('--yes', '-y')) || $input->hasParameterOption(array('--no', '-n'))) {
            $input->setInteractive(false);
        }
        // Enable the shell.
        elseif ($input->hasParameterOption(array('--shell', '-s'))) {
            $shell = new Shell($this);
            $shell->run();
            return 0;
        }

        $this->output = $output;
        return parent::doRun($input, $output);
    }

    /**
     * @return OutputInterface
     */
    public function getOutput() {
        if (isset($this->output)) {
            return $this->output;
        }
        $stream = fopen('php://stdout', 'w');
        return new StreamOutput($stream);
    }

    /**
     * Set the default timezone.
     *
     * PHP 5.4 has removed the autodetection of the system timezone,
     * so it needs to be done manually.
     * UTC is the fallback in case autodetection fails.
     */
    protected function setDefaultTimezone() {
        $timezone = 'UTC';
        if (is_link('/etc/localtime')) {
            // Mac OS X (and older Linuxes)
            // /etc/localtime is a symlink to the timezone in /usr/share/zoneinfo.
            $filename = readlink('/etc/localtime');
            if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
                $timezone = substr($filename, 20);
            }
        } elseif (file_exists('/etc/timezone')) {
            // Ubuntu / Debian.
            $data = file_get_contents('/etc/timezone');
            if ($data) {
                $timezone = trim($data);
            }
        } elseif (file_exists('/etc/sysconfig/clock')) {
            // RHEL/CentOS
            $data = parse_ini_file('/etc/sysconfig/clock');
            if (!empty($data['ZONE'])) {
                $timezone = trim($data['ZONE']);
            }
        }

        date_default_timezone_set($timezone);
    }

}
