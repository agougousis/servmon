<?php

namespace App\Packages\Gougousis\SSH;

/**
 * Helper functions for SSH functionality
 *
 * @author Alexandros Gougousis
 */
class SshHelper
{
    /**
     * Translate an error message obtained during an SSH connection
     *
     * @param string $message
     * @return string
     */
    public static function identifyLoginError($message)
    {
        if (strpos($message, 'getaddrinfo failed') === false) {
            if (strpos($message, 'Connection refused') === false) {
                if (strpos($message, 'No route to host') === false) {
                    if (strpos($message, 'Permission denied') === false) {
                        return $message;
                    }
                    return "Please check if password authentication is enabled on the server.";
                }
                return "Please check if the port is correct and the server is reachable through this subnet.";
            }
            return "Please check the provided username, password and port.";
        }
        return "Please check the validity of the hostname.";
    }

    /**
     * Extracts information from the output of uptime command
     *
     * @param string $uptime_output
     * @return array
     */
    public static function extractInfoFromUptime($uptime_output, $count_processors)
    {
        $info = [];

        // Get uptime
        $line_parts = explode(',', $uptime_output);
        $uptime_parts = explode('up', $line_parts[0]);
        $info['uptime'] = trim($uptime_parts[1]);

        // Get number of logged in users
        $info['users_logged_in'] = trim($line_parts[2]);

        // Get CPU load
        $line_parts = explode('load average:', $uptime_output);
        $cpu_load_parts = explode(',', $line_parts[1]);
        $info['last5min_load'] = number_format(($cpu_load_parts[0]/$count_processors), 2);
        $info['last10min_load'] = number_format(($cpu_load_parts[1]/$count_processors), 2);

        return $info;
    }

    /**
     * Extracts block usage information from the output of df command
     *
     * The output expected should be from 'df -h' command
     *
     * @param string $df_blocks_output
     * @return array
     */
    public static function extractBlockUsageFromDf($df_blocks_output)
    {
        $df_bloks_lines = preg_split('/[\n]/', $df_blocks_output);
        $df_blocks = array();
        // Go through all output lines
        foreach ($df_bloks_lines as $line) {
            // Look for lines that correspond to a device
            if (startsWithString($line, '/dev/')) {
                // Extract info for that device
                $line_parts = preg_split('/\s+/', $line);
                $df_blocks[] = array(
                    'disk_name' =>  $line_parts[0],
                    'usage'     =>  trim($line_parts[4], '%'),
                    'mount_point'   =>  $line_parts[5]
                );
            }
        }
        return $df_blocks;
    }

    /**
     * Extracts Inodes usage information from the output of df command
     *
     * The output expected should be from 'df -h -i' command
     *
     * @param string $df_inodes_output
     * @return array
     */
    public static function extractInodesUsageFromDf($df_inodes_output)
    {
        $df_inodes_lines = preg_split('/[\n]/', $df_inodes_output);
        $df_inodes = array();
        // Go through all output lines
        foreach ($df_inodes_lines as $line) {
            // Look for lines that correspond to a device
            if (startsWithString($line, '/dev/')) {
                // Extract info for that device
                $line_parts = preg_split('/\s+/', $line);
                $df_inodes[] = array(
                    'disk_name' =>  $line_parts[0],
                    'usage'     =>  trim($line_parts[4], '%'),
                    'mount_point'   =>  $line_parts[5]
                );
            }
        }
        return $df_inodes;
    }

    /**
     * Extracts memory information from the output of free command
     *
     * The output expected should be from 'free' command (no options)
     *
     * @param string $free_output
     * @return array
     */
    public static function extractInfoFromFree($free_output)
    {
        $mem_info = [];
        $memory_lines = preg_split('/[\n]/', $free_output);

        // Get total RAM from second line
        $line_numbers = explode(':', $memory_lines[1]);
        $numbers = preg_split('/\s+/', trim($line_numbers[1]));
        $mem_info['total'] = trim($numbers[0]);

        // Get free memory from third line
        $line_numbers = explode(':', $memory_lines[2]);
        $numbers = preg_split('/\s+/', trim($line_numbers[1]));
        $mem_info['free'] = trim($numbers[1]);

        return $mem_info;
    }

    /**
     * Extract a list of active network services from the output of lsof command
     *
     * The output expected should be from 'lsof -i -n -P' command
     *
     * @param string $lsof_output
     * @return array
     */
    public static function extractServicesFromLsof($lsof_output)
    {
        $services = [];
        $lsof_lines = preg_split('/[\n]/', $lsof_output);
        unset($lsof_lines[count($lsof_lines)-1]); // remove prompt line
        unset($lsof_lines[1]); // remove header line
        unset($lsof_lines[0]); // remove empty line
        foreach ($lsof_lines as $line) {
            if (preg_match("/\s\(LISTEN\)/", $line)) {
                $columns = preg_split('/\s+/', trim($line));
                $command = $columns[0];
                $user = $columns[2];
                $ipType = $columns[4];
                $protocol = $columns[7];
                $bind = $columns[8];
                if (preg_match("/([0-9\.]+|\*):([0-9]+)/", $bind, $matches)) {
                    $address = $matches[1];
                    $port = $matches[2];
                    $services[] = compact('command', 'user', 'ipType', 'protocol', 'port', 'address');
                }
            }
        }
        $toJson = function ($item) {
            return json_encode($item);
        };
        $fromJson = function ($item) {
            return json_decode($item);
        };

        // Remove duplicates in case a service has many processes
        // Service items are compared as JSON strings.
        $temp = array_unique(array_map($toJson, $services));
        // The first column is 'command', so we are first sorting by service name
        sort($temp);
        // Back from JSON to array
        $services = array_map($fromJson, $temp);

        return $services;
    }

    /**
     * Adds units to an integer, expressing an amount of memory
     *
     * @param int $memoryInKilobytes
     * @return string
     */
    public static function addMemoryUnits($memoryInKilobytes)
    {
        if ($memoryInKilobytes < 1000) {
            return number_format($memoryInKilobytes, 2)." KB";
        } elseif (($memoryInKilobytes/1000) < 1000) {
            return number_format(($memoryInKilobytes/1000), 2)." MB";
        } else {
            return number_format(($memoryInKilobytes/1000000), 2)." GB";
        }
    }
}
