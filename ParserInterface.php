<?php

namespace parser;

interface ParserInterface
{
    public function getDomain(): string;
    public function getPageByUrl(string $url): ?string;
    public function getNeedLinks(string $html): ?array;
    public function extractData(string $html): ?array;
}