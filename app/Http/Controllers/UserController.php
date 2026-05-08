<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs et les employés sans compte.
     */
    public function index()
    {
        // Récupérer les utilisateurs avec leur rôle et les informations de l'employé associé
        // Utilisation de la pagination simple pour Inertia (8 par page)
        $users = User::with(['role', 'employee'])
            ->orderBy('created_at', 'desc')
            ->paginate(8);

        // Récupérer les employés qui n'ont pas encore de compte utilisateur (user_id est NULL)
        $employeesWithoutAccount = Employee::whereNull('user_id')
            ->select('id', 'first_name', 'last_name', 'email', 'position_id')
            ->get();

        // Récupérer tous les rôles pour le formulaire de création
        $roles = Role::all();

        // Retourner la vue Inertia avec les données
        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'employeesWithoutAccount' => $employeesWithoutAccount,
            'roles' => $roles
        ]);
    }

    /**
     * Crée un nouvel utilisateur et l'associe à un employé.
     */
    public function store(Request $request)
    {
        // Validation des données entrantes
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'role_id' => 'required|exists:roles,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Utiliser une transaction pour s'assurer que les deux opérations réussissent
        DB::transaction(function () use ($validated) {
            // Créer l'utilisateur avec le rôle sélectionné
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
            ]);

            // Récupérer l'employé pour le mettre à jour
            $employee = Employee::findOrFail($validated['employee_id']);
            $employee->update([
                'user_id' => $user->id
            ]);
        });

        // Rediriger vers la liste avec un message de succès
        return redirect()->route('admin.users.index')
            ->with('success', 'Le compte utilisateur a été créé avec succès.');
    }

    /**
     * Met à jour le rôle d'un utilisateur.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'role_id' => $validated['role_id']
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Le rôle de l\'utilisateur a été mis à jour.');
    }

    /**
     * Active ou désactive un utilisateur.
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'actif' ? 'inactif' : 'actif';
        
        $user->update([
            'status' => $newStatus
        ]);

        $message = $newStatus === 'actif' ? 'Le compte a été activé.' : 'Le compte a été désactivé.';

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}
