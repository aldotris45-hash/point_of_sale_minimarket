<?php

namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService implements CategoryServiceInterface
{
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function findOrFail(int $id): Category
    {
        return Category::findOrFail($id);
    }
}
