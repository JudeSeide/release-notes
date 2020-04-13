<?php declare(strict_types=1);

namespace App;

use chobie\Jira\Api;
use chobie\Jira\Issue;
use chobie\Jira\Issues\Walker;
use Dotenv\Dotenv;
use Pelago\Emogrifier;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    /** @var Api */
    protected $jira;

    /** @var array */
    protected $epics = [];

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->input = $input;
        $this->output = $output;
    }

    protected function configure(): void
    {
        $this->setName('release:notes')
            ->setDescription('Generate release notes from Jira resolved tasks')
            ->addOption('weeks', 'w', InputOption::VALUE_REQUIRED, 'Weeks since last notes')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date of last notes')
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'API username')
            ->addOption('token', 't', InputOption::VALUE_REQUIRED, 'API password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->configureJira();

        $query = $this->buildQuery();
        $filter = $this->parseFilter();

        $walker = new Walker($this->jira);
        $walker->push(sprintf($query, $filter), [
            'key',
            'Issue Type',
            'Epic Link',
            'Epic',
            'Summary',
        ]);

        ob_start();
        require(__DIR__ . '/template.php');
        $content = ob_get_clean();

        $emogrifier = new Emogrifier($content);
        $html = $emogrifier->emogrify();

        echo $html;
    }

    protected function configureJira(): void
    {
        $username = $this->input->getOption('username') ?: $_ENV['JIRA_USER'] ?? null;
        $token = $this->input->getOption('token') ?: $_ENV['JIRA_TOKEN'] ?? null;

        if (!$username || !$token) {
            $this->output->writeln('<error>Please enter a username and password</error>');
            exit;
        }

        $url = $_ENV['JIRA_URL'] ?? null;

        if (!$url) {
            $this->output->writeln('<error>Please set valid url for jira</error>');
            exit;
        }

        $this->jira = new Api($url, new Api\Authentication\Basic($username, $token));
    }

    protected function buildQuery(): string
    {
        $projects = explode('|', $_ENV['JIRA_PROJECTS'] ?? '');

        if (empty($projects)) {
            $this->output->writeln('<error>Please set jira projects to report</error>');
            exit;
        }

        $projects = array_map(static function ($project) {
            return 'project = "'.$project.'"';
        }, $projects);

        return '(' . implode(' OR ', $projects) . ') AND (status = Done OR resolution = Done) AND resolved >= %s ORDER BY "Epic Link", createdDate';
    }

    protected function parseFilter(): string
    {
        $weeks = $this->input->getOption('weeks');
        $date = $this->input->getOption('date');

        if (($weeks === null && $date === null) || ($weeks !== null && $date !== null)) {
            $this->output->writeln('<error>Please specify date OR weeks</error>');
            exit;
        }

        return $weeks !== null ? sprintf('-%dw', $weeks) : $date;
    }

    protected function getEpic(string $key): Issue
    {
        if (!isset($this->epics[$key])) {
            $this->epics[$key] = new Issue($this->jira->getIssue($key, ['Epic Color'])->getResult());
        }

        return $this->epics[$key];
    }
}
