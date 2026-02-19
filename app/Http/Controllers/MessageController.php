<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use AuthorizesRequests;

    // RÉCUPÈRE tous les messages pour une réservation
    public function index(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        // On charge les messages avec l'utilisateur qui les a envoyés
        $messages = $reservation->messages()->with('user:id,name')->get();

        return response()->json($messages);
    }

    // ENVOIE un nouveau message
    public function store(Request $request, Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        $request->validate(['body' => 'required|string|max:2000']);

        $message = $reservation->messages()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        broadcast(new \App\Events\MessageSent($message->load('user')))->toOthers();

        // On recharge le message avec l'utilisateur pour le renvoyer au front-end
        $message->load('user:id,name');

        // On renvoie le nouveau message en JSON
        return response()->json($message, 201); // 201 = "Created"
    }
}
