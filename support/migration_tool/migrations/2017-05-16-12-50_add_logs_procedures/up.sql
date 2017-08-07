CREATE PROCEDURE clearTempLogsTable()
  BEGIN
    DROP TABLE IF EXISTS system_log_temp;
  END;
CREATE PROCEDURE createTempLogsTable()
  BEGIN
    CREATE TABLE system_log_temp LIKE system_log;
  END;