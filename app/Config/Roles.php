<?php

namespace Config;

/**
 * Role => Permissions mapping for WITMS
 * Add or modify role permissions here. This file centralizes role capabilities
 * used by controllers and authorization checks.
 */
class Roles
{
    /**
     * @var array<string, array<string, bool>>
     */
    public $permissions = [
        // Accounts Payable Clerk
        'accounts_payable_clerk' => [
            // Supplier invoice management
            'invoices.create'    => true,
            'invoices.view'      => true,
            'invoices.update'    => true,
            'invoices.match'     => true, // match with PO / delivery / stock entries
            'invoices.flag'      => true, // flag discrepancies

            // Payment processing
            'payments.schedule'  => true,
            'payments.process'   => true,
            'payments.view'      => true,
            'payments.update'    => true,

            // Integration and reconciliation
            'inventory.view_relevant' => true, // view-only access to relevant inventory records
            'reconciliation.request'  => true, // request audit / reconciliation

            // Reporting and audit
            'reports.ap'         => true,
            'audit.trail.view'   => true,

            // Restricted
            'warehouse.controls' => false,
            'accounts_receivable.*' => false,
            'system.admin'       => false,
        ],

        // Placeholder for other roles (examples)
        'warehouse_manager' => [
            'inventory.manage' => true,
        ],
    ];

    /**
     * Helper to check a permission for a role.
     */
    public function can(string $role, string $permission): bool
    {
        if (! isset($this->permissions[$role])) {
            return false;
        }

        // Exact match
        if (array_key_exists($permission, $this->permissions[$role])) {
            return (bool) $this->permissions[$role][$permission];
        }

        // Wildcard match (e.g. invoices.*)
        foreach ($this->permissions[$role] as $key => $allowed) {
            if (substr($key, -2) === '.*') {
                $prefix = substr($key, 0, -2);
                if (strpos($permission, $prefix . '.') === 0) {
                    return (bool) $allowed;
                }
            }
        }

        return false;
    }
}
