CREATE EVENT clearExpiredConfirmations
ON SCHEDULE EVERY 20 MINUTE
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
DO CALL clearExpiredConfirmations;