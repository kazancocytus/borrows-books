<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Support\Facades\Validator;


class BooksController extends Controller
{
 
    /**
     * @OA\Get(
     *     path="/api/check-books",
     *     summary="Get all books with stock",
     *     description="Retrieve a list of books with their stock information.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(
     *                 property="books",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="author", type="string"),
     *                     @OA\Property(property="stock", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function check()
    {

        $books = Book::where('stock', '>', 0)
                    ->orderBy('id', 'ASC')
                    ->get();
        
        return response()->json([
            'books' => $books
        ]);

    }


    

}
