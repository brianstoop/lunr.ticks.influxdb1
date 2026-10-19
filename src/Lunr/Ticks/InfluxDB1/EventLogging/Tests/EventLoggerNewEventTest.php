<?php

/**
 * This file contains the EventLoggerNewEventTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Ticks\InfluxDB1\EventLogging\Tests;

use InfluxDB\Point;
use Lunr\Ticks\InfluxDB1\EventLogging\Event;
use Lunr\Ticks\InfluxDB1\EventLogging\EventLogger;
use ReflectionClass;

/**
 * This class contains tests for the EventLogger class.
 *
 * @covers Lunr\Ticks\InfluxDB1\EventLogging\EventLogger
 */
class EventLoggerNewEventTest extends EventLoggerTestCase
{

    /**
     * Test that newEvent() returns an Event instance.
     *
     * @covers Lunr\Ticks\InfluxDB1\EventLogging\EventLogger::newEvent
     */
    public function testNewEvent(): void
    {
        $event = $this->class->newEvent('event');

        $this->assertInstanceOf(Event::class, $event);

        $eventReflection = new ReflectionClass(Event::class);

        $eventLogger = $eventReflection->getProperty('eventLogger')
                                       ->getValue($event);

        $this->assertSame($this->class, $eventLogger);

        $point = $eventReflection->getProperty('point')
                                 ->getValue($event);

        $this->assertInstanceOf(Point::class, $point);

        $pointReflection = new ReflectionClass(Point::class);

        $measurement = $pointReflection->getProperty('measurement')
                                       ->getValue($point);

        $this->assertSame('event', $measurement);

        $defaultTags = $pointReflection->getProperty('tags')
                                       ->getValue($point);

        $this->assertSame($this->defaultTags, $defaultTags);

        $defaultFields = $pointReflection->getProperty('fields')
                                         ->getValue($point);

        // influxdb-php wraps string field values in double quotes
        $this->assertSame([ 'baz' => '"qux"' ], $defaultFields);
    }

    /**
     * Test that fields added to an event are merged with, and override, the default fields.
     *
     * @covers Lunr\Ticks\InfluxDB1\EventLogging\EventLogger::newEvent
     */
    public function testNewEventFieldsMergeWithDefaultFields(): void
    {
        $event = $this->class->newEvent('event');

        $event->addFields([ 'baz' => 'override', 'extra' => 'new' ]);

        $point = (new ReflectionClass(Event::class))->getProperty('point')
                                                    ->getValue($event);

        $fields = (new ReflectionClass(Point::class))->getProperty('fields')
                                                     ->getValue($point);

        $this->assertSame([ 'baz' => '"override"', 'extra' => '"new"' ], $fields);
    }

    /**
     * Test that newEvent() creates an event without fields if no default fields are set.
     *
     * @covers Lunr\Ticks\InfluxDB1\EventLogging\EventLogger::newEvent
     */
    public function testNewEventWithoutDefaultFields(): void
    {
        $class = new EventLogger($this->client, $this->logger, $this->defaultTags);

        $event = $class->newEvent('event');

        $point = (new ReflectionClass(Event::class))->getProperty('point')
                                                    ->getValue($event);

        $fields = (new ReflectionClass(Point::class))->getProperty('fields')
                                                     ->getValue($point);

        $this->assertSame([], $fields);
    }

}

?>
