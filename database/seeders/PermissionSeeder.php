<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions=[
            ['name'=>'Member.view','display_name'=>'View Members','module'=>'Member'],
            ['name'=>'Member.create','display_name'=>'Create Member','module'=>'Member'],
            ['name'=>'Member.update','display_name'=>'Update Member','module'=>'Member'],
            ['name'=>'Member.delete','display_name'=>'Delete Member','module'=>'Member'],

            ['name'=>'User.view','display_name'=>'View Users','module'=>'User'],
            ['name'=>'User.create','display_name'=>'Create User','module'=>'User'],
            ['name'=>'User.update','display_name'=>'Update User','module'=>'User'],
            ['name'=>'User.delete','display_name'=>'Delete User','module'=>'User'],

            ['name'=>'Role.view','display_name'=>'View Roles','module'=>'Role'],
            ['name'=>'Role.create','display_name'=>'Create Role','module'=>'Role'],
            ['name'=>'Role.update','display_name'=>'Update Role','module'=>'Role'],
            ['name'=>'Role.delete','display_name'=>'Delete Role','module'=>'Role'],

            ['name'=>'Finance.view','display_name'=>'View Finance','module'=>'Finance'],
            ['name'=>'Finance.create','display_name'=>'Create Finance Entry','module'=>'Finance'],
            ['name'=>'Finance.update','display_name'=>'Update Finance Entry','module'=>'Finance'],
            ['name'=>'Finance.delete','display_name'=>'Delete Finance Entry','module'=>'Finance'],

            ['name'=>'Investment.view','display_name'=>'View Investments','module'=>'Investment'],
            ['name'=>'Investment.create','display_name'=>'Create Investment','module'=>'Investment'],
            ['name'=>'Investment.update','display_name'=>'Update Investment','module'=>'Investment'],
            ['name'=>'Investment.delete','display_name'=>'Delete Investment','module'=>'Investment'],

            ['name'=>'Land.view','display_name'=>'View Land','module'=>'Land'],
            ['name'=>'Land.create','display_name'=>'Create Land','module'=>'Land'],
            ['name'=>'Land.update','display_name'=>'Update Land','module'=>'Land'],
            ['name'=>'Land.delete','display_name'=>'Delete Land','module'=>'Land'],

            ['name'=>'Project.view','display_name'=>'View Projects','module'=>'Project'],
            ['name'=>'Project.create','display_name'=>'Create Project','module'=>'Project'],
            ['name'=>'Project.update','display_name'=>'Update Project','module'=>'Project'],
            ['name'=>'Project.delete','display_name'=>'Delete Project','module'=>'Project'],

            ['name'=>'Poll.view','display_name'=>'View Polls','module'=>'Poll'],
            ['name'=>'Poll.create','display_name'=>'Create Poll','module'=>'Poll'],
            ['name'=>'Poll.update','display_name'=>'Update Poll','module'=>'Poll'],
            ['name'=>'Poll.delete','display_name'=>'Delete Poll','module'=>'Poll'],

            ['name'=>'Notice.view','display_name'=>'View Notices','module'=>'Notice'],
            ['name'=>'Notice.create','display_name'=>'Create Notice','module'=>'Notice'],
            ['name'=>'Notice.update','display_name'=>'Update Notice','module'=>'Notice'],
            ['name'=>'Notice.delete','display_name'=>'Delete Notice','module'=>'Notice'],

            ['name'=>'Notification.view','display_name'=>'View Notifications','module'=>'Notification'],
            ['name'=>'Notification.send','display_name'=>'Send Notifications','module'=>'Notification'],

            ['name'=>'Approval.view','display_name'=>'View Approvals','module'=>'Approval'],
            ['name'=>'Approval.approve','display_name'=>'Approve Requests','module'=>'Approval'],
            ['name'=>'Approval.reject','display_name'=>'Reject Requests','module'=>'Approval'],
            ['name'=>'Approval.update','display_name'=>'Update Approvals','module'=>'Approval'],

            ['name'=>'Document.view','display_name'=>'View Documents','module'=>'Document'],
            ['name'=>'Document.create','display_name'=>'Upload Documents','module'=>'Document'],
            ['name'=>'Document.update','display_name'=>'Update Documents','module'=>'Document'],
            ['name'=>'Document.delete','display_name'=>'Delete Documents','module'=>'Document'],
            ['name'=>'Document.manage','display_name'=>'Manage All Documents','module'=>'Document'],

            ['name'=>'Report.view','display_name'=>'View Reports','module'=>'Report'],
            ['name'=>'Mailing.view','display_name'=>'View Mailing','module'=>'Mailing'],
            ['name'=>'Setting.view','display_name'=>'View Settings','module'=>'Setting'],
            ['name'=>'Setting.update','display_name'=>'Update Settings','module'=>'Setting'],
            ['name'=>'Audit.view','display_name'=>'View Audit Logs','module'=>'Audit'],

            ['name'=>'Backup.view','display_name'=>'View Backups','module'=>'Backup'],
            ['name'=>'Backup.create','display_name'=>'Create Backup','module'=>'Backup'],
            ['name'=>'Backup.import','display_name'=>'Import Database','module'=>'Backup'],
            ['name'=>'Backup.delete','display_name'=>'Delete Backup','module'=>'Backup'],
        ];

        foreach($permissions as $permission){
            Permission::updateOrCreate(
                ['name'=>$permission['name']],
                [
                    'display_name'=>$permission['display_name'],
                    'module'=>$permission['module']
                ]
            );
        }

        Cache::forget('rbac:permissions:list');
    }
}