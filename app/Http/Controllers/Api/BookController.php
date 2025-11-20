<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $q = Book::query();
        if ($request->filled('q')) {
            $q->where('title','ilike','%'.$request->q.'%');
        }
        return response()->json($q->paginate(15));
    }

    public function show(Book $book)
    {
        return response()->json($book);
    }
}
