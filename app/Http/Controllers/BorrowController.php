<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Post(
 *     path="/api/borrow",
 *     summary="Borrow a book",
 *     description="Allows a member to borrow a book",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"members_id", "books_id"},
 *             @OA\Property(property="members_id", type="integer", description="ID of the member borrowing the book"),
 *             @OA\Property(property="books_id", type="integer", description="ID of the book being borrowed")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Borrowed book successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="borrow", type="object", 
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="members_id", type="integer"),
 *                 @OA\Property(property="books_id", type="integer"),
 *                 @OA\Property(property="borrowed_at", type="string", format="date-time"),
 *                 @OA\Property(property="returned_at", type="string", format="date-time", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean"),
 *             @OA\Property(property="message", type="object")
 *         )
 *     )
 * )
 */

class BorrowController extends Controller
{

    
    public function borrow(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'books_id' => 'required|exists:books,id',
            'members_id' => 'required|exists:members,id',
        ]);

        if ($validator->passes()) {

            $membersId = $request->input('members_id');
            $booksId = $request->input('books_id');


            // check member for borrow
            $borrowedBooks = Borrow::where('members_id', $membersId)
                                    ->where('books_id', $booksId)
                                    ->where('returned_at', '!=', null)
                                    ->count();

            // check data book
            if ($borrowedBooks >= 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'You only can borrow 2 books'
                ]);
            }

            // update data book
            $bookStock = Book::find($booksId);
            if ($bookStock->stock < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Book is being borrowed'
                ]);
            }

            // update member if got penalized
            $isPenalized = Member::find($membersId);
            if ($isPenalized->is_penalized != null) {
                return response()->json([
                    'status' => false,
                    'message' => 'you get penalty for 3 days not cant borrow books' 
                ]);
            }

            $bookStock->stock -= 1;
            $bookStock->save();

            $borrow = Borrow::create([
                'members_id' => $membersId,
                'books_id' => $booksId,
                'borrowed_at' => now()
            ]);

            

            return response()->json([
                'status' => true,
                'message' => 'Borrowed book successfully',
                'borrow' => $borrow
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

    }
/**
 * @OA\Post(
 *     path="/api/returns",
 *     summary="Return a borrowed book",
 *     description="Allows a member to return a borrowed book. If the book is returned late, a penalty is applied.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"members_id", "books_id"},
 *             @OA\Property(property="members_id", type="integer", description="ID of the member returning the book"),
 *             @OA\Property(property="books_id", type="integer", description="ID of the book being returned")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Book returned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="members_id", type="integer"),
 *             @OA\Property(property="books_id", type="integer"),
 *             @OA\Property(property="returned_at", type="string", format="date-time", nullable=true),
 *             @OA\Property(property="penalty_applied", type="boolean"),
 *             @OA\Property(property="penalized_until", type="string", format="date-time", nullable=true)
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

    public function returns(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'books_id' => 'required|exists:books,id',
            'members_id' => 'required|exists:members,id',
        ]);

        if ($validator->passes()) {

            $membersId = $request->input('members_id');
            $booksId = $request->input('books_id');

            $borrow = Borrow::where('members_id', $membersId)
                            ->where('books_id', $booksId)
                            ->whereNull('returned_at')
                            ->first();

            $bookStock = Book::find($booksId);
            if ($bookStock->stock > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'The book is been returned'
                ]);
            } elseif ($borrow->borrowed_at == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong'
                ]);
            }

            $bookStock->stock += 1;
            $bookStock->save();

            // calculate borrow book 
            $borrowedAt = $borrow->borrowed_at;
            $dayBorrowed = now()->diffInDays($borrowedAt);

            // update data member if pas 7 day borrow
            if ($dayBorrowed > 7) {
                $isPenalized = now()->addDays(3);
                Member::where('id', $membersId)->update(['is_penalized' => $isPenalized]);
            }

            $borrow->update(['returned_at' => now()]);

            return response()->json([
                'status' => true,
                'message' => 'Book returned successfully',
                'penalty' => $dayBorrowed > 7,
                'penalized' => $dayBorrowed > 7 ? $isPenalized : null
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

    }
}
