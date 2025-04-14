<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function update(Request $request)
    {
        // 1. Validazione dei dati
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        // 2. Recupera l'utente autenticato
        $user = Auth()->user();

        // 3. Filtra i dati in base ai campi fillable
        $data = $request->only(['name', 'email', 'password']);

        // 4. Gestisci la password (se presente)
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Rimuovi la password se non è stata modificata
        }

        // 5. Aggiorna i dati dell'utente
        $user->update($data);

        // 6. Reindirizza con un messaggio di successo
        return redirect(route('homepage'))->with('message', 'Profilo aggiornato con successo!');
    }
}
