<?php

namespace App\SQL;

use App\Exception\ReportDoesNotExist;

class Report
{
    private const array MAP = [
        self::MEMBERSHIP => self::MEMBERSHIP_QUERY,
        self::ACTIVE_MEMBERSHIP => self::ACTIVE_MEMBERSHIP_QUERY,
        self::SKU_ACTIVE => self::SKU_ACTIVE_QUERY,
        self::SKU_ALL => self::SKU_ALL_QUERY,
        self::AGING => self::AGING_QUERY,
        self::LEDGER_SUMMARY => self::LEDGER_SUMMARY_QUERY,
        self::LEDGER_DETAIL => self::LEDGER_DETAIL_QUERY,
        self::SKU_INVENTORY => self::SKU_INVENTORY_QUERY,
    ];

    public const string SKU_INVENTORY = 'sku-inventory';
    public const string SKU_INVENTORY_QUERY = '
    select
        corporation.name as "Corp",
        tax_service.token1 as "TaxJar Token",
        p.name as "Product",
        p.preprocessor as "Product Preprocessor",
        p.compiler as "Product Compiler",
        (
            case
                when p.frequency = 10 then \'Weekly\'
                when p.frequency = 20 then \'Monthly\'
                when p.frequency = 30 then \'Yearly\'
                else \'Once\'
            end
        )
        as "Product Frequency",
        st.label as "SKU Type",
        sku.sku_id as "ID",
        sku.sku_code as "Code",
        sku.name as "Name",
        sku.customer_facing_sku_name as "Customer-Facing Name",
        sku.is_subscription as "Subscription",
        sku.quantity as "Quantity",
        sku.unit_price as "Unit Price",
        sku.tax_code as "Tax Code",
        sku.composite_tax_code_id as "Composite Tax Code ID",
        ctc.name as "Composite Tax Code Name",
        (
            select string_agg(ctcl.code, \', \' order by ctcl.priority)
            from composite_tax_code_line ctcl
            where ctcl.composite_tax_code_id = sku.composite_tax_code_id
            group by ctcl.composite_tax_code_id
        )
        as "Composite Tax Codes",
        sku.ledger_id as "Ledger ID",
        sku.sortorder as "Sort Order"
    from sku
    inner join sku_type st on st.sku_type_id = sku.sku_type_id
    inner join product p on sku.product_id = p.product_id
    inner join corporation on p.corporation_id = corporation.corporation_id
    inner join tax_service on corporation.tax_service_id = tax_service.tax_service_id
    left join composite_tax_code ctc on ctc.composite_tax_code_id = sku.composite_tax_code_id
    order by p.product_id desc, sku.sortorder
    ';

    public const string LEDGER_DETAIL = 'ledger-detail';
    public const string LEDGER_DETAIL_QUERY = '
    select
    le.ledger_entry_id as "ID",
    a.account_id as "Account",
    u.name as "By",
    let.name as "Type",
    les.name as "Source",
    pr.name as "Bound to Subscription",
    i.identifier as "Invoice",
    p.identifier as "Payment",
    pi.identifier as "Installment",
    le.credit_entry_id as "Applied to Credit ID",
    le.created_at as "Date",
    le.amount as "Amount",
    le.currency as "Currency",
    le.is_applied as "Applied",
    le.transaction_id as "Transaction ID",
    le.reason as "Reason"
    from
        ledger_entry le
        inner join account a on a.account_id = le.account_id
        inner join ledger_entry_source les on les.ledger_entry_source_id = le.ledger_entry_source_id
        inner join ledger_entry_type let on let.ledger_entry_type_id = le.ledger_entry_type_id
        left join subscription s on s.subscription_id = le.subscription_id
        left join product pr on pr.product_id = s.product_id
        left join invoice i on i.invoice_id = le.invoice_id
        left join payment p on p.payment_id = le.payment_id
        left join payment_installment pi on pi.payment_installment_id = le.payment_installment_id
        left join users u on u.user_id = le.employee_id
    order by a.account_id, le.created_at desc
;
    ';

    public const string LEDGER_SUMMARY = 'ledger-summary';
    public const string LEDGER_SUMMARY_QUERY = '
    select
    a.account_id as "Account",
    sum(le.amount) as "Total Balance",
    count(le.ledger_entry_type_id) filter ( where  le.ledger_entry_type_id = 1) as "Credits",
    count(le.ledger_entry_type_id) filter ( where  le.ledger_entry_type_id = 2) as "Debits",
    string_agg(concat(les.name, \': \', le.amount), \', \')
    filter ( where le.ledger_entry_type_id = 1 and le.is_applied = false) as "Unapplied Credits",
    sum(le.amount) filter ( where le.ledger_entry_type_id = 1 and le.is_applied = false) as "Balance Unapplied Credits"
    from
        ledger_entry le
        inner join account a on a.account_id = le.account_id
        inner join ledger_entry_source les on les.ledger_entry_source_id = le.ledger_entry_source_id
    group by a.account_id, a.created_at
    order by a.created_at desc    
    ';

    public const string AGING = 'aging';
    public const string AGING_QUERY = '
    select
    a.account_id as "Account",
    case when (a.status = 1) then \'Active\' else \'Closed\' end as "Account Status",
    a.company as "Company",
    s.software_id as "Software ID",
    p.name as "Product",
    case when (s.status = 1) then \'Active\' else \'Closed\' end as "Subscription Status",
    s.start_date as "Start",
    s.end_date as "End",
    s.invoice_date as "Scheduled Invoice",
    case
        when (s.pause_payments is true) then \'Paused\'
        else \'Active\'
    end as "Subscription Payment Collection",
    sum     (
            case
                when
                    pmt.settled_at is null
                and
                    pmt.voided_at is null
                and
                    pmt.refunded_at is null
                then pmt.amount
            end
            ) as "Total Balance",
        sum     (
            case
                when
                    pmt.settled_at is null
                and
                    pmt.voided_at is null
                and
                    pmt.refunded_at is null
                and
                    pmt.is_paused is true
                then pmt.amount
            end
            ) as "Total Balance Paused",
            ps.type as "Payment Source",
            ps.brand as "Brand",
            ps.last4 as "Last 4",
            ps.label as "Label",
            (
                select
                    invoice.total_amount
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Due Amount",
    (
                select
                    invoice.paid_amount
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Paid Amount",
            (
                select
                    invoice.invoice_date
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Date"
    from
        account a
            inner join subscription s on s.account_id = a.account_id
            inner join product p on p.product_id = s.product_id
            inner join invoice i on i.subscription_id = s.subscription_id
            left join payment pmt on pmt.invoice_id = i.invoice_id
            left join payment_source ps on s.payment_source_id = ps.payment_source_id
    where
        pmt.settled_at is null
    and
        pmt.voided_at is null
    and
        pmt.refunded_at is null
    group by
        a.account_id,
        "Account Status",
        a.created_at,
        a.company,
        a.software_id,
        s.software_id,
        s.subscription_id,
        p.name,
        "Start",
        "End",
        s.invoice_date,
        "Subscription Status",
        "Subscription Payment Collection",
        ps.type,
        ps.brand,
        ps.last4,
        ps.label
    order by
        a.created_at desc
;
    ';
    public const string SKU_ALL = 'all-membership-skus';
    public const string SKU_ALL_QUERY = '
    select
        a.account_id as "Account",
        a.state_code as "State",
        a.country_code as "Country",
        case when (a.status = 1) then \'Active\' else \'Closed\' end as "Account Status",
        a.company as "Company",
        p.name as "Product",
        case when (s.status = 1) then \'Active\' else \'Closed\' end as "Subscription Status",
        s.start_date as "Start",
        s.end_date as "End",
        s.invoice_date as "Scheduled Invoice",
        sku_type.label as "SKU Type",
        sku.name as "SKU Name",
        ssku.quantity as "SKU Quantity",
        (
            select
                il.quantity
                from
                    invoice i
                inner join invoice_line il on il.invoice_id = i.invoice_id
                where
                    i.subscription_id = s.subscription_id
                and
                    il.sku_id = sku.sku_id
                order by il.created_at desc
                limit 1
        ) as "Last Billed SKU Quantity",
        ssku.unit_price as "SKU Unit Price",
        ssku.starts_at as "SKU Start Date",
        ssku.starts_at as "SKU End Date"
        from
            subscription_sku ssku
        inner join subscription s on s.subscription_id = ssku.subscription_id
        inner join product p on s.product_id = p.product_id
        inner join account a on a.account_id = s.account_id
        inner join sku on ssku.sku_id = sku.sku_id
        inner join sku_type on sku.sku_type_id = sku_type.sku_type_id
    order by
        a.account_id,
        s.subscription_id
    ';
    public const string SKU_ACTIVE = 'active-membership-skus';
    public const string SKU_ACTIVE_QUERY = '
    select
        a.account_id as "Account",
        a.state_code as "State",
        a.country_code as "Country",
        case when (a.status = 1) then \'Active\' else \'Closed\' end as "Account Status",
        a.company as "Company",
        p.name as "Product",
        case when (s.status = 1) then \'Active\' else \'Closed\' end as "Subscription Status",
        s.start_date as "Start",
        s.end_date as "End",
        s.invoice_date as "Scheduled Invoice",
        sku_type.label as "SKU Type",
        sku.name as "SKU Name",
        ssku.quantity as "SKU Quantity",
        (
            select
                il.quantity
                from
                    invoice i
                inner join invoice_line il on il.invoice_id = i.invoice_id
                where
                    i.subscription_id = s.subscription_id
                and
                    il.sku_id = sku.sku_id
                order by il.created_at desc
                limit 1
        ) as "Last Billed SKU Quantity",
        ssku.unit_price as "SKU Unit Price",
        ssku.starts_at as "SKU Start Date",
        ssku.starts_at as "SKU End Date"
        from
            subscription_sku ssku
        inner join subscription s on s.subscription_id = ssku.subscription_id
        inner join product p on s.product_id = p.product_id
        inner join account a on a.account_id = s.account_id
        inner join sku on ssku.sku_id = sku.sku_id
        inner join sku_type on sku.sku_type_id = sku_type.sku_type_id
    where
        s.status > 0
    and
        s.invoice_date > now()
    order by
        a.account_id,
        s.subscription_id
    ';

    public const string MEMBERSHIP = 'membership';
    public const string MEMBERSHIP_QUERY = '
     select
    a.account_id as "Account",
    a.state_code as "State",
    a.country_code as "Country",
    case when (a.status = 1) then \'Active\' else \'Closed\' end as "Account Status",
    a.company as "Company",
    s.software_id as "Software ID",
    p.name as "Product",
    case when (s.status = 1) then \'Active\' else \'Closed\' end as "Subscription Status",
    case
            when (s.frequency = 10)
                then \'Weekly\'
            when (s.frequency = 20)
                then \'Monthly\'
            when (s.frequency = 30)
                then \'Yearly\'
            when (s.frequency = 0)
                then \'Once\'
            else \'Other\' end as "Frequency",
    s.start_date as "Start",
    s.end_date as "End",
    s.invoice_date as "Scheduled Invoice",
    case
        when (s.pause_payments is true) then \'Paused\'
        else \'Active\'
    end as "Subscription Payment Collection",
    sum     (
            case
                when
                    pmt.settled_at is null
                and
                    pmt.voided_at is null
                and
                    pmt.refunded_at is null
                then pmt.amount
            end
            ) as "Total Balance",
        sum     (
            case
                when
                    pmt.settled_at is null
                and
                    pmt.voided_at is null
                and
                    pmt.refunded_at is null
                and
                    pmt.is_paused is true
                then pmt.amount
            end
            ) as "Total Balance Paused",
            ps.type as "Payment Source",
            ps.brand as "Brand",
            ps.last4 as "Last 4",
            ps.label as "Label",
            (
                select
                    invoice.total_amount
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Due Amount",
            (
                select
                    invoice.paid_amount
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Paid Amount",
            (
                select
                    invoice.invoice_date
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Date"
    from
        account a
            inner join subscription s on s.account_id = a.account_id
            inner join product p on p.product_id = s.product_id
            left join invoice i on i.subscription_id = s.subscription_id
            left join payment pmt on pmt.invoice_id = i.invoice_id
            left join payment_source ps on s.payment_source_id = ps.payment_source_id
    group by
        a.account_id,
        a.state_code,
        a.country_code,
        "Account Status",
        a.created_at,
        a.company,
        a.software_id,
        s.software_id,
        s.subscription_id,
        p.name,
        s.frequency,
        "Start",
        "End",
        s.invoice_date,
        "Subscription Status",
        "Subscription Payment Collection",
        ps.type,
        ps.brand,
        ps.last4,
        ps.label
    order by
        a.created_at desc
;
';
    private const string ACTIVE_MEMBERSHIP = 'active-membership';
    public const string ACTIVE_MEMBERSHIP_QUERY =
        '
     select
    a.account_id as "Account",
    a.state_code as "State",
    a.country_code as "Country",
    case when (a.status = 1) then \'Active\' else \'Closed\' end as "Account Status",
    a.company as "Company",
    s.software_id as "Software ID",
    p.name as "Product",
    case when (s.status = 1) then \'Active\' else \'Closed\' end as "Subscription Status",
    case
            when (s.frequency = 10)
                then \'Weekly\'
            when (s.frequency = 20)
                then \'Monthly\'
            when (s.frequency = 30)
                then \'Yearly\'
            when (s.frequency = 0)
                then \'Once\'
            else \'Other\' end as "Frequency",
    s.start_date as "Start",
    s.end_date as "End",
    s.invoice_date as "Scheduled Invoice",
    case
        when (s.pause_payments is true) then \'Paused\'
        else \'Active\'
    end as "Subscription Payment Collection",
    sum     (
            case
                when
                    pmt.settled_at is null
                and
                    pmt.voided_at is null
                and
                    pmt.refunded_at is null
                then pmt.amount
            end
            ) as "Total Balance",
        sum     (
            case
                when
                    pmt.settled_at is null
                and
                    pmt.voided_at is null
                and
                    pmt.refunded_at is null
                and
                    pmt.is_paused is true
                then pmt.amount
            end
            ) as "Total Balance Paused",
            ps.type as "Payment Source",
            ps.brand as "Brand",
            ps.last4 as "Last 4",
            ps.label as "Label",
            (
                select
                    invoice.total_amount
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Due Amount",
            (
                select
                    invoice.paid_amount
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Paid Amount",
            (
                select
                    invoice.invoice_date
                from invoice
                where
                    invoice.account_id = a.account_id
                and
                    invoice.subscription_id = s.subscription_id
                order by invoice.created_at desc limit 1
             )
            as "Last Invoice Date"
    from
        account a
            inner join subscription s on s.account_id = a.account_id
            inner join product p on p.product_id = s.product_id
            left join invoice i on i.subscription_id = s.subscription_id
            left join payment pmt on pmt.invoice_id = i.invoice_id
            left join payment_source ps on s.payment_source_id = ps.payment_source_id
    where
        a.status = 1
    and
        s.status = 1
    and
        s.invoice_date is not null
    group by
        a.account_id,
        a.state_code,
        a.country_code,
        "Account Status",
        a.created_at,
        a.company,
        a.software_id,
        s.software_id,
        s.subscription_id,
        p.name,
        s.frequency,
        "Start",
        "End",
        s.invoice_date,
        "Subscription Status",
        "Subscription Payment Collection",
        ps.type,
        ps.brand,
        ps.last4,
        ps.label
    order by
        a.created_at desc
;
'
    ;

    /**
     * @throws ReportDoesNotExist
     */
    public static function getReportQuery(string $reportName): string
    {
        if (!isset(self::MAP[$reportName])) {
            throw new ReportDoesNotExist($reportName);
        }

        return self::MAP[$reportName];
    }

    public static function getReportList(): array
    {
        return array_keys(self::MAP);
    }
}
