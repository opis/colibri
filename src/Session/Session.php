<?php
/* ===========================================================================
 * Copyright 2019-2021 Zindex Software
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

namespace Opis\Colibri\Session;

class Session
{
    private array $config;
    private SessionHandler $handler;
    private CookieContainer $container;
    private array $data;
    private ?Flash $flash = null;
    private ?SessionData $session;

    public function __construct(CookieContainer $container, ?SessionHandler $handler = null, array $config = [])
    {
        if ($handler === null) {
            $file = $config['save_path'] ?? ini_get('session.save_path');
            $handler = new Handlers\File($file ?: sys_get_temp_dir());
            unset($file);
        }

        $this->container = $container;
        /** @var SessionHandler $handler */
        $this->handler = $handler;

        $config += [
            'flash_slot' => '__flash__',
            'gc_probability' => (int) (ini_get('session.gc_probability') ?: 1),
            'gc_divisor' => (int) (ini_get('session.gc_divisor') ?: 100),
            'gc_maxlifetime' => (int) (ini_get('session.gc_maxlifetime') ?: 1440),

            'cookie_name' => ini_get('session.name') ?: 'PHPSESSID',
            'cookie_lifetime' => (int) (ini_get('session.cookie_lifetime') ?: 0),
            'cookie_path' => ini_get('session.cookie_path') ?: '/',
            'cookie_domain' => ini_get('session.cookie_domain') ?: '',
            'cookie_secure' => (bool) ini_get('session.cookie_secure'),
            'cookie_httponly' => (bool) (ini_get('session.cookie_httponly') ?? true),
            'cookie_samesite' => ini_get('session.cookie_samesite') ?: null,
        ];

        $this->config = $config;

        // Read session data
        $session = null;
        $handler->open($config['cookie_name']);

        // Try GC before reading session data
        $this->gc();

        if ($container->hasCookie($config['cookie_name'])) {
            $session = $handler->read($container->getCookie($config['cookie_name']));
        }

        if ($session === null) {
            $session_id = $handler->generateSessionId();
            $expire = $config['cookie_lifetime'] ? time() + $config['cookie_lifetime'] : 0;
            $container->setCookie(
                $config['cookie_name'],
                $session_id,
                $expire,
                $config['cookie_path'],
                $config['cookie_domain'],
                $config['cookie_secure'],
                $config['cookie_httponly'],
                $config['cookie_samesite']
            );
            $session = $handler->create($session_id, $expire);
        }

        $this->session = $session;
        $this->data = $session->data();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->session !== null) {
            $this->data[$this->config['flash_slot']] = $this->flash()->toArray();
            $this->session->setData($this->data);
            $this->handler->update($this->session);
        }

        $this->handler->close();
    }

    /**
     * @param bool $immediate
     * @return bool
     */
    public function gc(bool $immediate = false): bool
    {
        if (!$immediate) {
            $probability = $this->config['gc_probability'];
            if ($probability <= 0) {
                return false;
            }
            $probability /= $this->config['gc_divisor'];
            if (lcg_value() > $probability) {
                return false;
            }
        }

        return $this->handler->gc($this->config['gc_maxlifetime']);
    }

    /**
     * Returns the session id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->session->id();
    }

    /**
     * Returns a timestamp representing the session's creation date.
     *
     * @return int
     */
    public function createdAt(): int
    {
        return $this->session->createdAt();
    }

    /**
     * Returns a timestamp representing the last time this session was accessed.
     *
     * @return int
     */
    public function updatedAt(): int
    {
        return $this->session->updatedAt();
    }

    /**
     * Returns a timestamp representing the expiration date of the current session.
     *
     * @return int
     */
    public function expiresAt(): int
    {
        return $this->session->expiresAt();
    }

    /**
     * Extends the lifetime of the session.
     *
     * @param int $seconds
     * @return bool
     */
    public function extendLifetime(int $seconds): bool
    {
        if ($this->session === null || $seconds < 0) {
            return false;
        }

        $expire = $this->session->expiresAt();

        if ($expire === 0) {
            return true;
        }

        $expire += $seconds;

        $config = $this->config;

        $this->container->setCookie(
            $config['cookie_name'],
            $this->session->id(),
            $expire,
            $config['cookie_path'],
            $config['cookie_domain'],
            $config['cookie_secure'],
            $config['cookie_httponly'],
            $config['cookie_samesite']
        );

        $this->session->setExpirationDate($expire);

        return true;
    }

    /**
     * Returns a value from the session.
     *
     * @param string $key Session key
     * @param mixed|null $default (optional) Default value
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Stores a value in the session.
     *
     * @param string $key Session key
     * @param mixed $value Session data
     *
     * @return static
     */
    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Checks if the key was set.
     *
     * @param string $key Session key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Gets a value from session if the key exists, otherwise associate
     * the specified key with the value returned by invoking the callback.
     *
     * @param string $key Session key
     * @param callable $callback Callback function
     *
     * @return mixed
     */
    public function load(string $key, callable $callback): mixed
    {
        if (!$this->has($key)) {
            $this->set($key, $callback($key));
        }

        return $this->get($key);
    }

    /**
     * Removes a value from the session.
     *
     * @param string $key Session key
     *
     * @return static
     */
    public function delete(string $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Access flash object.
     *
     * @return Flash
     */
    public function flash(): Flash
    {
        if ($this->flash === null) {
            $this->flash = new Flash($this->data[$this->config['flash_slot']] ?? []);
        }

        return $this->flash;
    }

    /**
     * Clears all session data.
     *
     * @param bool $flash
     *
     * @return static
     */
    public function clear(bool $flash = true): static
    {
        $f = $this->flash();
        if ($flash) {
            $f->clear();
        }
        $this->data = [];

        return $this;
    }

    /**
     * Regenerates the session id.
     *
     * @param boolean $keep (optional) Keep old data associated with the old ID
     * @return boolean
     */
    public function regenerate(bool $keep = false): bool
    {
        if ($this->session === null) {
            return false;
        }

        $session_id = $this->handler->generateSessionId();

        $session = $this->handler->create($session_id, $this->session->expiresAt(), $this->session->data());

        if ($session === null) {
            return false;
        }

        if (!$keep) {
            $this->handler->delete($this->session);
        }

        $this->session = $session;

        return true;
    }

    /**
     * Destroys all data registered to the session and the session itself.
     *
     * @return boolean
     */
    public function destroy(): bool
    {
        if ($this->session === null) {
            return false;
        }

        $this->clear();
        $config = $this->config;

        if (!$this->handler->delete($this->session)) {
            return false;
        }

        $this->session = null;

        $this->container->setCookie(
            $config['cookie_name'],
            '',
            1,
            $config['cookie_path'],
            $config['cookie_domain'],
            $config['cookie_secure'],
            $config['cookie_httponly']
        );

        return true;
    }

    /**
     * Get the session handler object
     *
     * @return SessionHandler
     */
    public function getHandler(): SessionHandler
    {
        return $this->handler;
    }

    /**
     * Get the configuration for this session
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}