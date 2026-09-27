<?php

return [
    'title' => 'Remaining Amount Management',
    'excel_report' => 'Remaining Amount Report',
    'export_loading' => 'Exporting Remaining Amount Report ...',

    'tab' => [
        'all_outstanding' => 'All Outstanding',
        'partial_paid' => 'Partial Paid',
        'pending_payment' => 'Pending Payment',
        'fully_paid' => 'Fully Paid',
    ],

    'action' => [
        'view_detail' => 'View Detail',
        'add_payment' => 'Add Payment',
        'manage_payment' => 'Manage Payment',
        'payment_history' => 'Payment History',
        'send_reminder' => 'Send Reminder',
        'edit_payment' => 'Edit Payment',
        'delete_payment' => 'Delete Payment',
    ],

    'detail' => [
        'title' => 'Remaining Amount Detail',
        'back_to_list' => 'Back to List',
    ],

    'ledger' => [
        'total_amount' => 'Total Amount',
        'amount_paid' => 'Amount Paid',
        'remaining_balance' => 'Remaining Balance',
    ],

    'modal' => [
        'make_payment' => 'Make Payment',
        'payment_history' => 'Payment History',
    ],

    'form' => [
        'payment_amount' => 'Payment Amount ($)',
        'full_balance' => 'Full Balance',
        'payment_method' => 'Payment Method',
        'payment_date_time' => 'Payment Date & Time',
        'note_reference' => 'Note / Reference (Optional)',
    ],

    'placeholder' => [
        'note' => 'e.g. Transaction ID, receipt note...',
    ],

    'button' => [
        'send_reminder' => 'Send Reminder',
        'sending' => 'Sending...',
        'record_payment' => 'Record Payment',
        'processing_payment' => 'Processing Payment...',
    ],

    'history' => [
        'empty' => 'No payment transactions recorded yet.',
        'date_time' => 'Date & Time',
        'method' => 'Method',
        'amount' => 'Amount',
        'staff' => 'Staff',
        'note' => 'Note',
        'actions' => 'Actions',
        'date' => 'Date',
    ],

    'confirm' => [
        'delete_payment_record' => 'Are you sure want to delete payment of <b>:amount</b> (:method)?',
    ],

    'message' => [
        'reminder_sent_success' => 'Payment reminder sent successfully.',
        'reminder_sent_order' => 'Payment reminder sent successfully for order :invoice.',
        'payment_deleted_success' => 'Payment deleted successfully.',
        'error_record_payment' => 'Unable to record payment.',
        'error_update_payment' => 'Unable to update payment.',
        'error_delete_payment' => 'Unable to delete payment.',
        'error_send_reminder' => 'Unable to send reminder.',
        'cannot_add_payment_rejected' => 'Cannot add payment to a rejected order.',
        'order_already_paid' => 'Order is already fully paid.',
        'payment_exceeds_available' => 'Payment amount exceeds available balance.',
        'cannot_edit_payment_rejected' => 'Cannot edit payment on a rejected order.',
        'cannot_delete_payment_rejected' => 'Cannot delete payment on a rejected order.',
        'cannot_send_reminder_rejected' => 'Cannot send reminder for a rejected order.',
        'no_outstanding_balance' => 'This order does not have any outstanding balance.',
    ],

    'notification' => [
        'reminder_title' => 'Payment Reminder: Order :invoice',
        'reminder_desc' => 'Dear :customer, this is a friendly reminder that you have an outstanding balance of :remaining (Total: :total, Paid: :paid) for your order on :date.',
    ],

    'excel' => [
        'sheet_name' => 'Remaining Amount Report',
        'file_prefix' => 'Remaining_Amount_Report_',
        'order_id' => 'Order ID',
        'order_date' => 'Order Date',
        'shop' => 'Shop',
        'barber' => 'Barber',
        'customer_phone' => 'Customer Phone',
        'pay_status' => 'Pay Status',
        'total_price' => 'Total Price',
        'paid_amount' => 'Paid Amount',
        'remaining_balance' => 'Remaining Balance',
        'last_pay_date' => 'Last Pay Date',
    ],
];
