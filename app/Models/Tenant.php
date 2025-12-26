<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Tenant
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $status,
        public ?string $email,
        public ?string $phone,
        public ?string $cpfCnpj
    ) {}

    public static function findBySlug(string $slug): ?self
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenants WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);

        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return new self(
            (int)$row['id'],
            (string)$row['name'],
            (string)$row['slug'],
            (string)$row['status'],
            $row['email'] !== null ? (string)$row['email'] : null,
            $row['phone'] !== null ? (string)$row['phone'] : null,
            $row['cpf_cnpj'] !== null ? (string)$row['cpf_cnpj'] : null
        );
    }

    public static function findById(int $id): ?self
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return new self(
            (int)$row['id'],
            (string)$row['name'],
            (string)$row['slug'],
            (string)$row['status'],
            $row['email'] !== null ? (string)$row['email'] : null,
            $row['phone'] !== null ? (string)$row['phone'] : null,
            $row['cpf_cnpj'] !== null ? (string)$row['cpf_cnpj'] : null
        );
    }

    /** @return array<int, self> */
    public static function all(): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->query('SELECT * FROM tenants ORDER BY id DESC');

        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = new self(
                (int)$row['id'],
                (string)$row['name'],
                (string)$row['slug'],
                (string)$row['status'],
                $row['email'] !== null ? (string)$row['email'] : null,
                $row['phone'] !== null ? (string)$row['phone'] : null,
                $row['cpf_cnpj'] !== null ? (string)$row['cpf_cnpj'] : null
            );
        }

        return $items;
    }

    public static function create(
        string $name,
        string $slug,
        string $status = 'active',
        ?string $email = null,
        ?string $phone = null,
        ?string $cpfCnpj = null
    ): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO tenants (name, slug, status, email, phone, cpf_cnpj) VALUES (:name, :slug, :status, :email, :phone, :cpf_cnpj)');
        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
            'email' => $email,
            'phone' => $phone,
            'cpf_cnpj' => $cpfCnpj,
        ]);
    }
}
