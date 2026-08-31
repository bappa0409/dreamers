<?php

namespace Database\Seeders;

use App\Http\Controllers\Api\ApprovalWorkflowController;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultApprovalWorkflowSeeder extends Seeder
{
    /**
     * Seed a default single-step workflow for every module/action pair the
     * generic ApprovalService now actually understands (see
     * ApprovalWorkflowController::WIRED_MODULE_ACTIONS), except Member/create
     * which predates this migration and is left untouched.
     *
     * Idempotent: if a workflow already exists for a module/action, it is
     * left alone (admin may have already configured it via the UI).
     */
    public function run(): void
    {
        $defaultApprover = User::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query
                    ->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('roles.name', 'system_analyst');
                    })
                    ->orWhereHas('roles.permissions', function ($permissionQuery) {
                        $permissionQuery->where(
                            'permissions.name',
                            'Approval.approve'
                        );
                    });
            })
            ->orderBy('id')
            ->first();

        if (!$defaultApprover) {
            $this->command?->warn(
                'DefaultApprovalWorkflowSeeder: no eligible approver found (active user with '.
                'system_analyst role or Approval.approve permission) — skipping. '.
                'Configure workflows manually via the Approval Workflow Builder instead.'
            );

            return;
        }

        $pairs = ApprovalWorkflowController::WIRED_MODULE_ACTIONS;
        unset($pairs['Member']);

        foreach ($pairs as $module => $actions) {
            foreach ($actions as $action) {
                $exists = ApprovalWorkflow::query()
                    ->where('module', $module)
                    ->where('action', $action)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::transaction(function () use ($module, $action, $defaultApprover) {
                    $workflow = ApprovalWorkflow::create([
                        'module' => $module,
                        'action' => $action,
                        'is_active' => true,
                    ]);

                    $workflow->steps()->create([
                        'step_no' => 1,
                        'approver_user_id' => $defaultApprover->id,
                    ]);
                });

                $this->command?->info(
                    "DefaultApprovalWorkflowSeeder: created default workflow for {$module}.{$action} ".
                    "(approver: {$defaultApprover->name})."
                );
            }
        }
    }
}