<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Colibri\Test;

use Opis\Colibri\Events\{Event, EventDispatcher};
use PHPUnit\Framework\TestCase;

class EventsTest extends TestCase
{
    protected EventDispatcher $target;

    public function setUp(): void
    {
        $this->target = new EventDispatcher();
    }

    public function testBasicEvent()
    {
        $this->target->handle('ok', static function (Event $event) {
            print $event->name();
        });

        $this->expectOutputString('ok');
        $this->target->emit('ok');
    }

    public function testParams()
    {
        $this->target->handle('foo.{bar}', static function (Event $event) {
            print $event->name();
        })->where('bar', 'x');

        $this->expectOutputString('foo.x');
        $this->target->emit('foo.y');
        $this->target->emit('foo.x');
    }

    public function testParams2()
    {
        $this->target->handle('foo.{bar}', static function (Event $event) {
            print $event->name();
        })->where('bar', 'x|y');

        $this->expectOutputString('foo.yfoo.x');
        $this->target->emit('foo.y');
        $this->target->emit('foo.x');
    }

    public function testParams3()
    {
        $this->target->handle('foo.{bar=x|y}', static function (Event $event) {
            print $event->name();
        });

        $this->expectOutputString('foo.yfoo.x');
        $this->target->emit('foo.y');
        $this->target->emit('foo.x');
    }

    public function testDefaultPriority()
    {
        $this->target->handle('foo', static function () {
            print "foo";
        });

        $this->target->handle('foo', static function () {
            print "bar";
        });

        $this->expectOutputString("barfoo");
        $this->target->emit('foo');
    }

    public function testExplicitPriority()
    {
        $this->target->handle('foo', static function () {
            print "foo";
        }, 1);

        $this->target->handle('foo', static function () {
            print "bar";
        });

        $this->expectOutputString("foobar");
        $this->target->emit('foo');
    }

    public function testExplicitPriorityEqual()
    {
        $this->target->handle('foo', static function () {
            print "foo";
        }, 1);

        $this->target->handle('foo', static function () {
            print "bar";
        }, 1);

        $this->expectOutputString("barfoo");
        $this->target->emit('foo');
    }

    public function testDefaultPriorityNotCancel()
    {
        $this->target->handle('foo', static function () {
            print "foo";
        });

        $this->target->handle('foo', static function (Event $event) {
            $event->cancel();
            print "bar";
        });

        $this->expectOutputString("barfoo");
        $this->target->emit('foo', false);
    }

    public function testDefaultPriorityCancel()
    {
        $this->target->handle('foo', static function () {
            print "foo";
        });

        $this->target->handle('foo', static function (Event $event) {
            $event->cancel();
            print "bar";
        });

        $this->expectOutputString("bar");
        $this->target->emit('foo', true);
    }

    public function testDefaultPriorityCancel2()
    {
        $this->target->handle('foo', static function () {
            print "foo";
        });

        $this->target->handle('foo', static function (Event $event) {
            $event->cancel();
            print "bar";
        });

        $this->target->handle('f{=o{2}}', static function () {
            print "baz";
        });

        $this->expectOutputString("bazbar");
        $this->target->emit('foo', true);
    }

    public function testDispatch()
    {
        $this->target->handle('foo', static function ($event) {
            /** @noinspection PhpUndefinedMethodInspection */
            print $event->data();
        });

        $event = new class("foo", false, "test-data") extends Event
        {
            protected $data;

            public function __construct(string $name, bool $cancelable = false, string $data = '')
            {
                $this->data = $data;
                parent::__construct($name, $cancelable);
            }

            public function data(): string
            {
                return $this->data;
            }
        };

        $this->expectOutputString("test-data");
        $this->target->dispatch($event);
    }

    public function testDispatch2()
    {
        $this->target->handle('foo', static function () {
            print 'ok';
        });

        $event = new Event("foo", true);
        $event->cancel();

        $this->expectOutputString("");
        $this->target->dispatch($event);
    }

    public function testSerializable()
    {
        \Opis\Closure\Library::init();

        $this->target->handle('foo', static function () {
            print "foo";
        });

        $this->target->handle('foo', static function (Event $event) {
            $event->cancel();
            print "bar";
        });

        $this->target->handle('foo', static function () {
            print 'baz';
        });

        $target = unserialize(serialize($this->target));

        $this->expectOutputString("bazbar");
        $target->emit('foo', true);
    }
}
