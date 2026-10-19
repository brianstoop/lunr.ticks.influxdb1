<?php

/**
 * This file contains the EventLogger class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Ticks\InfluxDB1\EventLogging;

use InfluxDB\Client;
use InfluxDB\Exception as InfluxDBException;
use InfluxDB\Point;
use Lunr\Ticks\EventLogging\EventLoggerInterface;
use Lunr\Ticks\Precision;
use Psr\Log\LoggerInterface;

/**
 * Class for logging events.
 */
class EventLogger implements EventLoggerInterface
{

    /**
     * Instance of the InfluxDB Client class
     * @var Client
     */
    protected readonly Client $client;

    /**
     * Instance of a PSR-3 logger.
     * @var LoggerInterface
     */
    protected readonly LoggerInterface $logger;

    /**
     * Name of the database
     * @var string
     */
    protected readonly string $database;

    /**
     * Retention policy to apply
     * @var string
     */
    protected readonly string $retentionPolicy;

    /**
     * Default tags to use for all events.
     * @var array<string,string>
     */
    protected readonly array $defaultTags;

    /**
     * Default fields to use for all events.
     * @var array<string,scalar>
     */
    protected readonly array $defaultFields;

    /**
     * Constructor.
     *
     * @param Client               $client        Instance of the InfluxDB Client class
     * @param LoggerInterface      $logger        Instance of a PSR-3 Logger
     * @param array<string,string> $defaultTags   Default tags to use for all events
     * @param array<string,scalar> $defaultFields Default fields to use for all events
     */
    public function __construct(Client $client, LoggerInterface $logger, array $defaultTags = [], array $defaultFields = [])
    {
        $this->client        = $client;
        $this->logger        = $logger;
        $this->defaultTags   = $defaultTags;
        $this->defaultFields = $defaultFields;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // no-op
    }

    /**
     * Get an instance of a new event
     *
     * @param string $name Event name
     *
     * @return Event Instance of a new Event
     */
    public function newEvent(string $name): Event
    {
        return new Event(
            $this,
            new Point(
                measurement: $name,
                tags: $this->defaultTags,
                additionalFields: $this->defaultFields,
            ),
        );
    }

    /**
     * Set a database for the new event.
     *
     * @param string $database Database name
     *
     * @return void
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    /**
     * Set a retention policy for the analytics data.
     *
     * @param string $retentionPolicy Name of the policy for how long to retain the analytics data
     *
     * @return void
     */
    public function setRetentionPolicy(string $retentionPolicy): void
    {
        $this->retentionPolicy = $retentionPolicy;
    }

    /**
     * Log a single event.
     *
     * @param Point     $event     Event to log
     * @param Precision $precision Timestamp precision to use for the event
     *
     * @return void
     */
    public function record(Point $event, Precision $precision): void
    {
        $clientPrecision = $this->client->getPrecision();

        $influxdbPrecision = match ($precision)
        {
            Precision::Hours => $clientPrecision::PRECISION_HOURS,
            Precision::Minutes => $clientPrecision::PRECISION_MINUTES,
            Precision::Seconds => $clientPrecision::PRECISION_SECONDS,
            Precision::MilliSeconds => $clientPrecision::PRECISION_MILLISECONDS,
            Precision::MicroSeconds => $clientPrecision::PRECISION_MICROSECONDS,
            Precision::NanoSeconds => $clientPrecision::PRECISION_NANOSECONDS,
        };

        try
        {
            $this->client->selectDB($this->database)->writePoints([ $event ], $influxdbPrecision, $this->retentionPolicy ?? NULL);
        }
        catch (InfluxDBException $e)
        {
            $this->logger->warning('Logging to InfluxDB failed: {error}', [ 'error' => $e->getMessage() ]);
        }
    }

}

?>
