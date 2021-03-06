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

namespace Opis\Colibri\Session\Handlers;

use Opis\Colibri\Session\{SessionData, SessionHandler};
use Opis\Database\{Connection, Schema, Schema\Blueprint, SQL\WhereStatement, DatabaseHandler};

class Database implements SessionHandler
{
    use SessionIdTrait;

    protected ?DatabaseHandler $db;
    protected string $table;
    protected array $columns;
    protected ?string $name = null;

    /**
     * Database constructor.
     * @param Connection $connection
     * @param string $table
     * @param array $columns
     */
    public function __construct(Connection $connection, string $table = 'sessions', array $columns = [])
    {
        $this->db = $connection->getDatabaseHandler();
        $this->table = $table;
        $this->columns = $columns + [
            'id' => 'id',
            'name' => 'name',
            'expire' => 'expire',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'data' => 'data',
        ];
    }

    /**
     * @inheritDoc
     */
    public function open(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->name = null;
    }

    /**
     * @inheritDoc
     */
    public function create(string $session_id, int $expire, array $data = []): SessionData
    {
        $data = new SessionData($session_id, $expire, $data);
        $this->createOrUpdate($data);
        return $data;
    }

    /**
     * @param SessionData $data
     * @return bool
     */
    protected function createOrUpdate(SessionData $data): bool
    {
        if ($this->name === null) {
            return false;
        }

        $col = $this->columns;

        $exists = $this->db->from($this->table)
                ->where($col['name'])->is($this->name)
                ->andWhere($col['id'])->is($data->id())
                ->limit(1)
                ->count() > 0;

        $d = [];
        $d[$col['expire']] = $data->expiresAt();
        $d[$col['created_at']] = $data->createdAt();
        $d[$col['updated_at']] = $data->updatedAt();
        $d[$col['data']] = $this->serializeData($data->data());

        if ($exists) {
            $d[$col['updated_at']] = time();
            return $this->db->update($this->table)
                    ->where($col['name'])->is($this->name)
                    ->andWhere($col['id'])->is($data->id())
                    ->set($d) > 0;
        }

        $d[$col['name']] = $this->name;
        $d[$col['id']] = $data->id();

        return $this->db->insert($d)->into($this->table);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function serializeData(array $data): string
    {
        return serialize($data);
    }

    /**
     * @inheritDoc
     */
    public function update(SessionData $data): bool
    {
        return $this->createOrUpdate($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(SessionData $data): bool
    {
        return $this->deleteById($data->id());
    }

    /**
     * @inheritDoc
     */
    public function deleteById(string $session_id): bool
    {
        return $this->deleteMultipleById([$session_id]) > 0;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultipleById(array $session_ids): int
    {
        if ($this->name === null) {
            return false;
        }

        $col = $this->columns;

        return $this->db->from($this->table)
            ->where($col['id'])->in($session_ids)
            ->andWhere($col['name'])->is($this->name)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function read(string $session_id): ?SessionData
    {
        if ($this->name === null) {
            return null;
        }

        $col = $this->columns;

        $result = $this->db->from($this->table)
            ->where($col['id'])->is($session_id)
            ->andWhere($col['name'])->is($this->name)
            ->limit(1)
            ->select()
            ->first();

        if (!$result) {
            return null;
        }

        $result = (array)$result;

        return new SessionData(
            $result[$col['id']],
            $result[$col['expire']] ?? 0,
            $this->unserializeData($result[$col['data']] ?? null),
            $result[$col['created_at']] ?? null,
            $result[$col['updated_at']] ?? null
        );
    }

    /**
     * @param string|null $data
     * @return array
     */
    protected function unserializeData(?string $data): array
    {
        if (!$data) {
            return [];
        }

        return unserialize($data);
    }

    /**
     * @inheritDoc
     */
    public function gc(int $maxLifeTime): bool
    {
        if ($this->name === null) {
            return false;
        }

        $timestamp = time() - $maxLifeTime;

        $col = $this->columns;

        return $this->db->from($this->table)
                ->where($col['name'])->is($this->name)
                ->andWhere(static function (WhereStatement $query) use ($col, $timestamp) {
                    $query
                        ->where(static function (WhereStatement $query) use ($col, $timestamp) {
                            $query
                                ->where($col['expire'])->is(0)
                                ->andWhere($col['updated_at'])->lessThan($timestamp);
                        })
                        ->orWhere(static function (WhereStatement $query) use ($col, $timestamp) {
                            $query
                                ->where($col['expire'])->isNot(0)
                                ->andWhere($col['expire'])->lessThan($timestamp);
                        });
                })
                ->delete() > 0;
    }

    public static function setup(Schema $schema, string $table_name = 'sessions', array $columns = [], ?int $sid_length = null): void
    {
        $columns += [
            'id' => 'id',
            'name' => 'name',
            'expire' => 'expire',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'data' => 'data',
        ];

        if ($sid_length === null) {
            $sid_length = (int) ini_get('session.sid_length');
        }

        // 32-255
        $sid_length = min(255, max(32, $sid_length));

        $schema->create($table_name, static function (Blueprint $table) use ($columns, $sid_length) {
            $table->fixed($columns['id'], $sid_length)->notNull();
            $table->string($columns['name'], 32)->notNull();
            $table->integer($columns['expire'])->unsigned()->notNull()->defaultValue(0);
            $table->integer($columns['created_at'])->unsigned()->notNull();
            $table->integer($columns['updated_at'])->unsigned()->notNull();
            $table->binary($columns['data'])->size('big')->defaultValue(null);

            $table->primary([$columns['id'], $columns['name']]);
            $table->index($columns['expire']);
            $table->index($columns['updated_at']);
        });
    }
}