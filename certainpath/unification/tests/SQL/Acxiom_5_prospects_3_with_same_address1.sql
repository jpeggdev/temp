INSERT INTO prospects_stream (
    created_at, tenant, software, version, tag, processed,
    prefixttl, individualname, firstname, middlename, lastname, address, address2line,
    city, state, zip, zip4, country, dpbc, confidencecode, ageofindividual,
    addrtypeind, networthprem, homeownren, hownrenfl, dsfwalksequence, crrt,
    areacode, phone, phone_nospace, phoneflag, yearhomebuilt
) VALUES
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Mr', 'David M Manuz', 'David', 'M', 'Manuz', '6904 Ward Canyon Rd', NULL,
          'Clifton', 'AZ', '75001', '8054', 'US', '45', 1, 58,
          '1', '7', 'O', 'V', '34', 'R001',
          NULL, NULL, NULL, 'N', 1975
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Mr', 'Levi D Mortensen', 'Levi', 'D', 'Mortensen', '6904 Ward Canyon Rd', NULL,
          'Clifton', 'AZ', '75001', '8054', 'US', '345', 1, 30,
          '1', '7', 'O', 'V', '250', 'R001',
          NULL, NULL, NULL, 'N', 1975
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Ms', 'Serena Porter', 'Serena', NULL, 'Porter', '6904 Ward Canyon Rd', NULL,
          'Clifton', 'AZ', '75001', '8054', 'US', '500', 1, 38,
          '1', '6', 'O', 'V', '239', 'R001',
          NULL, NULL, NULL, 'N', 1975
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Mr', 'Ryan Mortensen', 'Ryan', 'D', 'Mortensen', '6905 Ward Canyon Rd', NULL,
          'Clifton', 'AZ', '75001', '8054', 'US', '345', 1, 30,
          '1', '7', 'O', 'V', '250', 'R001',
          NULL, NULL, NULL, 'N', 1975
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Ms', 'Mike Porter', 'Smith', NULL, 'Porter', '6906 Ward Canyon Rd', NULL,
          'Clifton', 'AZ', '75001', '8054', 'US', '500', 1, 38,
          '1', '6', 'O', 'V', '239', 'R001',
          NULL, NULL, NULL, 'N', 1975
      );