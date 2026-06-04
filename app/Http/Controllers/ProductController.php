<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->where('status', 'available');

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $products = $query->get();

        $this->attachProductStats($products);

        return response()->json([
            'message' => 'Berhasil mengambil data produk',
            'data' => $products
        ], 200);
    }

    public function show($slug)
    {
        $product = Product::with(['category', 'images'])
            ->where('slug', $slug)
            ->where('status', 'available')
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $this->attachProductStats(collect([$product]));

        return response()->json([
            'message' => 'Berhasil mengambil detail produk',
            'data' => $product
        ], 200);
    }

    private function attachProductStats($products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $productIds = $products->pluck('id')->values();

        $reviewStats = Review::whereIn('product_id', $productIds)
            ->selectRaw('product_id, AVG(rating) as average_rating, COUNT(*) as total_reviews')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $soldStats = OrderItem::whereIn('product_id', $productIds)
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['completed', 'shipped', 'delivered']);
            })
            ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as sold_count')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $products->transform(function ($product) use ($reviewStats, $soldStats) {
            $reviewStat = $reviewStats->get($product->id);
            $soldStat = $soldStats->get($product->id);

            $averageRating = $reviewStat ? (float) $reviewStat->average_rating : 0;
            $totalReviews = $reviewStat ? (int) $reviewStat->total_reviews : 0;
            $soldCount = $soldStat ? (int) $soldStat->sold_count : 0;

            $product->setAttribute('average_rating', round($averageRating, 1));
            $product->setAttribute('total_reviews', $totalReviews);
            $product->setAttribute('sold_count', $soldCount);

            return $product;
        });
    }
}