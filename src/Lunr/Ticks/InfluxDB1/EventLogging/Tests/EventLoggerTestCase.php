<?php

/**
 * This file contains the EventLoggerTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Ticks\InfluxDB1\EventLogging\Tests;

use InfluxDB\Client;
use Lunr\Halo\LunrBaseTestCase;
use Lunr\Ticks\InfluxDB1\EventLogging\EventLogger;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the EventLogger class.
 *
 * @covers Lunr\Ticks\InfluxDB1\EventLogging\EventLogger
 */
abstract class EventLoggerTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the InfluxDB Client.
     * @var Client
     */
    protected Client $client;

    /**
     * Mock instance of a Logger.
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Instance of the tested class.
     * @var EventLogger
     */
    protected EventLogger $class;

    /**
     * Default tags
     * @var array<string, string>
     */
    protected array $defaultTags = [ 'foo' => 'bar' ];

    /**
     * Default fields
     * @var array<string, scalar>
     */
    protected array $defaultFields = [ 'baz' => 'qux' ];

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
                             ->getMock();

        $this->class = new EventLogger($this->client, $this->logger, $this->defaultTags, $this->defaultFields);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->client);
        unset($this->logger);
        unset($this->class);
    }

}

?>
