<?php

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Exceptions\TerminusProcessException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\ProcessUtils;
use League\Container\ContainerAwareTrait;
use League\Container\ContainerAwareInterface;
use Pantheon\Terminus\Helpers\LocalMachineHelper;

class RemoteSqlCommand extends TerminusCommand implements SiteAwareInterface, ContainerAwareInterface
{

    use SiteAwareTrait;
    use ContainerAwareTrait;

    /**
     * @inheritdoc
     */
    protected $command = 'mysql';

    protected $environment;

    protected $site;

    /**
     * [mysq] Gets the mysql command string from the environment and runs it.
     *
     * @authorise
     *
     * @command mysql
     *
     * @param  string $site_env The Site Environment in the `site_name.env` format
     * @param string $sql_command  The sql statement to run on the remote
     * @usage terminus mysql <site>.<env> -- <command> the sql query to run on the db </command>
     */
    public function mysqlCommand($site_env, $sql_command = "")
    {

        $this->prepareEnvironment($site_env);

        // get the full mysql command from the connection info
        $info = $this->environment->connectionInfo();

        $site_label = $this->site->get('name') . '\'s ' . $this->environment->id . ' environment';
        $this->log()->notice('Connecting to the mysql database for ' . $site_label);

        if (!empty($info['mysql_command'])) {
            $command = str_replace($this->command . " ","", $info['mysql_command']);
            if (!empty($sql_command)) {
                $command .= ' -e "' . $sql_command . '"';
            }
            $this->log()->notice($command);
            return $this->executeCommand($command);
        }

        throw new TerminusProcessException('Couldn\'t retrieve info for ' . $site_label);
    }

    /**
     * Sets our environment variables.
     * @param  string $site_id  the full id of the site site_name.environment_id
     * @return void
     */
    protected function prepareEnvironment($site_id)
    {
        list($this->site, $this->environment) = $this->getSiteEnv($site_id);
        $this->environment->wake();
    }

    /**
     * Executes the command
     * @param  string $command  the full command to execute
     * @return void
     */
    protected function executeCommand($command = '')
    {
        if (empty($command))

        $output = $this->output();
        $useTty = $this->useTty();
        $echoOutput = function ($type, $buffer){};

        if ($useTty) {
            $echoOutput = function ($type, $buffer) use ($output) {
                $output->write($buffer);
            };
        }

        $command = join(" ", [$this->command, $command]);
        // $result = $this->environment->sendCommandViaSsh($command, $echoOutput, $useTty);
        $result = $this->execCommand($command, $echoOutput, $useTty);
        $output = $result['output'];
        $exit = $result['exit_code'];

        $this->log()->notice('Command: {site}.{env} -- {command} [Exit: {exit}]', [
            'site' => $this->site->get('name'),
            'env' => $this->environment->id,
            'command' => $command,
            'exit' => $exit,
        ]);

        if ($exit !=0) {
            throw new TerminusProcessException($output);
        }
    }

    /**
     * whether or not to use Tty.
     * @return boolean
     */
    protected function useTty()
    {
        if (!$this->input()->isInteractive()) {
            return false;
        }

        return (function_exists('posix_isatty') && !posix_isatty(STDOUT)) ? false : null;
    }

    /**
     * Executes the command
     * @param  string $command   the full mysql command
     * @param  function $callback
     * @param  boolean $useTty
     * @return string  (I think. either string or magic).
     */
    protected function execCommand($command, $callback, $useTty)
    {
        return $this->getContainer()->get(LocalMachineHelper::class)->execInteractive($command, $callback, $useTty);
    }
}
