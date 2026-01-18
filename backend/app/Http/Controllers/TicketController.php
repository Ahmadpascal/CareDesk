<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketReplyStoreRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketReplyResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Ticket::query();
            $query->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->priority) {
                $query->where('priority', $request->priority);
            }

            if (auth()->user()->role != 'admin') {
                $query->where('user_id', auth()->user()->id);
            }

            $tickets = $query->get();

            return response()->json([
                'message' => 'Tickets fetched successfully',
                'data' => TicketResource::collection($tickets),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if(!$ticket) {
                return response()->json([
                    'message' => 'Ticket not found',
                    'data' => null,
                ], 404);
            }

            if (auth()->user()->role != 'admin' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'data' => null,
                ], 403);
            }

            return response()->json([
                'message' => 'Ticket fetched successfully',
                'data' => new TicketResource($ticket),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high'
        ]);

        DB::beginTransaction();

        try {
            $ticket = new Ticket;
            $ticket->user_id = auth()->user()->id;
            // $ticket->code = 'TIC' . rand(1000, 9999);
            $ticket->title = $validated['title'];
            $ticket->description = $validated['description'];
            $ticket->priority = $validated['priority'];
            $ticket->save();

            $ticket->code = 'TIC-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT);
            $ticket->save();

            DB::commit();

            return response()->json([
                'message' => 'Ticket created successfully',
                'data' => new TicketResource($ticket),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    public function storeReply(TicketReplyStoreRequest $request, $code)
    {
        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Ticket not found',
                    'data' => null,
                ], 404);
            }

            if (auth()->user()->role != 'admin' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Unauthorized bro',
                    'data' => null,
                ], 403);
            }

            $reply = new TicketReply;
            $reply->ticket_id = $ticket->id;
            $reply->user_id = auth()->user()->id;
            $reply->content = $request['content'];
            $reply->save();

            if (auth()->user()->role === 'admin' && $request->has('status')) {
                $ticket->status = $request['status'];
                if ($request['status'] === 'resolved') {
                    $ticket->completed_at = now();
                }
                $ticket->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Reply created successfully',
                'data' => new TicketReplyResource($reply),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong',
                'data' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}