<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Member.view', 'display_name' => 'View Members', 'module' => 'Member'],
            ['name' => 'Member.create', 'display_name' => 'Create Member', 'module' => 'Member'],
            ['name' => 'Member.update', 'display_name' => 'Update Member', 'module' => 'Member'],
            ['name' => 'Member.delete', 'display_name' => 'Delete Member', 'module' => 'Member'],

            ['name' => 'User.view', 'display_name' => 'View Users', 'module' => 'User'],
            ['name' => 'User.create', 'display_name' => 'Create User', 'module' => 'User'],
            ['name' => 'User.update', 'display_name' => 'Update User', 'module' => 'User'],
            ['name' => 'User.delete', 'display_name' => 'Delete User', 'module' => 'User'],

            ['name' => 'Role.view', 'display_name' => 'View Roles', 'module' => 'Role'],
            ['name' => 'Role.create', 'display_name' => 'Create Role', 'module' => 'Role'],
            ['name' => 'Role.update', 'display_name' => 'Update Role', 'module' => 'Role'],
            ['name' => 'Role.delete', 'display_name' => 'Delete Role', 'module' => 'Role'],

            ['name' => 'Mail.view', 'display_name' => 'View Mailing', 'module' => 'Mail'],
            ['name' => 'Mail.create', 'display_name' => 'Create Mail Campaign', 'module' => 'Mail'],
            ['name' => 'Mail.update', 'display_name' => 'Update Mail Campaign', 'module' => 'Mail'],
            ['name' => 'Mail.send', 'display_name' => 'Send Mail Campaign', 'module' => 'Mail'],
            ['name' => 'Mail.delete', 'display_name' => 'Delete Mail Campaign', 'module' => 'Mail'],

            ['name' => 'Finance.view', 'display_name' => 'View Finance', 'module' => 'Finance'],
            ['name' => 'Finance.create', 'display_name' => 'Create Finance Entry', 'module' => 'Finance'],
            ['name' => 'Finance.update', 'display_name' => 'Update Finance Entry', 'module' => 'Finance'],
            ['name' => 'Finance.delete', 'display_name' => 'Delete Finance Entry', 'module' => 'Finance'],

            ['name' => 'Investment.view', 'display_name' => 'View Investments', 'module' => 'Investment'],
            ['name' => 'Investment.create', 'display_name' => 'Create Investment', 'module' => 'Investment'],
            ['name' => 'Investment.update', 'display_name' => 'Update Investment', 'module' => 'Investment'],
            ['name' => 'Investment.delete', 'display_name' => 'Delete Investment', 'module' => 'Investment'],

            ['name' => 'Loan.view', 'display_name' => 'View Loans', 'module' => 'Loan'],
            ['name' => 'Loan.create', 'display_name' => 'Create Loan Request', 'module' => 'Loan'],
            ['name' => 'Loan.update', 'display_name' => 'Update Loan', 'module' => 'Loan'],
            ['name' => 'Loan.approve', 'display_name' => 'Approve or Reject Loan', 'module' => 'Loan'],
            ['name' => 'Loan.disburse', 'display_name' => 'Disburse Loan', 'module' => 'Loan'],
            ['name' => 'Loan.repay', 'display_name' => 'Receive Loan Repayment', 'module' => 'Loan'],

            ['name' => 'Nominee.view', 'display_name' => 'View Nominees', 'module' => 'Nominee'],
            ['name' => 'Nominee.create', 'display_name' => 'Create Nominee', 'module' => 'Nominee'],
            ['name' => 'Nominee.update', 'display_name' => 'Update Nominee', 'module' => 'Nominee'],
            ['name' => 'Nominee.verify', 'display_name' => 'Verify Nominee', 'module' => 'Nominee'],
            ['name' => 'Nominee.delete', 'display_name' => 'Remove Nominee', 'module' => 'Nominee'],

            ['name' => 'MemberExit.view', 'display_name' => 'View Member Exits', 'module' => 'Member Exit'],
            ['name' => 'MemberExit.create', 'display_name' => 'Initiate Member Exit', 'module' => 'Member Exit'],
            ['name' => 'MemberExit.review', 'display_name' => 'Review Member Exit', 'module' => 'Member Exit'],
            ['name' => 'MemberExit.approve', 'display_name' => 'Approve Member Exit', 'module' => 'Member Exit'],
            ['name' => 'MemberExit.settle', 'display_name' => 'Settle Member Exit', 'module' => 'Member Exit'],

            ['name' => 'Welfare.view', 'display_name' => 'View Welfare', 'module' => 'Welfare'],
            ['name' => 'Welfare.create', 'display_name' => 'Create Welfare Request', 'module' => 'Welfare'],
            ['name' => 'Welfare.review', 'display_name' => 'Review Welfare Request', 'module' => 'Welfare'],
            ['name' => 'Welfare.approve', 'display_name' => 'Approve Welfare Request', 'module' => 'Welfare'],
            ['name' => 'Welfare.disburse', 'display_name' => 'Disburse Welfare Assistance', 'module' => 'Welfare'],
            ['name' => 'Welfare.manage', 'display_name' => 'Manage Welfare Funds', 'module' => 'Welfare'],

            ['name' => 'Tour.view', 'display_name' => 'View Tours', 'module' => 'Tour'],
            ['name' => 'Tour.create', 'display_name' => 'Create Tour', 'module' => 'Tour'],
            ['name' => 'Tour.update', 'display_name' => 'Update Tour', 'module' => 'Tour'],
            ['name' => 'Tour.delete', 'display_name' => 'Delete Tour', 'module' => 'Tour'],

            ['name' => 'Meeting.view', 'display_name' => 'View Meetings', 'module' => 'Meeting'],
            ['name' => 'Meeting.create', 'display_name' => 'Create Meeting', 'module' => 'Meeting'],
            ['name' => 'Meeting.update', 'display_name' => 'Update Meeting', 'module' => 'Meeting'],
            ['name' => 'Meeting.delete', 'display_name' => 'Delete Meeting', 'module' => 'Meeting'],

            ['name' => 'Land.view', 'display_name' => 'View Land', 'module' => 'Land'],
            ['name' => 'Land.create', 'display_name' => 'Create Land', 'module' => 'Land'],
            ['name' => 'Land.update', 'display_name' => 'Update Land', 'module' => 'Land'],
            ['name' => 'Land.delete', 'display_name' => 'Delete Land', 'module' => 'Land'],

            ['name' => 'Project.view', 'display_name' => 'View Projects', 'module' => 'Project'],
            ['name' => 'Project.create', 'display_name' => 'Create Project', 'module' => 'Project'],
            ['name' => 'Project.update', 'display_name' => 'Update Project', 'module' => 'Project'],
            ['name' => 'Project.delete', 'display_name' => 'Delete Project', 'module' => 'Project'],

            ['name' => 'Poll.view', 'display_name' => 'View Polls', 'module' => 'Poll'],
            ['name' => 'Poll.create', 'display_name' => 'Create Poll', 'module' => 'Poll'],
            ['name' => 'Poll.update', 'display_name' => 'Update Poll', 'module' => 'Poll'],
            ['name' => 'Poll.delete', 'display_name' => 'Delete Poll', 'module' => 'Poll'],

            ['name' => 'Notice.view', 'display_name' => 'View Notices', 'module' => 'Notice'],
            ['name' => 'Notice.create', 'display_name' => 'Create Notice', 'module' => 'Notice'],
            ['name' => 'Notice.update', 'display_name' => 'Update Notice', 'module' => 'Notice'],
            ['name' => 'Notice.delete', 'display_name' => 'Delete Notice', 'module' => 'Notice'],

            ['name' => 'Notification.view', 'display_name' => 'View Notifications', 'module' => 'Notification'],
            ['name' => 'Notification.send', 'display_name' => 'Send Notifications', 'module' => 'Notification'],

            ['name' => 'Approval.view', 'display_name' => 'View Approvals', 'module' => 'Approval'],
            ['name' => 'Approval.approve', 'display_name' => 'Approve Requests', 'module' => 'Approval'],
            ['name' => 'Approval.reject', 'display_name' => 'Reject Requests', 'module' => 'Approval'],
            ['name' => 'Approval.update', 'display_name' => 'Update Approvals', 'module' => 'Approval'],

            ['name' => 'Document.view', 'display_name' => 'View Documents', 'module' => 'Document'],
            ['name' => 'Document.create', 'display_name' => 'Upload Documents', 'module' => 'Document'],
            ['name' => 'Document.update', 'display_name' => 'Update Documents', 'module' => 'Document'],
            ['name' => 'Document.delete', 'display_name' => 'Delete Documents', 'module' => 'Document'],
            ['name' => 'Document.manage', 'display_name' => 'Manage All Documents', 'module' => 'Document'],

            ['name' => 'Report.view', 'display_name' => 'View Reports', 'module' => 'Report'],
            ['name' => 'Mailing.view', 'display_name' => 'View Mailing', 'module' => 'Mailing'],
            ['name' => 'Setting.view', 'display_name' => 'View Settings', 'module' => 'Setting'],
            ['name' => 'Setting.update', 'display_name' => 'Update Settings', 'module' => 'Setting'],
            ['name' => 'Audit.view', 'display_name' => 'View Audit Logs', 'module' => 'Audit'],

            ['name' => 'FeedbackSupport.view', 'display_name' => 'View Feedback & Support', 'module' => 'Feedback & Support'],
            ['name' => 'FeedbackSupport.create', 'display_name' => 'Create Feedback & Support', 'module' => 'Feedback & Support'],
            ['name' => 'FeedbackSupport.review', 'display_name' => 'Review Feedback & Support', 'module' => 'Feedback & Support'],
            ['name' => 'FeedbackSupport.assign', 'display_name' => 'Assign Feedback & Support', 'module' => 'Feedback & Support'],
            ['name' => 'FeedbackSupport.resolve', 'display_name' => 'Resolve Feedback & Support', 'module' => 'Feedback & Support'],
            ['name' => 'FeedbackSupport.confidential', 'display_name' => 'View Confidential Feedback & Support', 'module' => 'Feedback & Support'],
            ['name' => 'FeedbackSupport.manage', 'display_name' => 'Manage Feedback & Support', 'module' => 'Feedback & Support'],

            ['name' => 'Backup.view', 'display_name' => 'View Backups', 'module' => 'Backup'],
            ['name' => 'Backup.create', 'display_name' => 'Create Backup', 'module' => 'Backup'],
            ['name' => 'Backup.import', 'display_name' => 'Import Database', 'module' => 'Backup'],
            ['name' => 'Backup.delete', 'display_name' => 'Delete Backup', 'module' => 'Backup'],

            ['name' => 'System.manage',  'display_name' => 'Manage System', 'module' => 'System'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'module' => $permission['module']
                ]
            );
        }

        Cache::forget('rbac:permissions:list');
    }
}
