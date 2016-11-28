<?php

namespace App\Packages\Gougousis\SSH;

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;
use App\Packages\Gougousis\SSH\SshHelper;
use App\Packages\Gougousis\Helpers\ServerInfo;

/**
 * Handles the SSH communication with remote servers
 *
 * @author Alexandros Gougousis
 */
class SshManager
{
    const SSH_INIT = 0;             // We havev not connected
    const SSH_AUTHENTICATED = 1;    // We have conected and authenticated to the remote server

    /**
     * The fully qualified name of the remote server
     *
     * @var string
     */
    private $host;

    /**
     * The SSH port to the remote server
     *
     * @var int
     */
    private $port;

    /**
     * The username to be used in authentication to remote server
     *
     * @var string
     */
    private $username;

    /**
     * The password to be used in authentication to remote server
     *
     * @var string
     */
    private $password;

    /**
     * The path to the RSA file that will be used in authentication to
     * remote server
     *
     * @var string
     */
    private $sshKeyPath;

    /**
     *
     * @var string
     */
    private $authMode;

    /**
     * The commection to the remote server
     *
     * @var phpseclib\Net\SSH2
     */
    private $conn;

    /**
     * The status of our connection to the rempte server
     *
     * @var int
     */
    private $status;

    public function __construct($host, $port, $username, $password = null, $sshKeyPath = null) {
        if (empty($host)||empty($port)||empty($username)) {
            throw new \Exception('Invalid connection parameters!');
        }

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;

        if (empty($sshKeyPath)) {
            if (empty($password)) {
                throw new \Exception('Authentication credentials are missing!');
            }

            $this->password = $password;
            $this->authMode = 'password';
        } else {
            $this->sshKeyPath = $sshKeyPath;
            $this->authMode = 'rsa';
        }

        $this->conn = new SSH2($host, $port);
        $this->status = self::SSH_INIT;
    }

    /**
     * Authenticates to the remote server
     *
     * @return boolean
     * @throws \Exception
     */
    public function connectAndAuthenticate()
    {
        if ($this->status == self::SSH_AUTHENTICATED) {
            throw new Exception('Already authenticated!');
        }

        switch ($this->authMode) {   // Authenticate using password
            case 'password':
                if (!$this->conn->login($this->username, $this->password)) {
                    return false;
                }
                break;
            case 'rsa':              // Authenticate using RSA key
                 if (!file_exists($this->sshKeyPath)) {
                     throw new \Exception("The provided SSH key path does not exist or is not accessible!");
                 }

                 $key = new RSA();
                 $key->loadKey(file_get_contents($this->sshKeyPath));

                 if (!$this->conn->login($this->username, $key)) {
                     return false;
                 }
                break;
            default:
                return false;
        }

        $this->status = self::SSH_AUTHENTICATED;
        return true;
    }

    /**
     * Retrieves information about server's health
     *
     * @return ServerInfo
     * @throws \Exception
     */
    public function getServerInfo()
    {
        if ($this->status != self::SSH_AUTHENTICATED) {
            throw new \Exception('SSH authentication has not taken place!');
        }

        $serverInfo = new ServerInfo();

        // Get disk-related info
        $serverInfo->df_blocks = $this->getBlocksUsage();
        $serverInfo->df_inodes = $this->getInodesUsage();

        // Get CPU-related info
        $serverInfo->count_processors = $this->countProcessors();
        $uptime_info = $this->getUptimeAndCpuLoad($serverInfo->count_processors);
        $serverInfo->uptime = $uptime_info['uptime'];
        $serverInfo->last5min_load = $uptime_info['last5min_load'];
        $serverInfo->last10min_load = $uptime_info['last10min_load'];

        // Get memory-related info
        $mem_info = $this->getMemoryInfo();
        $serverInfo->total_memory = $mem_info['total'];
        $serverInfo->total_memory_text = SshHelper::addMemoryUnits($mem_info['total']);
        $serverInfo->free_memory = $mem_info['free'];
        $serverInfo->free_memory_text = SshHelper::addMemoryUnits($mem_info['free']);

        // Get network services-related info
        $serverInfo->services = $this->getServicesList();

        return $serverInfo;
    }

    /**
     * Retrieves information about disk blocks usage
     *
     * @return array
     */
    private function getBlocksUsage()
    {
        $df_blocks_output = $this->conn->exec('df -h');
        return SshHelper::extractBlockUsageFromDf($df_blocks_output);
    }

    /**
     * Retrieves information about disk inodes usage
     *
     * @return array
     */
    private function getInodesUsage()
    {
        $df_inodes_output = $this->conn->exec('df -h -i');
        return SshHelper::extractInodesUsageFromDf($df_inodes_output);
    }

    /**
     * Retrieves information about the number of processors
     *
     * @return array
     */
    private function countProcessors()
    {
        return trim($this->conn->exec("grep 'model name' /proc/cpuinfo | wc -l"));
    }

    /**
     * Retrieves information about uptime and CPU load
     *
     * @param int $count_processors
     * @return array
     */
    private function getUptimeAndCpuLoad($count_processors)
    {
        $uptime_output = $this->conn->exec('uptime');
        return SshHelper::extractInfoFromUptime($uptime_output, $count_processors);
    }

    /**
     * Retrieves information about memory
     *
     * @return array
     */
    private function getMemoryInfo()
    {
        $free_output = $this->conn->exec('free');
        return SshHelper::extractInfoFromFree($free_output);
    }

    /**
     * Retrieves information about network services
     *
     * @return array
     */
    private function getServicesList()
    {
        if ($this->username != 'root') {
            $this->conn->write("sudo lsof -i -n -P\n");
            $template = "/password\sfor\s".$this->username.":|".$this->username."@".$this->host.":~\$/";
            // Read until you see the password prompt or the normal prompt
            $sudo_response = $this->conn->read($template, 2);
            if (preg_match("/password\sfor/", $sudo_response)) {
                // In case of password prompt, give the password
                $this->conn->write($this->password."\n");
                // Take into account the 3 sec default delay of linux
                // (it is there to prevent brute-force attacks)
                $this->conn->setTimeout(3);
            }

            // Read until you see another prompt. We expect to see a normal prompt
            // but, if the password is wrong, it can be a password prompt.
            $lsof_output = $this->conn->read($template, 2);
        } else {
            $lsof_output = $this->conn->exec('lsof -i -n -P');
        }
        return SshHelper::extractServicesFromLsof($lsof_output);
    }
}

