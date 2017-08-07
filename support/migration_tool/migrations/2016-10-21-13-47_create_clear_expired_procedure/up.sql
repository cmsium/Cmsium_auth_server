CREATE PROCEDURE clearExpiredConfirmations()
  BEGIN
    DELETE FROM confirmation WHERE confirmation.expires < NOW();
  END;