<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryServiceInterface
{
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;

    public function findOrFail(int $id): Category;
}
