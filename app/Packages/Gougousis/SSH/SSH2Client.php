<?php

namespace App\Packages\Gougousis\SSH;

/**
 * A wrapper for SSH functionality
 *
 * A minimal wrapper to support just the SSH functionality we need for this
 * application. It uses the native SSH support that comes with PHP and which
 * is based on libssh2.
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class SSH2Client
{

    public $connection;
    public $authenticated = false;

    private $output;
    private $hostname;
    private $port;

    /**
     * Resets some properties of the class
     */
    public function disconnect()
    {
        $this->connection = null;
        $this->authenticated = false;
        $this->output = null;
    }

    /**
     * Represents the authentication status of an SSH connection
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Sets the required properties for opening an SSH connection
     *
     * @param string $hostname
     * @param int $port
     * @throws \Exception
     */
    public function setTargetServer($hostname, $port = 22)
    {
        if (empty($hostname)) {
            throw new \Exception('Hostname is empty!');
        }
        $this->hostname = $hostname;
        $this->port = $port;
    }

    /**
     * Connects to SSH server and authenticates using password
     *
     * @param string $username
     * @param string $password
     * @return boolean
     * @throws \Exception
     */
    public function connectWithPassword($username, $password)
    {
        // SSH server info should not be empty
        if (empty($this->hostname)||empty($this->port)) {
            throw new \Exception('Target server has not been set!');
        }

        // If we are not connected, connect
        if (empty($this->connection)) {
            $this->connection = ssh2_connect($this->hostname, $this->port, array('hostkey'=>'ssh-rsa'));
        }

        // Check that we are not already authenticated
        if ($this->authenticated) {
            throw new \Exception('Already authenticated!');
        }

        // Authenticate
        if (ssh2_auth_password($this->connection, $username, $password)) {
            $this->authenticated = true;
            return true;
        } else {
            $this->authenticated = false;
            return false;
        }
    }

    /**
     * Connects to SSH server and authenticates using an RSA key
     *
     * @param string $username
     * @param string $pubKeyPath
     * @param string $privKeyPath
     * @return boolean
     * @throws \Exception
     */
    public function connectWithRsaKey($username, $pubKeyPath, $privKeyPath)
    {
        // SSH server info should not be empty
        if (empty($this->hostname)||empty($this->port)) {
            throw new \Exception('Target server has not been set!');
        }

        // If we are not connected, connect
        if (empty($this->connection)) {
            $this->connection = ssh2_connect($this->hostname, $this->port, array('hostkey'=>'ssh-rsa'));
        }

        // Check that we are not already authenticated
        if ($this->authenticated) {
            throw new \Exception('Already authenticated!');
        }

        // Authenticate
        if (ssh2_auth_pubkey_file($this->connection, $username, $pubKeyPath, $privKeyPath, '')) {
            $this->authenticated = true;
            return true;
        } else {
            $this->authenticated = false;
            return false;
        }
    }

    /**
     * Executes a command that produces non-empty output
     *
     * @param string $command
     * @param string $password
     * @return boolean
     * @throws \Exception
     */
    public function execWithOutput($command, $password = null)
    {
        // SSH server info should not be empty
        if (empty($this->hostname)||empty($this->port)) {
            throw new \Exception('Target server has not been set!');
        }

        // SSH cconnection should be established in advance
        if (empty($this->connection)) {
            throw new \Exception('You are not connected!');
        }

        // Successful authentication should have taken place in advance
        if (!$this->authenticated) {
            throw new \Exception('You have not authenticated!');
        }

        // The command should not be empty
        if (empty($command)) {
            throw new \Exception('The provided command is empty!');
        }

        // If password is provided execute the command using sudo
        if (!empty($password)) {
            $command = "echo $password | sudo -S $command\n";
        }

        // Execute the command
        $stream= ssh2_exec($this->connection, $command);
        stream_set_blocking($stream, true);
        $this->output = stream_get_contents($stream);
        // If output is empty, an error must have happened
        if (empty($this->output)) {
            // Retrieve the STDERR output
            $stream= ssh2_exec($this->connection, $command);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($errorStream, true);
            $this->output = stream_get_contents($errorStream);
            return false;
        }

        return true;
    }

    /**
     * Returns the output produced by the last command execution
     *
     * @return string
     */
    public function getOutout()
    {
        return $this->output;
    }
}
