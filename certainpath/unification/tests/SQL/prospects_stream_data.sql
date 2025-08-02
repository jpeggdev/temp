INSERT INTO prospects_stream (
    created_at, tenant, software, version, tag, processed,
    prefixttl, individualname, firstname, middlename, lastname, address, address2line,
    city, state, zip, zip4, country, dpbc, confidencecode, ageofindividual,
    addrtypeind, networthprem, homeownren, hownrenfl, dsfwalksequence, crrt,
    areacode, phone, phone_nospace, phoneflag, yearhomebuilt
) VALUES
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Ms', 'Susan M Ksenich', 'Susan', 'M', 'Ksenich', '1939 E Claire Dr', '',
          'Phoenix', 'AZ', '85022', '85022', 'US', '392', 1, 78,
          '1', 'B', 'O', 'V', '0044', 'C020',
          '', '', '', 'N', 1995
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Ms', 'Paula J Farquharson', 'Paula', 'J', 'Farquharson', '817 E Waltann Ln', '',
          'Phoenix', 'AZ', '85022', '85022', 'US', '170', 1, 66,
          '1', 'A', 'O', 'V', '0319', 'C010',
          '', '', '', 'N', 1979
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Mr', 'Matthew K Hed', 'Matthew', 'K', 'Hed', '2151 E Paradise Ln', '',
          'Phoenix', 'AZ', '85022', '85022', 'US', '514', 1, 46,
          '1', '9', 'O', 'V', '0026', 'C011',
          '', '', '', 'N', 1990
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Mr', 'Joseph N Blegen', 'Joseph', 'N', 'Blegen', '13630 N 18th St', '',
          'Phoenix', 'AZ', '85022', '85022', 'US', '301', 1, 70,
          '1', 'A', 'O', 'V', '0481', 'C006',
          '', '', '', 'N', 1999
      ),
      (
          'NOW()', 'UNI1', 'acxiom', 123, 'test_tag1', FALSE,
          'Ms', 'Claudia G Burns', 'Claudia', 'G', 'Burns', '1611 E Paradise Ln', '',
          'Phoenix', 'AZ', '85022', '85022', 'US', '117', 1, 76,
          '1', 'B', 'O', 'V', '0069', 'C031',
          '', '', '', 'N', 1993
      );
