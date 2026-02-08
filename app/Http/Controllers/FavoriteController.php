<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Get all favorites for the authenticated user.
     */
    public function getUserFavorites(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }

            // Get user's favorited books with pagination
            $perPage = (int) $request->get('per_page', 20);
            $perPage = $perPage > 0 ? min($perPage, 100) : 20;

            $favorites = $user->favoritedBooks()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $favorites->items(),
                'meta' => [
                    'total' => $favorites->total(),
                    'per_page' => $favorites->perPage(),
                    'current_page' => $favorites->currentPage(),
                    'last_page' => $favorites->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المفضلات',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a book to user's favorites.
     */
    public function addToFavorites(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }

            $validated = $request->validate([
                'book_id' => 'required|integer|exists:books,id',
            ]);

            // Check if already favorited
            $existing = Favorite::where('user_id', $user->id)
                ->where('book_id', $validated['book_id'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الكتاب موجود بالفعل في المفضلات',
                ], 409);
            }

            $favorite = Favorite::create([
                'user_id' => $user->id,
                'book_id' => $validated['book_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة الكتاب إلى المفضلات بنجاح',
                'data' => $favorite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المفضلة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a book from user's favorites.
     */
    public function removeFromFavorites($bookId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }

            $favorite = Favorite::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'المفضلة غير موجودة',
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'تمت إزالة الكتاب من المفضلات بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إزالة المفضلة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a book is in user's favorites.
     */
    public function isFavorited($bookId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => true,
                    'isFavorited' => false,
                ]);
            }

            $isFavorited = Favorite::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->exists();

            return response()->json([
                'success' => true,
                'isFavorited' => $isFavorited,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a book.
     */
    public function toggleFavorite(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تسجيل الدخول أولاً',
                ], 401);
            }

            $validated = $request->validate([
                'book_id' => 'required|integer|exists:books,id',
            ]);

            $favorite = Favorite::where('user_id', $user->id)
                ->where('book_id', $validated['book_id'])
                ->first();

            if ($favorite) {
                $favorite->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'تمت إزالة الكتاب من المفضلات',
                    'isFavorited' => false,
                ]);
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'book_id' => $validated['book_id'],
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'تمت إضافة الكتاب إلى المفضلات',
                    'isFavorited' => true,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
