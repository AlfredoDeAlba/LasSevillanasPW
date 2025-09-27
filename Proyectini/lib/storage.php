<?php

namespace App\Lib;

const DATA_DIR = __DIR__ . '/../data';
const PRODUCTS_FILE = DATA_DIR . '/products.json';

function ensureDataDir(): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0775, true);
    }
}

function readProducts(): array
{
    ensureDataDir();
    if (!file_exists(PRODUCTS_FILE)) {
        file_put_contents(PRODUCTS_FILE, '[]');
        return [];
    }

    $raw = file_get_contents(PRODUCTS_FILE);
    if ($raw === false || $raw === '') {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function writeProducts(array $products): void
{
    ensureDataDir();
    $payload = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($payload === false) {
        throw new \RuntimeException('No se pudo codificar el JSON de productos.');
    }

    $result = file_put_contents(PRODUCTS_FILE, $payload, LOCK_EX);
    if ($result === false) {
        throw new \RuntimeException('No se pudo guardar el archivo de productos.');
    }
}

function findProduct(string $id): ?array
{
    foreach (readProducts() as $product) {
        if (($product['id'] ?? '') === $id) {
            return $product;
        }
    }

    return null;
}

function generateId(string $name): string
{
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9-]+/i', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'producto-' . uniqid();
}

function upsertProduct(array $payload): array
{
    $products = readProducts();
    $id = $payload['id'] ?? null;

    if ($id === null || $id === '') {
        $payload['id'] = generateId($payload['name'] ?? '');
        $products[] = $payload;
    } else {
        $updated = false;
        foreach ($products as &$product) {
            if (($product['id'] ?? '') === $id) {
                $product = array_merge($product, $payload);
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $products[] = $payload;
        }
    }

    writeProducts($products);
    return $payload;
}

function deleteProduct(string $id): void
{
    $products = readProducts();
    $filtered = array_values(array_filter($products, static fn ($product) => ($product['id'] ?? '') !== $id));
    writeProducts($filtered);
}