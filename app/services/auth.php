<?php
declare(strict_types=1);
function auth_service_login(PDO $pdo, array $input): array
{
    $email = strtolower(validation_text($input, "email"));
    $password = is_string($input["password"] ?? null) ? $input["password"] : "";
    // Limitation locale par session, adaptée à la démonstration.
    $now = time();
    $attempts = array_values(
        array_filter(
            $_SESSION["attempts"] ?? [],
            fn(int $t): bool => $t > $now - 60,
        ),
    );
    if (count($attempts) >= 5) {
        return ["Trop de tentatives. Réessayez dans une minute."];
    }
    $user = filter_var($email, FILTER_VALIDATE_EMAIL)
        ? user_repository_findByEmail($pdo, $email)
        : null;
    $hash =
        $user["password"] ??
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    if (!password_verify($password, $hash) || !$user) {
        $_SESSION["attempts"] = [...$attempts, $now];
        return ["Adresse e-mail ou mot de passe incorrect."];
    }
    session_regenerate_id(true);
    $_SESSION["user"] = [
        "id" => (int) $user["id"],
        "name" => $user["name"],
        "email" => $user["email"],
    ];
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
    unset($_SESSION["attempts"]);
    return [];
}
function auth_service_logout(PDO $pdo): void
{
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}
