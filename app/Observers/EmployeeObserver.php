<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        EmployeeHistory::create([
            'employee_id' => $employee->id,
            'old_position_id' => null,
            'new_position_id' => $employee->position_id,
            'old_status' => null,
            'new_status' => $employee->status,
            'old_salary' => 0,
            'new_salary' => $employee->salary_base,
            'changed_by' => Auth::id(),
            'reason' => 'Création initiale du dossier employé',
        ]);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        // On récupère uniquement les champs qui ont changé
        $dirty = $employee->getDirty();
        
        // Liste des champs dont nous voulons tracer l'historique
        $trackedFields = ['position_id', 'status', 'salary_base'];
        $hasChanges = false;
        
        foreach ($trackedFields as $field) {
            if (array_key_exists($field, $dirty)) {
                $hasChanges = true;
                break;
            }
        }

        if ($hasChanges) {
            EmployeeHistory::create([
                'employee_id' => $employee->id,
                'old_position_id' => $employee->getOriginal('position_id'),
                'new_position_id' => $employee->position_id,
                'old_status' => $employee->getOriginal('status'),
                'new_status' => $employee->status,
                'old_salary' => $employee->getOriginal('salary_base'),
                'new_salary' => $employee->salary_base,
                'changed_by' => Auth::id(),
                'reason' => 'Mise à jour automatique des informations du dossier',
            ]);
        }
    }
}
