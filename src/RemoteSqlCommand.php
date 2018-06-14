<?php

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusProcessException;
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
     * [env:mysq] Gets the mysql command string from the environment and runs it.
     *
     * @authorise
     *
     * @command env:mysql
     *
     * @param  string $site_env The Site Environment in the `site_name.env` format
     * @param string $sql_command  The sql statement to run on the remote
     * @usage terminus mysql <site>.<env> -- <command> the sql query to run on the db </command>
     */
    public function mysqlCommand($site_env, $sql_command = "")
    {
        $this->prepareEnvironment($site_env);

        $site_label = $this->getSiteLabel();
        $this->log()->notice('Connecting to the mysql database for ' . $site_label);
        $command = $this->getCommand('mysql_command');
        if (!empty($sql_command)) {
            $command .= ' -e "' . $sql_command . '"';
        }

        return $this->runCommand($command);
    }

    /**
     * gets the site label that we want to display in logs
     * @return string
     */
    protected function getSiteLabel()
    {
        return $this->site->get('name') . '\'s ' . $this->environment->id . ' environment';
    }

    /**
     * [env:sftp] Gets the sftp command string from the environment and runs it.
     *
     * @authorise
     *
     * @command env:sftp
     *
     * @param  string $site_env The Site Environment in the `site_name.env` format
     * @usage terminus env:sftp <site>.<env>
     */
    public function sftpCommand($site_env)
    {
        $this->prepareEnvironment($site_env);
        $site_label = $this->getSiteLabel();
        $this->log()->notice('Establishing sftp connection to ' . $site_label);
        $command = $this->getCommand('sftp_command');
        return $this->runCommand($command);
    }

    /**
     * Gets from the site environment the full command string
     * @param  string $command_name  the key name of the command in the connection info
     * @return string
     */
    protected function getCommand($command_name = '')
    {
        // get the full mysql command from the connection info
        $info = $this->environment->connectionInfo();

        return $info[$command_name];
    }

    /**
     * executes the command and if no command was passed we couldn't get the info
     * so it throws a terminus exception
     * @param  string $command  the command to run
     * @return void really
     */
    protected function runCommand($command = '')
    {
        if (!empty($command)) {
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
