<?php

return [
    'title' => 'Staff Expense Management',
    'subtitle' => 'Track and manage staff expenses',
    'all_shops' => 'All Shops',

    'summary' => [
        'base_salary' => 'Base Salary',
        'bonus_rewards' => 'Bonuses / Rewards',
        'deductions' => 'Deductions',
        'net_total_expense' => 'Net Total Expense',
        'net_total_payable' => 'Net Total Payable',
    ],

    'table' => [
        'no' => 'Nº',
        'staff_name' => 'Staff Name',
        'shop' => 'Shop',
        'type' => 'Type',
        'amount' => 'Amount',
        'amount_usd' => 'Amount (USD)',
        'expense_date' => 'Expense Date',
        'remarks' => 'Remarks',
        'description' => 'Description / Remarks',
        'recorded_by' => 'Recorded By',
        'status' => 'Status',
        'action' => 'Action',
    ],

    'type' => [
        'salary' => 'Salary',
        'bonus' => 'Bonus',
        'deduction' => 'Deduction',
        'other' => 'Other Expense',
    ],

    'button' => [
        'record_expense' => 'Record Expense',
        'create' => 'Record Expense',
        'save' => 'Save',
        'save_new' => 'Save & New',
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'export' => 'Export',
    ],

    'form' => [
        'title' => [
            'create' => 'Record Staff Expense',
            'update' => 'Update Staff Expense',
            'view' => 'View Staff Expense',
        ],
        'staff' => 'Staff Member',
        'select_staff' => '-- Select Staff Member --',
        'type' => 'Expense Type',
        'select_type' => '-- Select Expense Type --',
        'shop' => 'Shop / Branch',
        'all_shops' => '-- All Shops / General --',
        'expense_date' => 'Expense Date',
        'amount' => 'Amount ($)',
        'status' => 'Status',
        'description' => 'Description / Remarks',
        'placeholder_description' => 'Enter notes or explanation for this expense ...',
        'placeholder_date' => 'YYYY-MM-DD',
        'placeholder_amount' => '0.00',
    ],

    'filter' => [
        'from_date' => 'From Date',
        'to_date' => 'To Date',
        'expense_type' => 'Expense Type',
        'all_types' => '-- All Types --',
        'staff' => 'Staff Member',
        'all_staff' => 'All Staff Members',
        'shop' => 'Shop / Branch',
        'search' => 'Search...',
    ],

    'history' => [
        'title' => 'Staff Expense History',
        'phone' => 'Phone',
        'position' => 'Position',
        'all_staff_expenses' => 'All Staff Expenses',
        'confirm_delete' => 'Move this expense record to trash?',
        'no_records' => 'No expense records found for :name.',
    ],

    'validation' => [
        'staff_id_required' => 'Please select a staff member.',
        'staff_id_exists' => 'Selected staff member does not exist.',
        'type_required' => 'Expense type is required.',
        'type_invalid' => 'Selected expense type is invalid.',
        'amount_required' => 'Expense amount is required.',
        'amount_numeric' => 'Expense amount must be a number.',
        'amount_min' => 'Expense amount must be at least $0.01.',
        'expense_date_required' => 'Expense date is required.',
        'expense_date_date' => 'Expense date must be a valid date.',
        'status_required' => 'Status is required.',
        'description_max' => 'Description must not exceed 1000 characters.',
    ],

    'message' => [
        'create_success' => 'Expense recorded successfully.',
        'create_failed' => 'Failed to record expense!',
        'update_success' => 'Expense updated successfully.',
        'update_failed' => 'Failed to update expense!',
        'delete_success' => 'Expense deleted successfully.',
        'delete_failed' => 'Failed to delete expense!',
        'restore_success' => 'Expense restored successfully.',
        'restore_failed' => 'Failed to restore expense!',
        'destroy_success' => 'Expense permanently deleted.',
        'destroy_failed' => 'Failed to permanently delete expense!',
    ],
];
