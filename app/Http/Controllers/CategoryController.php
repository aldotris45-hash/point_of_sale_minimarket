<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Category\CategoryServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryServiceInterface $service)
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(): View
    {
        return view('categories.index');
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->service->update($category, $request->validated());

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->service->delete($category);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function data()
    {
        $query = Category::query()->select(['id', 'name', 'description', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function (Category $c) {
                $editUrl = route('kategori.edit', $c);
                $deleteUrl = route('kategori.destroy', $c);
                $csrf = csrf_token();
                return <<<HTML
                    <div class="d-flex justify-content-end gap-1">
                        <a href="{$editUrl}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini? Tindakan tidak dapat dibatalkan.');">
                            <input type="hidden" name="_token" value="{$csrf}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                HTML;
            })
            ->rawColumns(['action'])
            ->toJson();
    }
}
