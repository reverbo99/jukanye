<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\DeepLTranslateService;
use App\Support\Bilingual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    use StoresPublicImages;

    public function index(): View
    {
        $products = Product::query()->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(Request $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator);
        if ($request->hasFile('image')) {
            $data['image'] = $this->storePublicImage($request->file('image'), 'products');
        }
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator);
        if ($request->hasFile('image')) {
            $this->deletePublicImage($product->image);
            $data['image'] = $this->storePublicImage($request->file('image'), 'products');
        }
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deletePublicImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request, DeepLTranslateService $translator): array
    {
        $data = $request->validate(array_merge(
            Bilingual::pairRules('name'),
            Bilingual::pairRules('tagline', ['string', 'max:255'], false),
            Bilingual::pairRules('description', ['string'], false),
            [
                'price' => ['required', 'integer', 'min:0'],
                'currency' => ['nullable', 'string', 'max:10'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'image' => ['nullable', 'image', 'max:4096'],
            ]
        ));
        $data = $translator->fillMissingPairs($data, [
            ['name_sw', 'name_en'],
            ['tagline_sw', 'tagline_en'],
            ['description_sw', 'description_en'],
        ]);
        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        return $data;
    }
}
