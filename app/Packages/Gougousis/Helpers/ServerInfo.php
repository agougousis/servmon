<?php

namespace App\Packages\Gougousis\Helpers;

/**
 * A container for server health information
 *
 * @author Alexandros Gougousis
 */
class ServerInfo
{
    /**
     * Blocks usage for each server's disk
     *
     * Each array item is an assoiative array with the following keys:
     *  - disk_name
     *  - usage
     *  - mount_point
     *
     * @var array
     */
    public $df_blocks;

    /**
     * Inodes usage for each server's disk
     *
     * Each array item is an assoiative array with the following keys:
     *  - disk_name
     *  - usage
     *  - mount_point
     *
     * @var array
     */
    public $df_inodes;

    /**
     * The number of processors present on the server
     *
     * @var int
     */
    public $count_processors;

    /**
     * How long since the last server's reboot
     *
     * @var string
     */
    public $uptime;

    /**
     * Server's load in during the last 5 minutes (in 0-100%)
     *
     * @var float
     */
    public $last5min_load;

    /**
     * Server's load in during the last 10 minutes (in 0-100%)
     *
     * @var float
     */
    public $last10min_load;

    /**
     * The server's physical memory in KBs
     *
     * @var int
     */
    public $total_memory;

    /**
     * The server's physical memory in human readable format
     *
     * @var string
     */
    public $total_memory_text;

    /**
     * The server's free memory in KBs
     *
     * @var int
     */
    public $free_memory;

    /**
     * The server's free memory in human readable format
     *
     * @var string
     */
    public $free_memory_text;

    /**
     * A list of network services on the server
     *
     * Each array item is an assoiative array with the following keys:
     *  - command
     *  - user
     *  - pType
     *  - protocol
     *  - port
     *  - address
     *
     * @var array
     */
    public $services;

}
