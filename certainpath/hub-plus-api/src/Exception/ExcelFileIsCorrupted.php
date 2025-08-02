<?php

namespace App\Exception;

class ExcelFileIsCorrupted extends AppException
{
    protected function getDefaultMessage(): string
    {
        return
            'To upload ServiceTitan reports, follow these steps:'
            .' 1) Open the file in Excel,'
            .' 2) From the File menu choose "Save a Copy ...",'
            .' 3) Give the File a New Name, '
            .' 4) For Customer Reports use YourCompanyName-customers.xlsx, '
            .'5) For Invoice Reports use yourCompanyName-invoices.xlsx, '
            .'6) Upload the new file.'
        ;
    }
}
