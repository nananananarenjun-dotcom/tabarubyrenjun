<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index($productId)
    {
        $reviews = Review::with('user')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating');

        return response()->json([
            'message' => 'Berhasil mengambil ulasan',
            'data' => [
                'reviews' => $reviews,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $reviews->count()
            ]
        ], 200);
    }

    public function myProducts(Request $request)
    {
        $productIds = Review::where('user_id', $request->user()->id)
            ->pluck('product_id');

        return response()->json([
            'message' => 'Berhasil mengambil daftar produk yang sudah diulas',
            'data' => $productIds
        ], 200);
    }

    public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string',
        'photo' => 'nullable|image|max:5120',
        'video' => 'nullable|mimetypes:video/mp4,video/quicktime|max:10240'
    ]);

    $user = $request->user();

    $existingReview = Review::where('user_id', $user->id)
        ->where('product_id', $request->product_id)
        ->first();

    if ($existingReview) {
        return response()->json([
            'message' => 'Anda sudah memberikan ulasan untuk produk ini.'
        ], 400);
    }

    // WAJIB: produk harus benar-benar pernah dibeli user
    // dan status order harus completed
    $orderItem = OrderItem::where('product_id', $request->product_id)
        ->whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'completed');
        })
        ->first();

    if (!$orderItem) {
        return response()->json([
            'message' => 'Ulasan hanya bisa diberikan setelah pesanan selesai.'
        ], 403);
    }

    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('reviews/photos', 'public');
    }

    $videoPath = null;
    if ($request->hasFile('video')) {
        $videoPath = $request->file('video')->store('reviews/videos', 'public');
    }

    $review = Review::create([
        'user_id' => $user->id,
        'product_id' => $request->product_id,
        'rating' => $request->rating,
        'comment' => $request->comment,
        'photo' => $photoPath,
        'video' => $videoPath
    ]);

    $review->load('user');

    return response()->json([
        'message' => 'Terima kasih! Ulasan Anda berhasil disimpan.',
        'data' => $review
    ], 201);
}
}