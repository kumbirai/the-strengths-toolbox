<?php

namespace App\Repositories;

interface BaseRepositoryInterface
{
    public function find(int $id);

    public function create(array $data);

    public function update($model, array $data);

    public function delete($model): bool;

    public function all();
}
