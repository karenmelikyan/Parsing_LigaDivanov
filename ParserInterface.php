<?php

namespace parser;

interface ParserInterface
{
    /** must return current domain name */
    public function getDomain(): string;

    /** must return html of page as string  */
    public function getPageByUrl(string $url): ?string;

    /** must return all filtred links as array */
    public function getNeedLinks(string $html): ?array;

    /**
     *  must return all data of page as associative
     *  array with keys appropriate of database column names
     */
    public function extractData(string $html): ?array;
}