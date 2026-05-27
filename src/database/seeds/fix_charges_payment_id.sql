SELECT 
    pm.id AS payment_id,
    c.id AS charge_id,
    c.name,
    c.order_id,
    c.gross_amount,
    c.transaction_status,
    c.bank,
    c.va_number,
    c.transaction_time,
    c.fraud_status,
    c.payment_type,
    c.snap_token,
    c.created_at,
    c.updated_at
FROM charges c
LEFT JOIN payments pm ON pm.order_id = c.order_id
WHERE c.payment_id IS NULL
ORDER BY c.created_at DESC;