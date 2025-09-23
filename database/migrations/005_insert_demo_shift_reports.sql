-- Migration: Insert demo shift reports for testing
-- Description: Add sample shift report data to demonstrate the system
-- Created: 2025-01-22

-- Insert demo morning shift report
INSERT INTO shift_reports (
    user_id, 
    shift_date, 
    shift_type, 
    location, 
    checklist_data, 
    reviews_count, 
    shipments_data, 
    refunds_data, 
    notes, 
    created_at
) VALUES (
    1, -- Admin User
    '2025-01-21', 
    'morning', 
    'Lutz', 
    JSON_ARRAY(
        'Count your drawer – verify cash is correct and properly closed from previous night',
        'Prepare daily cleaning sheet – ensure all stylists are included',
        'Organize coffee area – stock K-cups, fill Keurig, restock cups, creamer, sugar, and snacks',
        'Clean lobby – restock candles, clean tables, dust sofas, sweep and mop as needed',
        'Clean exterior windows and door glass',
        'Replace used mop heads and place in laundry bin for washing',
        'Check supply inventory – cards, paper, pens, stamps, bags, and printer rolls',
        'Complete follow-up calls for services from 2 days ago',
        'Check and respond to any voicemails',
        'Contact all missed appointment requests from previous day',
        'Follow up on weekly cancellations for rescheduling',
        'Check out all used color tubes and boxes from color bar bowl',
        'Organize front desk – professional appearance, no clutter or dust',
        'Send end-of-shift summary email to managers and incoming front desk staff',
        'Close and balance drawer – notify Eliana of any discrepancies'
    ),
    3, -- 3 reviews received
    JSON_OBJECT(
        'status', 'yes',
        'vendor', 'UPS',
        'notes', 'Received 2 boxes of hair products and 1 box of retail items'
    ),
    JSON_ARRAY(
        JSON_OBJECT(
            'amount', 45.00,
            'reason', 'Service Issue',
            'customer', 'Sarah Johnson',
            'service', 'Hair Color Correction',
            'notes', 'Client was not satisfied with color result, full refund processed'
        )
    ),
    'Great morning shift! Everything went smoothly. The new UPS delivery system is working well. Had one refund for color correction - client was very understanding. All morning duties completed successfully.',
    '2025-01-21 14:30:00'
);

-- Insert demo evening shift report
INSERT INTO shift_reports (
    user_id, 
    shift_date, 
    shift_type, 
    location, 
    checklist_data, 
    reviews_count, 
    shipments_data, 
    refunds_data, 
    notes, 
    created_at
) VALUES (
    1, -- Admin User
    '2025-01-20', 
    'evening', 
    'Wesley Chapel', 
    JSON_ARRAY(
        'Count register and process final payments',
        'Complete deep cleaning of all areas',
        'Sanitize all tools and stations',
        'Start laundry and towel washing',
        'Stock inventory for tomorrow',
        'Turn off all equipment and electronics',
        'Turn off all lights',
        'Lock all doors and windows',
        'Set security system',
        'Complete cash drop and deposit preparation',
        'Leave notes for morning staff',
        'Complete final walkthrough checklist'
    ),
    1, -- 1 review received
    JSON_OBJECT(
        'status', 'no',
        'vendor', '',
        'notes', ''
    ),
    JSON_ARRAY(
        JSON_OBJECT(
            'amount', 25.00,
            'reason', 'Product Return',
            'customer', 'Mike Rodriguez',
            'service', 'Shampoo Return',
            'notes', 'Client allergic to new shampoo formula, returned unopened bottle'
        ),
        JSON_OBJECT(
            'amount', 15.00,
            'reason', 'Scheduling Error',
            'customer', 'Lisa Chen',
            'service', 'Cancellation Fee Waiver',
            'notes', 'Our system double-booked appointment, waived cancellation fee'
        )
    ),
    'Evening shift completed successfully. Had two refunds - one product return and one scheduling error on our end. All closing procedures followed. Left detailed notes for morning team about the inventory restocking needed.',
    '2025-01-20 21:45:00'
);

-- Insert another demo morning shift report
INSERT INTO shift_reports (
    user_id, 
    shift_date, 
    shift_type, 
    location, 
    checklist_data, 
    reviews_count, 
    shipments_data, 
    refunds_data, 
    notes, 
    created_at
) VALUES (
    1, -- Admin User
    '2025-01-19', 
    'morning', 
    'Land O\' Lakes', 
    JSON_ARRAY(
        'Count your drawer – verify cash is correct and properly closed from previous night',
        'Prepare daily cleaning sheet – ensure all stylists are included',
        'Organize coffee area – stock K-cups, fill Keurig, restock cups, creamer, sugar, and snacks',
        'Clean lobby – restock candles, clean tables, dust sofas, sweep and mop as needed',
        'Clean exterior windows and door glass',
        'Replace used mop heads and place in laundry bin for washing',
        'Check supply inventory – cards, paper, pens, stamps, bags, and printer rolls',
        'Complete follow-up calls for services from 2 days ago',
        'Check and respond to any voicemails',
        'Contact all missed appointment requests from previous day',
        'Follow up on weekly cancellations for rescheduling',
        'Check out all used color tubes and boxes from color bar bowl',
        'Organize front desk – professional appearance, no clutter or dust',
        'Send end-of-shift summary email to managers and incoming front desk staff',
        'Close and balance drawer – notify Eliana of any discrepancies'
    ),
    5, -- 5 reviews received
    JSON_OBJECT(
        'status', 'yes',
        'vendor', 'FedEx',
        'notes', 'Received salon supplies and retail inventory'
    ),
    JSON_ARRAY(), -- No refunds
    'Excellent morning! Received 5 positive reviews. FedEx delivery came early. All morning tasks completed ahead of schedule. Coffee area is fully stocked and lobby looks pristine.',
    '2025-01-19 13:15:00'
);
