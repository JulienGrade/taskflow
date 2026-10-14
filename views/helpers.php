<?php
declare(strict_types=1);
function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8",
    );
}
function url(string $route, array $params = []): string
{
    return "/?" . http_build_query(["route" => $route] + $params);
}
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' .
        e($_SESSION["csrf"]) .
        '">';
}
function go(string $route, array $params = []): never
{
    header("Location: " . url($route, $params), true, 303);
    exit();
}
function flash(string $message): void
{
    $_SESSION["flash"] = $message;
}
function status_label(string $status): string
{
    return ["todo" => "À faire", "doing" => "En cours", "done" => "Terminée"][
        $status
    ] ?? $status;
}
function priority_label(string $priority): string
{
    return ["low" => "Basse", "medium" => "Normale", "high" => "Haute"][
        $priority
    ] ?? $priority;
}
function status_class(string $status): string
{
    return [
        "todo" => "bg-slate-100 text-slate-600",
        "doing" => "bg-indigo-50 text-indigo-700",
        "done" => "bg-emerald-50 text-emerald-700",
    ][$status] ?? "";
}
function priority_class(string $priority): string
{
    return [
        "low" => "bg-slate-100 text-slate-600",
        "medium" => "bg-amber-50 text-amber-700",
        "high" => "bg-rose-50 text-rose-700",
    ][$priority] ?? "";
}
function date_label(?string $date): string
{
    return $date ? date("d/m/Y", strtotime($date)) : "Sans échéance";
}

// Extraire un caractère Unicode sans imposer l’extension mbstring.
function initial(string $value): string
{
    preg_match('/^./us', trim($value), $matches);
    return $matches[0] ?? '?';
}
