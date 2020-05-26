<?php

namespace parser;

interface DatabaseInterface
{
   public function insert($data): bool;
   public function getAll(): ?array;
   public function getByColumn($columnName, $value): ?array;
   public function deleteByColumn($columnName, $value): bool;
}