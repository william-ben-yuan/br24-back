<?php

namespace App\Repositories;

interface CompanyRepositoryInterface
{
    public function getAllCompanies(): array;

    public function create(array $companyData): int;

    public function show(int $companyId): array;

    public function update(int $companyId, array $companyData): array;

    public function delete(int $companyId): void;
}
