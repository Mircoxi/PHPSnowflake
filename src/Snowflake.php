<?php

namespace Mircoxi\Snowflake;

class Snowflake
{
    private int $machineID;
    private int $startTime;
    private int $sequence;
    private int $elapsedTime;

    public function __construct()
    {
        $this->machineID = $this->getMachineID();
        // TODO: Configurable start time.
        $startDate = new \DateTime('2000-01-01 00:00:00.00000');
        $this->startTime = $startDate->getTimestamp() * 1000;
        /* This is an awkward workaround for not being able to have a sequence identifier.
         * It generates a random integer between 0 and 32768, which gives plenty of room for further
         * generation.
         *
         * TODO: See if this can be fixed to use an *actual* sequence without leaving a file or using Redis.
         */
        $this->sequence = rand(0, 32768);
        $this->elapsedTime = $this->getElapsedTime();

    }

    /**
     * Uses the lower 16 bits of the host or container's IP address to generate a machine ID. If this is a containerised
     * app, allows for up to 65,535 nodes.
     *
     * @return int The machine ID.
     */
    public function getMachineID(): int {
        $ip =  gethostbyname(gethostname());
        $octets = array_map('intval', explode('.', $ip));
        return ($octets[2] << 8) + $octets[3];
    }

    /**
     * Gets the current time as a Unix timestamp in milliseconds.
     *
     * @return int The current time as a Unix timestamp.
     */
    public function getCurrentTime(): float {
        return intval(microtime(true) * 1000);
    }

    public function getElapsedTime(): int {
        return $this->getCurrentTime() - $this->startTime;
    }

    public function next(): int {
        return $this->getID();
    }

    public function getID(): int {
        $this->sequence += 1;
        return (($this->getElapsedTime() << 16) | ($this->sequence < 8) | $this->machineID);
    }

}
