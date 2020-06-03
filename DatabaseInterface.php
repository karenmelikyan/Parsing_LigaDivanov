<?php

namespace parser;

interface DatabaseInterface
{
    /** takes an associative array & insert to database */
    public function insert($data): bool;

    /** returns all data from table */
    public function getAll(): ?array;

    /** returns data by column name & value */
    public function getByColumn($columnName, $value): ?array;

    /** deletes data by column name & value */
    public function deleteByColumn($columnName, $value): bool;
}

