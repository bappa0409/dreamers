<?php

namespace Tests\Support;

use App\Models\Account;
use App\Models\ApprovalWorkflow;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Database\Factories\AccountFactory;
use Database\Factories\MemberFactory;
use Database\Factories\RoleFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Shared scaffolding for the Finance-core test suite (Loan, Investment,
 * Subscription, Teller, Accounting). Uses each model's dedicated
 * Database\Factories\* class directly (Factory::new()->create()) rather
 * than Model::factory(), because these finance models do not currently
 * use the HasFactory trait.
 */
abstract class FinanceTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticate as a user with the `system_analyst` role.
     * User::isSystemAnalyst() makes hasPermission()/hasAnyPermission()
     * return true unconditionally, so this actor can hit any
     * permission-protected endpoint without seeding the permissions table.
     */
    protected function actingAsAnalyst(): User
    {
        $role = RoleFactory::new()->systemAnalyst()->create();

        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);

        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * An active member with a linked user account.
     */
    protected function activeMember(array $overrides = []): Member
    {
        return MemberFactory::new()->create($overrides);
    }

    /**
     * A single account with an explicit type/sub_type, used for
     * AccountingService::account($subType) lookups and for
     * cash/bank posting accounts.
     */
    protected function account(string $subType, string $type = 'asset', array $overrides = []): Account
    {
        return AccountFactory::new()
            ->withSubType($subType, $type)
            ->create($overrides);
    }

    /**
     * An active ApprovalWorkflow with one step, for module/action pairs
     * that ApprovalService::createRequest() checks before deciding whether
     * to hold a request for approval or auto-approve it immediately (see
     * the "Approval skipped" branch in ApprovalService::createRequest()).
     * Without this, requests for a module/action with no active workflow
     * are auto-approved on creation, which is the correct production
     * behaviour but usually not what a test asserting a 'pending' status
     * wants.
     */
    protected function activeApprovalWorkflow(
        string $module,
        string $action,
        ?User $approver = null
    ): ApprovalWorkflow {
        $approver ??= User::factory()->create();

        $workflow = ApprovalWorkflow::create([
            'module' => $module,
            'action' => $action,
            'is_active' => true,
        ]);

        $workflow->steps()->create([
            'step_no' => 1,
            'approver_user_id' => $approver->id,
        ]);

        return $workflow;
    }

    /**
     * The standard chart-of-accounts entries the finance services look
     * up by sub_type. Create only what a given test needs by calling
     * account() directly, or grab the full set here.
     */
    protected function seedCoreAccounts(): array
    {
        return [
            'cash' => $this->account('cash', 'asset'),
            'bank' => $this->account('bank', 'asset'),
            'investment' => $this->account('investment', 'asset'),
            'member_loan_receivable' => $this->account('member_loan_receivable', 'asset'),
            'loan_interest_income' => $this->account('loan_interest_income', 'income'),
            'receivable' => $this->account('receivable', 'asset'),
            'subscription_income' => $this->account('subscription_income', 'income'),
            'fine_income' => $this->account('fine_income', 'income'),
            'income' => $this->account('donation_income', 'income'),
            'investment_income' => $this->account('investment_income', 'income'),
            'liability' => $this->account('member_savings', 'liability'),
        ];
    }
}
