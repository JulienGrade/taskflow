<?php
declare(strict_types=1);
function validation_text(array $input, string $key): string
{
    return is_string($input[$key] ?? null) ? trim($input[$key]) : "";
}
function validation_length(string $text): int
{
    return preg_match_all("/./us", $text) ?: 0;
}
function validation_date(string $date): bool
{
    $parsed = \DateTimeImmutable::createFromFormat("!Y-m-d", $date);
    return $parsed !== false && $parsed->format("Y-m-d") === $date;
}
