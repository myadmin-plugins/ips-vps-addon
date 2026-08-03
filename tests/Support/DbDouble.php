<?php

declare(strict_types=1);

namespace Detain\MyAdminVpsIps\Tests\Support;

/**
 * Stands in for the MyAdmin database handle the addon reads its existing
 * additional-IP invoices out of.
 */
final class DbDouble
{
    /**
     * Every SQL statement that reached the database, in order.
     *
     * @var array<int, string>
     */
    public $queries = [];

    /**
     * @var array<string, mixed>
     */
    public $Record = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private $rows;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * @param array<int, array<string, mixed>> $rows rows the next query returns
     */
    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    /**
     * @param  string      $sql
     * @param  int|null    $line
     * @param  string|null $file
     * @return bool
     */
    public function query($sql, $line = null, $file = null)
    {
        $this->queries[] = $sql;
        $this->cursor = 0;
        $this->Record = [];
        return true;
    }

    /**
     * @return int
     */
    public function num_rows()
    {
        return count($this->rows);
    }

    /**
     * @param  int|null $mode
     * @return bool
     */
    public function next_record($mode = null)
    {
        if (!isset($this->rows[$this->cursor])) {
            $this->Record = [];
            return false;
        }
        $this->Record = $this->rows[$this->cursor];
        $this->cursor++;
        return true;
    }

    /**
     * @return void
     */
    public function free()
    {
        $this->cursor = 0;
    }

    /**
     * @param  string $value
     * @return string
     */
    public function real_escape($value)
    {
        return addslashes((string) $value);
    }
}
