<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
/**
     * @OA\Get(
     *     path="/api/check-members",
     *     summary="Get all members with their borrow count",
     *     description="Retrieve a list of all members along with the count of books they currently have borrowed",
     *     @OA\Response(
     *         response=200,
     *         description="List of members retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="members", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="is_penalized", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="borrow_count", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

class MembersController extends Controller
{
    

    public function check()
    {
        try {
            $members = Member::withCount(['borrow' => function($query) {
                $query->whereNull('returned_at'); 
            }])->orderBy('id', 'ASC')->get();

            return response()->json([
                'status' => true,
                'members' => $members
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
}
