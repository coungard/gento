<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Part;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Список разделов каталога.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->roots()
            ->ordered()
            ->withPartCounts()
            ->with(['children' => fn ($query) => $query->active()->ordered()->withPartCounts()])
            ->get();

        return view('catalog.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Запчасти раздела с учётом подразделов.
     */
    public function show(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        $children = $category->children()->active()->ordered()->withPartCounts()->get();
        $categoryIds = $children->pluck('id')->push($category->getKey())->all();

        $filters = $this->filters($request);

        $parts = $this->query($categoryIds, $filters)
            ->paginate(12)
            ->withQueryString();

        $brands = Part::query()
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $breadcrumbs = collect();

        if ($category->parent) {
            $breadcrumbs->push($category->parent);
        }

        return view('catalog.category', [
            'category' => $category,
            'children' => $children,
            'parts' => $parts,
            'brands' => $brands,
            'breadcrumbs' => $breadcrumbs,
            'filters' => $filters,
        ]);
    }

    /**
     * @param  list<int>  $categoryIds
     * @param  array{sort: string, brand: string, price_from: int|null, price_to: int|null, in_stock: bool}  $filters
     * @return Builder<Part>
     */
    private function query(array $categoryIds, array $filters): Builder
    {
        $query = Part::query()
            ->active()
            ->whereIn('category_id', $categoryIds)
            ->with('category')
            ->when($filters['brand'] !== '', fn (Builder $q) => $q->where('brand', $filters['brand']))
            ->when($filters['in_stock'], fn (Builder $q) => $q->where('stock', '>', 0))
            ->priceBetween(
                $filters['price_from'] !== null ? (float) $filters['price_from'] : null,
                $filters['price_to'] !== null ? (float) $filters['price_to'] : null,
            );

        return match ($filters['sort']) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            'new' => $query->latest('id'),
            default => $query->orderByDesc('is_featured')->orderBy('name'),
        };
    }

    /**
     * @return array{sort: string, brand: string, price_from: int|null, price_to: int|null, in_stock: bool}
     */
    private function filters(Request $request): array
    {
        $sort = $request->string('sort')->toString();
        $allowedSorts = ['popular', 'price_asc', 'price_desc', 'name', 'new'];

        return [
            'sort' => in_array($sort, $allowedSorts, true) ? $sort : 'popular',
            'brand' => mb_substr($request->string('brand')->toString(), 0, 128),
            'price_from' => $request->filled('price_from') ? max(0, $request->integer('price_from')) : null,
            'price_to' => $request->filled('price_to') ? max(0, $request->integer('price_to')) : null,
            'in_stock' => $request->boolean('in_stock'),
        ];
    }
}
